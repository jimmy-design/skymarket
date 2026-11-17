<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Facades\Assets;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\Message;
use Botble\Marketplace\Tables\MessageTable;
use Botble\Marketplace\Supports\ConversationService;
use Illuminate\Http\JsonResponse;
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
        Assets::addScriptsDirectly([asset('js/skymarket-vendor-inbox.js')]);

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

    public function stream(string $id, Request $request, ConversationService $service): JsonResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);
        $request->validate(['after' => ['nullable', 'integer', 'min:0']]);
        $conversation = $service->find($id, 'vendor', (int) auth('customer')->user()->store->id);

        return response()->json($service->stream($conversation, 'vendor', (int) $request->input('after', 0)))
            ->header('Cache-Control', 'private, no-store');
    }

    public function reply(string $id, Request $request, ConversationService $service): RedirectResponse|JsonResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $data = $request->validate(['content' => ['required', 'string', 'max:2000', 'regex:/\S/u'], 'client_id' => ['nullable', 'uuid']]);
        $vendor = auth('customer')->user();
        $message = $service->find($id, 'vendor', (int) $vendor->store->id);
        abort_unless($message->customer_id, 422);
        $reply = $service->send($message, 'vendor', $vendor->store->name, $vendor->store->email ?: $vendor->email, $data['content'], $data['client_id'] ?? null);
        if ($request->expectsJson()) {
            return response()->json(['message' => $service->serialize($reply)]);
        }

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
