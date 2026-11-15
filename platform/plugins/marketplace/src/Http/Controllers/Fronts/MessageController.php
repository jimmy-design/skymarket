<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\Message;
use Botble\Marketplace\Tables\MessageTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends BaseController
{
    public function index(MessageTable $messageTable)
    {
        if (! MarketplaceHelper::isEnabledMessagingSystem()) {
            abort(404);
        }

        $this->pageTitle(__('Messages'));

        return $messageTable->renderTable();
    }

    public function show(string $id)
    {
        if (! MarketplaceHelper::isEnabledMessagingSystem()) {
            abort(404);
        }

        $message = Message::query()
            ->where('store_id', auth('customer')->user()->store->id)
            ->whereNull('parent_id')
            ->with(['store', 'customer', 'replies'])
            ->findOrFail($id);

        Message::query()
            ->where(fn ($query) => $query->whereKey($message->getKey())->orWhere('parent_id', $message->getKey()))
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->pageTitle(__('Viewing message #:id', ['id' => $message->getKey()]));

        return MarketplaceHelper::view('vendor-dashboard.messages.show', compact('message'));
    }

    public function reply(string $id, Request $request): RedirectResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $data = $request->validate(['content' => ['required', 'string', 'max:2000']]);
        $vendor = auth('customer')->user();
        $message = Message::query()
            ->where('store_id', $vendor->store->id)
            ->whereNull('parent_id')
            ->findOrFail($id);

        Message::query()->create([
            'parent_id' => $message->getKey(),
            'store_id' => $message->store_id,
            'customer_id' => $message->customer_id,
            'sender_type' => 'vendor',
            'name' => $vendor->store->name,
            'email' => $vendor->store->email ?: $vendor->email,
            'content' => $data['content'],
        ]);
        $message->touch();

        return back()->with('success_msg', __('Reply sent successfully.'));
    }

    public function destroy(string $id)
    {
        if (! MarketplaceHelper::isEnabledMessagingSystem()) {
            abort(404);
        }

        $message = Message::query()
            ->where('store_id', auth('customer')->user()->store->id)
            ->whereNull('parent_id')
            ->findOrFail($id);

        Message::query()->where('parent_id', $message->getKey())->delete();

        return DeleteResourceAction::make($message);
    }
}
