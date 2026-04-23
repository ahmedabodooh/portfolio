<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExperienceService;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    public function __construct(private readonly ExperienceService $experiences) {}

    public function index()
    {
        return response()->json($this->experiences->all());
    }

    public function show(int $id)
    {
        return response()->json($this->experiences->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        return response()->json($this->experiences->create($data), 201);
    }

    public function update(Request $request, int $id)
    {
        $experience = $this->experiences->findOrFail($id);
        $data = $this->validatePayload($request);
        return response()->json($this->experiences->update($experience, $data));
    }

    public function destroy(int $id)
    {
        $this->experiences->delete($this->experiences->findOrFail($id));
        return response()->json(['ok' => true]);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'company'      => ['required', 'string', 'max:150'],
            'role'         => ['required', 'string', 'max:150'],
            'location'     => ['nullable', 'string', 'max:150'],
            'period'       => ['nullable', 'string', 'max:120'],
            'started_at'   => ['nullable', 'date'],
            'ended_at'     => ['nullable', 'date', 'after_or_equal:started_at'],
            'summary'      => ['nullable', 'string', 'max:2000'],
            'highlights'   => ['nullable', 'array'],
            'highlights.*' => ['string', 'max:300'],
            'sort_order'   => ['nullable', 'integer'],
            'is_published' => ['boolean'],
        ]);
    }
}
