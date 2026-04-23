<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\CertificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificationController extends Controller
{
    public function __construct(private readonly CertificationService $certifications) {}

    public function index()
    {
        return response()->json($this->certifications->all());
    }

    public function show(int $id)
    {
        return response()->json($this->certifications->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request);
        }

        return response()->json($this->certifications->create($data), 201);
    }

    public function update(Request $request, int $id)
    {
        $cert = $this->certifications->findOrFail($id);
        $data = $this->validatePayload($request);

        if ($request->hasFile('image')) {
            if ($cert->image) {
                Storage::disk('public')->delete($cert->image);
            }
            $data['image'] = $this->storeImage($request);
        }

        return response()->json($this->certifications->update($cert, $data));
    }

    public function destroy(int $id)
    {
        $cert = $this->certifications->findOrFail($id);
        if ($cert->image) {
            Storage::disk('public')->delete($cert->image);
        }
        $this->certifications->delete($cert);
        return response()->json(['ok' => true]);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'title'          => ['required', 'string', 'max:200'],
            'issuer'         => ['required', 'string', 'max:150'],
            'image'          => ['nullable', 'file', 'max:4096', 'mimetypes:image/*'],
            'credential_url' => ['nullable', 'url', 'max:500'],
            'year'           => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'sort_order'     => ['nullable', 'integer'],
            'is_published'   => ['nullable', 'boolean'],
        ]);
    }

    private function storeImage(Request $request): string
    {
        return $request->file('image')->store('certifications', 'public');
    }
}
