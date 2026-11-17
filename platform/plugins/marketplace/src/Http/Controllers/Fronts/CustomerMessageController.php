<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\Message;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Botble\Marketplace\Supports\ConversationService;
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

        $messages = Message::query()
            ->where('customer_id', auth('customer')->id())
            ->whereNull('parent_id')
            ->with(['store', 'latestReply'])
            ->withCount(['replies as unread_count' => fn ($query) => $query->where('sender_type', 'vendor')->whereNull('read_at')])
            ->latest('updated_at')
            ->paginate(10);

        return Theme::scope(
            'marketplace.customer-messages.index',
            compact('messages'),
            'plugins/marketplace::themes.customer-messages.index'
        )->render();
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

        Message::query()
            ->where('parent_id', $message->getKey())
            ->where('sender_type', 'vendor')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        SeoHelper::setTitle(__('Conversation with :store', ['store' => $message->store?->name ?: __('Supplier')]));

        return Theme::scope(
            'marketplace.customer-messages.show',
            compact('message'),
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
}
