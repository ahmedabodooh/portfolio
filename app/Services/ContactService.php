<?php

namespace App\Services;

use App\Models\ContactMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ContactService
{
    public function store(array $data, ?string $ip = null): ContactMessage
    {
        return ContactMessage::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'subject'    => $data['subject'] ?? null,
            'message'    => $data['message'],
            'ip_address' => $ip,
        ]);
    }

    public function list(?bool $unreadOnly = null, int $perPage = 20): LengthAwarePaginator
    {
        return ContactMessage::query()
            ->when($unreadOnly === true, fn (Builder $q) => $q->where('is_read', false))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): ContactMessage
    {
        return ContactMessage::query()->findOrFail($id);
    }

    public function markRead(ContactMessage $message, bool $read = true): ContactMessage
    {
        $message->update(['is_read' => $read]);
        return $message->fresh();
    }

    public function delete(ContactMessage $message): void
    {
        $message->delete();
    }

    public function unreadCount(): int
    {
        return ContactMessage::query()->where('is_read', false)->count();
    }
}
