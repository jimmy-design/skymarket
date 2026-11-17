<?php

namespace Botble\Marketplace\Supports;

use Botble\Marketplace\Models\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ConversationService
{
    public function find(string $id, string $role, int $ownerId): Message
    {
        abort_unless(in_array($role, ['customer', 'vendor'], true), 403);

        return Message::query()->whereNull('parent_id')
            ->where($role === 'vendor' ? 'store_id' : 'customer_id', $ownerId)
            ->with(['store', 'customer'])->findOrFail($id);
    }

    public function serialize(Message $message): array
    {
        return [
            'id' => $message->getKey(),
            'sender' => $message->sender_type,
            'content' => $message->content,
            'created_at' => $message->created_at->toIso8601String(),
            'read_at' => $message->read_at?->toIso8601String(),
        ];
    }

    public function stream(Message $conversation, string $role, int $after): array
    {
        $items = Message::query()
            ->where(fn ($query) => $query->whereKey($conversation->getKey())->orWhere('parent_id', $conversation->getKey()))
            ->where('id', '>', $after)->orderBy('id')->limit(100)->get();

        $receivedIds = $items->where('sender_type', '!=', $role)->whereNull('read_at')->pluck('id');
        if ($receivedIds->isNotEmpty()) {
            Message::query()->whereIn('id', $receivedIds)->whereNull('read_at')->update(['read_at' => now()]);
        }

        $lastReadId = Message::query()
            ->where(fn ($query) => $query->whereKey($conversation->getKey())->orWhere('parent_id', $conversation->getKey()))
            ->where('sender_type', $role)->whereNotNull('read_at')->max('id');

        return [
            'messages' => $items->map(fn (Message $item) => $this->serialize($item))->all(),
            'last_read_id' => (int) $lastReadId,
            'has_more' => $items->count() === 100,
        ];
    }

    public function send(Message $conversation, string $role, string $name, string $email, string $content, ?string $clientId): Message
    {
        $create = function () use ($conversation, $role, $name, $email, $content) {
            return DB::transaction(function () use ($conversation, $role, $name, $email, $content) {
                $message = Message::query()->create([
                    'parent_id' => $conversation->getKey(), 'store_id' => $conversation->store_id,
                    'customer_id' => $conversation->customer_id, 'sender_type' => $role,
                    'name' => $name, 'email' => $email, 'content' => trim($content),
                ]);
                $conversation->touch();

                return $message;
            });
        };

        if (! $clientId) {
            return $create();
        }

        $key = 'marketplace:reply:' . $conversation->getKey() . ':' . $role . ':' . $clientId;

        return Cache::lock($key . ':lock', 10)->block(5, function () use ($key, $create, $conversation) {
            $id = Cache::remember($key, now()->addMinutes(15), fn () => $create()->getKey());

            return Message::query()->where('parent_id', $conversation->getKey())->findOrFail($id);
        });
    }
}
