<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\Message;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
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

        $messages = Message::query()
            ->where('customer_id', auth('customer')->id())
            ->whereNull('parent_id')
            ->with(['store', 'replies'])
            ->latest('updated_at')
            ->paginate(10);

        return Theme::scope(
            'marketplace.customer-messages.index',
            compact('messages'),
            'plugins/marketplace::themes.customer-messages.index'
        )->render();
    }

    public function show(string $id)
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $message = $this->findConversation($id);

        Message::query()
            ->where('parent_id', $message->getKey())
            ->where('sender_type', 'vendor')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        SeoHelper::setTitle(__('Conversation with :store', ['store' => $message->store->name]));

        return Theme::scope(
            'marketplace.customer-messages.show',
            compact('message'),
            'plugins/marketplace::themes.customer-messages.show'
        )->render();
    }

    public function reply(string $id, Request $request): RedirectResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $data = $request->validate(['content' => ['required', 'string', 'max:2000']]);
        $message = $this->findConversation($id);
        $customer = auth('customer')->user();

        Message::query()->create([
            'parent_id' => $message->getKey(),
            'store_id' => $message->store_id,
            'customer_id' => $customer->getKey(),
            'sender_type' => 'customer',
            'name' => $customer->name,
            'email' => $customer->email,
            'content' => $data['content'],
        ]);
        $message->touch();

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
}
