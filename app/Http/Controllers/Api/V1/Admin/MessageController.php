<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContactService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private readonly ContactService $contact) {}

    public function index(Request $request)
    {
        return response()->json(
            $this->contact->list(
                unreadOnly: $request->boolean('unread'),
                perPage: $request->integer('per_page', 20),
            )
        );
    }

    public function show(int $id)
    {
        $msg = $this->contact->findOrFail($id);
        if (! $msg->is_read) {
            $msg = $this->contact->markRead($msg);
        }
        return response()->json($msg);
    }

    public function markRead(Request $request, int $id)
    {
        $msg = $this->contact->findOrFail($id);
        return response()->json($this->contact->markRead($msg, $request->boolean('read', true)));
    }

    public function destroy(int $id)
    {
        $this->contact->delete($this->contact->findOrFail($id));
        return response()->json(['ok' => true]);
    }

    public function unreadCount()
    {
        return response()->json(['count' => $this->contact->unreadCount()]);
    }
}
