<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogPostResource;
use App\Services\BlogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function __construct(private readonly BlogService $blog) {}

    public function index(Request $request)
    {
        $page = $this->blog->listAll(
            q: $request->string('q')->toString() ?: null,
            published: $request->has('published') ? $request->boolean('published') : null,
            perPage: $request->integer('per_page', 20),
        );
        return BlogPostResource::collection($page);
    }

    public function show(int $id)
    {
        $post = $this->blog->findOrFail($id);
        return (new BlogPostResource($post))->additional(['meta' => ['expand' => true]]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $data = $this->handleCover($request, $data);
        $post = $this->blog->create($data);
        return (new BlogPostResource($post))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $id)
    {
        $post = $this->blog->findOrFail($id);
        $data = $this->validatePayload($request, $id);
        $data = $this->handleCover($request, $data, $post->cover_image);
        $post = $this->blog->update($post, $data);
        return new BlogPostResource($post);
    }

    public function destroy(int $id)
    {
        $post = $this->blog->findOrFail($id);
        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }
        $this->blog->delete($post);
        return response()->json(['ok' => true]);
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title'           => ['required', 'string', 'max:200'],
            'slug'            => ['nullable', 'string', 'max:200'],
            'excerpt'         => ['nullable', 'string', 'max:500'],
            'body'            => ['required', 'string'],
            'tags'            => ['nullable', 'array'],
            'tags.*'          => ['string', 'max:50'],
            'reading_minutes' => ['nullable', 'integer', 'min:1', 'max:300'],
            'is_published'    => ['boolean'],
            'published_at'    => ['nullable', 'date'],
            'cover'           => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function handleCover(Request $request, array $data, ?string $existing = null): array
    {
        if ($request->hasFile('cover')) {
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }
            $data['cover_image'] = $request->file('cover')->store('blog/covers', 'public');
        }
        unset($data['cover']);
        return $data;
    }
}
