<?php

namespace Tests\Feature;

use Botble\Marketplace\Models\Message;
use Botble\Marketplace\Supports\ConversationService;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Theme\Farmart\Http\Controllers\SourcingController;
use Botble\Marketplace\Http\Controllers\Fronts\CustomerMessageController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MarketplaceConversationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'conversation_test', 'database.connections.conversation_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ], 'cache.default' => 'array']);
        Schema::create('ec_customers', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('email');
            $table->boolean('is_vendor')->default(false); $table->timestamp('vendor_verified_at')->nullable();
        });
        Schema::create('mp_stores', function (Blueprint $table) {
            $table->id(); $table->integer('customer_id'); $table->string('name'); $table->string('status')->default('published');
        });
        Schema::create('mp_messages', function (Blueprint $table) {
            $table->id(); $table->integer('store_id'); $table->integer('parent_id')->nullable();
            $table->integer('customer_id')->nullable(); $table->string('sender_type')->default('customer');
            $table->string('name'); $table->string('email'); $table->text('content');
            $table->timestamp('read_at')->nullable(); $table->timestamps();
        });
        DB::table('ec_customers')->insert([
            ['id' => 1, 'name' => 'Buyer A', 'email' => 'a@example.test'],
            ['id' => 2, 'name' => 'Buyer B', 'email' => 'b@example.test'],
        ]);
        DB::table('mp_stores')->insert([
            ['id' => 10, 'customer_id' => 5, 'name' => 'Supplier A'],
            ['id' => 20, 'customer_id' => 6, 'name' => 'Supplier B'],
        ]);
        DB::table('ec_customers')->insert([
            ['id' => 5, 'name' => 'Supplier A', 'email' => 'vendor-a@example.test', 'is_vendor' => true, 'vendor_verified_at' => now()],
            ['id' => 6, 'name' => 'Supplier B', 'email' => 'vendor-b@example.test', 'is_vendor' => true, 'vendor_verified_at' => now()],
        ]);
    }

    private function conversation(int $buyer = 1, int $store = 10): Message
    {
        return Message::query()->create(['store_id' => $store, 'customer_id' => $buyer,
            'sender_type' => 'customer', 'name' => 'Buyer', 'email' => 'buyer@example.test', 'content' => 'Initial enquiry']);
    }

    public function test_buyer_cannot_read_another_buyers_conversation(): void
    {
        $conversation = $this->conversation();
        $this->expectException(ModelNotFoundException::class);
        app(ConversationService::class)->find((string) $conversation->id, 'customer', 2);
    }

    public function test_vendor_cannot_read_another_stores_conversation(): void
    {
        $conversation = $this->conversation();
        $this->expectException(ModelNotFoundException::class);
        app(ConversationService::class)->find((string) $conversation->id, 'vendor', 20);
    }

    public function test_replies_cannot_be_used_as_conversation_roots(): void
    {
        $service = app(ConversationService::class);
        $reply = $service->send($this->conversation(), 'vendor', 'Supplier', 'vendor@example.test', 'Reply', null);
        $this->expectException(ModelNotFoundException::class);
        $service->find((string) $reply->id, 'customer', 1);
    }

    public function test_live_stream_only_contains_its_own_conversation_and_marks_received_messages_read(): void
    {
        $service = app(ConversationService::class);
        $conversation = $this->conversation();
        $other = $this->conversation(2, 20);
        $mine = $service->send($conversation, 'customer', 'Buyer', 'buyer@example.test', 'My question', null);
        $received = $service->send($conversation, 'vendor', 'Supplier', 'vendor@example.test', 'Supplier reply', null);
        $hidden = $service->send($other, 'vendor', 'Other', 'other@example.test', 'Private content', null);
        $data = $service->stream($conversation, 'customer', $conversation->id);
        $this->assertSame([$mine->id, $received->id], array_column($data['messages'], 'id'));
        $this->assertNotNull($received->fresh()->read_at);
        $this->assertNull($mine->fresh()->read_at);
        $this->assertNull($hidden->fresh()->read_at);
        $this->assertArrayNotHasKey('email', $data['messages'][0]);
        $this->assertFalse($data['has_more']);
    }

    public function test_read_receipts_advance_only_after_the_other_party_reads(): void
    {
        $service = app(ConversationService::class);
        $conversation = $this->conversation();
        $reply = $service->send($conversation, 'vendor', 'Supplier', 'vendor@example.test', 'Price confirmed', null);
        $this->assertSame(0, $service->stream($conversation, 'vendor', $reply->id)['last_read_id']);
        $service->stream($conversation, 'customer', $conversation->id);
        $this->assertSame($reply->id, $service->stream($conversation, 'vendor', $reply->id)['last_read_id']);
    }

    public function test_network_retry_with_same_client_id_does_not_duplicate_a_message(): void
    {
        $service = app(ConversationService::class);
        $conversation = $this->conversation();
        $uuid = 'fd6c7c2e-d02e-4503-81ad-d271be232ca6';
        $first = $service->send($conversation, 'customer', 'Buyer', 'buyer@example.test', 'Need 20 units', $uuid);
        $retry = $service->send($conversation, 'customer', 'Buyer', 'buyer@example.test', 'Need 20 units', $uuid);
        $this->assertSame($first->id, $retry->id);
        $this->assertSame(1, Message::query()->where('parent_id', $conversation->id)->count());
    }

    public function test_backlog_is_paginated_without_losing_replies(): void
    {
        $service = app(ConversationService::class);
        $conversation = $this->conversation();
        $rows = [];
        for ($i = 0; $i < 103; $i++) {
            $rows[] = ['parent_id' => $conversation->id, 'store_id' => 10, 'customer_id' => 1,
                'sender_type' => 'vendor', 'name' => 'Supplier', 'email' => 'vendor@example.test',
                'content' => 'Reply ' . $i, 'created_at' => now(), 'updated_at' => now()];
        }
        DB::table('mp_messages')->insert($rows);
        $first = $service->stream($conversation, 'customer', $conversation->id);
        $this->assertCount(100, $first['messages']);
        $this->assertTrue($first['has_more']);
        $after = max(array_column($first['messages'], 'id'));
        $second = $service->stream($conversation, 'customer', $after);
        $this->assertCount(3, $second['messages']);
        $this->assertFalse($second['has_more']);
    }

    private function prepareProduct(): void
    {
        Schema::create('slugs', function (Blueprint $table) {
            $table->id(); $table->integer('reference_id'); $table->string('reference_type');
            $table->string('key'); $table->string('prefix')->default('products');
        });
        Schema::create('ec_products', function (Blueprint $table) {
            $table->id(); $table->integer('store_id'); $table->string('name');
            $table->string('status'); $table->boolean('is_variation')->default(false);
        });
        DB::table('ec_products')->insert(['id' => 100, 'store_id' => 10, 'name' => 'Test laptop', 'status' => 'published']);
        auth('customer')->setUser((new Customer())->forceFill(['id' => 1, 'name' => 'Buyer A', 'email' => 'a@example.test']));
    }

    public function test_quote_is_saved_for_the_authenticated_buyer_and_actual_supplier(): void
    {
        $this->prepareProduct();
        $request = Request::create('/sourcing/enquiry/100', 'POST', [
            'mode' => 'quote', 'quantity' => 25, 'delivery' => 'Nairobi', 'content' => 'Please confirm lead time.',
            'customer_id' => 2, 'store_id' => 20, 'email' => 'spoof@example.test',
        ]);
        app(SourcingController::class)->send('100', $request);
        $message = Message::query()->sole();
        $this->assertSame(1, (int) $message->customer_id);
        $this->assertSame(10, (int) $message->store_id);
        $this->assertSame('a@example.test', $message->email);
        $this->assertStringContainsString('Quantity: 25', $message->content);
        $this->assertStringContainsString('Delivery location: Nairobi', $message->content);
    }

    public function test_invalid_quote_quantity_does_not_create_a_conversation(): void
    {
        $this->prepareProduct();
        try {
            app(SourcingController::class)->send('100', Request::create('/sourcing/enquiry/100', 'POST', [
                'mode' => 'quote', 'quantity' => 0, 'content' => 'Need stock',
            ]));
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quantity', $exception->errors());
            $this->assertSame(0, Message::query()->count());
        }
    }

    public function test_supplier_cannot_start_a_quote_with_their_own_store(): void
    {
        $this->prepareProduct();
        auth('customer')->setUser((new Customer())->forceFill(['id' => 5, 'name' => 'Owner', 'email' => 'owner@example.test']));
        try {
            app(SourcingController::class)->enquiry('100', Request::create('/sourcing/enquiry/100'));
            $this->fail('Expected access denial');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_shared_chat_view_escapes_user_content_and_has_working_stream_and_reply_routes(): void
    {
        $this->prepareProduct();
        $conversation = $this->conversation();
        app(ConversationService::class)->send($conversation, 'vendor', 'Supplier', 'vendor@example.test', '<img src=x onerror=alert(1)>', null);
        $conversation->load(['store', 'replies']);
        $html = view('plugins/marketplace::messages.conversation', ['message' => $conversation, 'viewer' => 'customer'])->render();
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $html);
        $this->assertStringContainsString('/customer/messages/' . $conversation->id . '/stream', $html);
        $this->assertStringContainsString('/customer/messages/' . $conversation->id . '/reply', $html);
    }

    public function test_live_inbox_only_shows_current_buyers_latest_reply_and_unread_state(): void
    {
        auth('customer')->setUser((new Customer())->forceFill(['id' => 1, 'name' => 'Buyer A', 'email' => 'a@example.test']));
        $service = app(ConversationService::class);
        $mine = $this->conversation();
        $other = $this->conversation(2, 20);
        $service->send($mine, 'vendor', 'Supplier', 'vendor@example.test', 'Your bulk price is ready', null);
        $service->send($other, 'vendor', 'Other supplier', 'other@example.test', 'Do not expose this preview', null);
        $data = app(CustomerMessageController::class)->inbox()->getData(true);
        $this->assertCount(1, $data['conversations']);
        $this->assertSame('Your bulk price is ready', $data['conversations'][0]['preview']);
        $this->assertTrue($data['conversations'][0]['unread']);
        $this->assertStringNotContainsString('Do not expose', json_encode($data));
    }
}
