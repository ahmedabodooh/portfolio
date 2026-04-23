<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\SkillService;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function __construct(private readonly SkillService $skills) {}

    public function index()
    {
        return response()->json($this->skills->all());
    }

    public function show(int $id)
    {
        return response()->json($this->skills->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $skill = $this->skills->create($data);
        return response()->json($skill, 201);
    }

    public function update(Request $request, int $id)
    {
        $skill = $this->skills->findOrFail($id);
        $data = $this->validatePayload($request);
        return response()->json($this->skills->update($skill, $data));
    }

    public function destroy(int $id)
    {
        $skill = $this->skills->findOrFail($id);
        $this->skills->delete($skill);
        return response()->json(['ok' => true]);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:120'],
            'category'     => ['required', 'string', 'max:80'],
            'proficiency'  => ['nullable', 'integer', 'min:0', 'max:100'],
            'sort_order'   => ['nullable', 'integer'],
            'is_published' => ['boolean'],
        ]);
    }
}
