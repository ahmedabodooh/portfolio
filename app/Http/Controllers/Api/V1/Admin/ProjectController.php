<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projects) {}

    public function index(Request $request)
    {
        $page = $this->projects->listAll(
            q: $request->string('q')->toString() ?: null,
            published: $request->has('published') ? $request->boolean('published') : null,
            perPage: $request->integer('per_page', 20),
        );
        return ProjectResource::collection($page);
    }

    public function show(int $id)
    {
        return new ProjectResource($this->projects->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $data = $this->handleImages($request, $data);
        $project = $this->projects->create($data);
        return (new ProjectResource($project))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $id)
    {
        $project = $this->projects->findOrFail($id);
        $data = $this->validatePayload($request, $id);
        $data = $this->handleImages($request, $data, $project->cover_image);
        $project = $this->projects->update($project, $data);
        return new ProjectResource($project);
    }

    public function destroy(int $id)
    {
        $project = $this->projects->findOrFail($id);
        if ($project->cover_image) {
            Storage::disk('public')->delete($project->cover_image);
        }
        foreach ((array) $project->gallery as $path) {
            Storage::disk('public')->delete($path);
        }
        $this->projects->delete($project);
        return response()->json(['ok' => true]);
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title'        => ['required', 'string', 'max:200'],
            'slug'         => ['nullable', 'string', 'max:200'],
            'category'     => ['nullable', 'string', 'max:80'],
            'client'       => ['nullable', 'string', 'max:120'],
            'role'         => ['nullable', 'string', 'max:120'],
            'year'         => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'tagline'      => ['nullable', 'string', 'max:300'],
            'summary'      => ['nullable', 'string', 'max:2000'],
            'description'  => ['nullable', 'string'],
            'highlights'   => ['nullable', 'array'],
            'highlights.*' => ['string', 'max:300'],
            'tech_stack'   => ['nullable', 'array'],
            'tech_stack.*' => ['string', 'max:60'],
            'live_url'     => ['nullable', 'url', 'max:500'],
            'repo_url'     => ['nullable', 'url', 'max:500'],
            'is_featured'  => ['boolean'],
            'is_published' => ['boolean'],
            'sort_order'   => ['nullable', 'integer'],
            'cover'        => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,avif,bmp,tiff,tif,heic,heif,ico', 'max:10240'],
            'gallery_files' => ['nullable', 'array'],
            'gallery_files.*' => ['file', 'mimes:jpeg,jpg,png,gif,webp,svg,avif,bmp,tiff,tif,heic,heif,ico', 'max:10240'],
        ]);
    }

    private function handleImages(Request $request, array $data, ?string $existingCover = null): array
    {
        if ($request->hasFile('cover')) {
            if ($existingCover) {
                Storage::disk('public')->delete($existingCover);
            }
            $data['cover_image'] = $request->file('cover')->store('projects/covers', 'public');
        }
        unset($data['cover']);

        if ($request->hasFile('gallery_files')) {
            $paths = [];
            foreach ($request->file('gallery_files') as $file) {
                $paths[] = $file->store('projects/gallery', 'public');
            }
            $data['gallery'] = $paths;
        }
        unset($data['gallery_files']);

        return $data;
    }
}
