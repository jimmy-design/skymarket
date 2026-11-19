<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\Message;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Botble\Marketplace\Supports\ConversationService;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Models\Store;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerMessageController extends BaseController
{
    public function __construct()
    {
        Theme::asset()->add('customer-style', 'vendor/core/plugins/ecommerce/css/customer.css', ['bootstrap-css']);
    }

    public function index()
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        SeoHelper::setTitle(__('Messages'));
        Theme::breadcrumb()->add(__('Messages'), route('customer.messages.index'));

        $conversations = $this->customerConversations();
        $message = $conversations->first();

        if ($message) {
            $this->markConversationRead($message);
            $conversations = $this->customerConversations();
            $message = $conversations->first();
            $message?->load('replies');
        }

        return Theme::scope(
            'marketplace.customer-messages.index',
            compact('conversations', 'message'),
            'plugins/marketplace::themes.customer-messages.index'
        )->render();
    }

    public function start(string $store, Request $request, ?ConversationService $service = null): RedirectResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);
        $service ??= app(ConversationService::class);
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'content' => ['nullable', 'string', 'max:2000', 'regex:/\S/u'],
        ]);
        $supplier = Store::query()->wherePublished()->findOrFail($store);
        $customer = auth('customer')->user();
        $product = Product::query()->wherePublished()->where('store_id', $supplier->id)
            ->where('is_variation', false)->findOrFail($request->input('product_id'));
        $content = trim((string) ($data['content'] ?? ''));
        $hasComposedContent = $content !== '';
        if (! $hasComposedContent) {
            $content = __('Hello, I have a question about :product.', ['product' => $product->name]) . "\n" . $product->url;
        }

        [$conversation, $created] = Cache::lock('marketplace:start:' . $customer->id . ':' . $supplier->id, 10)->block(5, function () use ($customer, $supplier, $content) {
            $existing = Message::query()->where([
                'customer_id' => $customer->id, 'store_id' => $supplier->id, 'parent_id' => null,
            ])->first();

            if ($existing) {
                return [$existing, false];
            }

            return [Message::query()->create([
                'customer_id' => $customer->id, 'store_id' => $supplier->id, 'parent_id' => null,
                'sender_type' => 'customer', 'name' => $customer->name, 'email' => $customer->email,
                'content' => $content,
            ]), true];
        });

        if (! $created && $hasComposedContent) {
            $service->send($conversation, 'customer', $customer->name, $customer->email, $content, null);
        }

        return redirect()->route('customer.messages.show', $conversation);
    }

    public function unread()
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);
        $customer = auth('customer')->user();
        $vendor = $customer->is_vendor && $customer->store?->id;
        $count = Message::query()->whereNull('read_at')
            ->when($vendor, fn ($query) => $query->where('store_id', $customer->store->id)->where('sender_type', 'customer'),
                fn ($query) => $query->where('customer_id', $customer->id)->where('sender_type', 'vendor'))->count();

        return response()->json(['unread' => $count])->header('Cache-Control', 'private, no-store');
    }

    public function inbox(): JsonResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);
        $messages = Message::query()->where('customer_id', auth('customer')->id())->whereNull('parent_id')
            ->with(['store', 'latestReply'])
            ->withCount(['replies as unread_count' => fn ($query) => $query->where('sender_type', 'vendor')->whereNull('read_at')])
            ->latest('updated_at')->limit(10)->get();

        return response()->json(['conversations' => $messages->map(fn (Message $message) => [
            'url' => route('customer.messages.show', $message),
            'supplier' => $message->store?->name ?: __('Unavailable supplier'),
            'preview' => mb_substr($message->latestReply?->content ?: $message->content, 0, 110),
            'updated_at' => ($message->latestReply?->created_at ?: $message->created_at)->toIso8601String(),
            'unread' => $message->unread_count > 0,
        ])])->header('Cache-Control', 'private, no-store');
    }

    public function show(string $id)
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $message = $this->findConversation($id);
        $this->markConversationRead($message);
        $conversations = $this->customerConversations();

        SeoHelper::setTitle(__('Conversation with :store', ['store' => $message->store?->name ?: __('Supplier')]));

        return Theme::scope(
            'marketplace.customer-messages.show',
            compact('message', 'conversations'),
            'plugins/marketplace::themes.customer-messages.show'
        )->render();
    }

    public function stream(string $id, Request $request, ConversationService $service): JsonResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);
        $request->validate(['after' => ['nullable', 'integer', 'min:0']]);
        $conversation = $service->find($id, 'customer', (int) auth('customer')->id());

        return response()->json($service->stream($conversation, 'customer', (int) $request->input('after', 0)))
            ->header('Cache-Control', 'private, no-store');
    }

    public function reply(string $id, Request $request, ConversationService $service): RedirectResponse|JsonResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $data = $request->validate(['content' => ['required', 'string', 'max:2000', 'regex:/\S/u'], 'client_id' => ['nullable', 'uuid']]);
        $message = $service->find($id, 'customer', (int) auth('customer')->id());
        abort_unless($message->store, 404);
        $customer = auth('customer')->user();

        $reply = $service->send($message, 'customer', $customer->name, $customer->email, $data['content'], $data['client_id'] ?? null);
        if ($request->expectsJson()) {
            return response()->json(['message' => $service->serialize($reply)]);
        }

        return back()->with('success_msg', __('Reply sent successfully.'));
    }

    private function findConversation(string $id): Message
    {
        return Message::query()
            ->where('customer_id', auth('customer')->id())
            ->whereNull('parent_id')
            ->with(['store', 'replies'])
            ->findOrFail($id);
    }

    private function customerConversations()
    {
        return Message::query()
            ->where('customer_id', auth('customer')->id())
            ->whereNull('parent_id')
            ->with(['store', 'latestReply'])
            ->withCount(['replies as unread_count' => fn ($query) => $query->where('sender_type', 'vendor')->whereNull('read_at')])
            ->latest('updated_at')
            ->limit(50)
            ->get();
    }

    private function markConversationRead(Message $message): void
    {
        Message::query()
            ->where('parent_id', $message->getKey())
            ->where('sender_type', 'vendor')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $message->load('replies');
    }
}
