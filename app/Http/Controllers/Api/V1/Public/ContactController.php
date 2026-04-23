<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\ContactService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(private readonly ContactService $contact) {}

    public function store(Request $request)
    {
        if (filled($request->input('website'))) {
            return response()->json(['ok' => true]);
        }

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email', 'max:180'],
            'subject' => ['nullable', 'string', 'max:180'],
            'message' => ['required', 'string', 'min:5', 'max:4000'],
        ]);

        $this->contact->store($data, $request->ip());

        return response()->json(['ok' => true, 'message' => 'Thanks! Your message landed.']);
    }
}
