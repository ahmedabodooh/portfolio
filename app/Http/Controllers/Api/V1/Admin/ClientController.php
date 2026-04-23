<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clients) {}

    public function index()
    {
        return response()->json($this->clients->all());
    }

    public function show(int $id)
    {
        return response()->json($this->clients->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request, requireLogo: true);
        $data['logo'] = $this->storeLogo($request);
        $client = $this->clients->create($data);
        return response()->json($client, 201);
    }

    public function update(Request $request, int $id)
    {
        $client = $this->clients->findOrFail($id);
        $data = $this->validatePayload($request);

        if ($request->hasFile('logo')) {
            if ($client->logo) {
                Storage::disk('public')->delete($client->logo);
            }
            $data['logo'] = $this->storeLogo($request);
        }

        return response()->json($this->clients->update($client, $data));
    }

    public function destroy(int $id)
    {
        $client = $this->clients->findOrFail($id);
        if ($client->logo) {
            Storage::disk('public')->delete($client->logo);
        }
        $this->clients->delete($client);
        return response()->json(['ok' => true]);
    }

    private function validatePayload(Request $request, bool $requireLogo = false): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:120'],
            'logo'         => [$requireLogo ? 'required' : 'nullable', 'file', 'max:4096', 'mimetypes:image/*'],
            'website'      => ['nullable', 'url', 'max:500'],
            'sort_order'   => ['nullable', 'integer'],
            'is_published' => ['nullable', 'boolean'],
        ]);
    }

    private function storeLogo(Request $request): string
    {
        return $request->file('logo')->store('clients', 'public');
    }
}
