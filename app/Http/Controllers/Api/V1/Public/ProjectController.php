<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projects) {}

    public function index(Request $request)
    {
        $page = $this->projects->listPublished(
            filters: array_filter([
                'featured' => $request->has('featured') ? $request->boolean('featured') : null,
                'category' => $request->string('category')->toString() ?: null,
            ], fn ($v) => ! is_null($v)),
            perPage: $request->integer('per_page', 20),
        );

        return ProjectResource::collection($page);
    }

    public function show(string $slug, Request $request)
    {
        $project = $this->projects->findBySlug($slug);
        abort_if(! $project || ! $project->is_published, 404);
        return (new ProjectResource($project))->additional(['meta' => ['expand' => $request->boolean('expand')]]);
    }
}
