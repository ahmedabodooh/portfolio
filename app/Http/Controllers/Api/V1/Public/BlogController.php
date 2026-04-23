<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogPostResource;
use App\Services\BlogService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(private readonly BlogService $blog) {}

    public function index(Request $request)
    {
        $page = $this->blog->listPublished(
            tag: $request->string('tag')->toString() ?: null,
            perPage: $request->integer('per_page', 20),
        );
        return BlogPostResource::collection($page);
    }

    public function show(string $slug, Request $request)
    {
        $post = $this->blog->findBySlug($slug);
        abort_if(! $post || ! $post->is_published, 404);
        return (new BlogPostResource($post))->additional(['meta' => ['expand' => $request->boolean('expand')]]);
    }
}
