<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BlogService
{
    public function listPublished(?string $tag = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->published()
            ->when($tag, fn (Builder $q, string $t) => $q->whereJsonContains('tags', $t))
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    public function listAll(?string $q = null, ?bool $published = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->when($q, fn (Builder $b, string $term) => $b->where(function ($w) use ($term) {
                $w->where('title', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%");
            }))
            ->when(! is_null($published), fn (Builder $b) => $b->where('is_published', $published))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findBySlug(string $slug): ?BlogPost
    {
        return BlogPost::query()->where('slug', $slug)->first();
    }

    public function findOrFail(int $id): BlogPost
    {
        return BlogPost::query()->findOrFail($id);
    }

    public function create(array $data): BlogPost
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'] ?? '');
        return BlogPost::create($data);
    }

    public function update(BlogPost $post, array $data): BlogPost
    {
        if (! empty($data['slug']) && $data['slug'] !== $post->slug) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $post->id);
        }
        $post->update($data);
        return $post->fresh();
    }

    public function delete(BlogPost $post): void
    {
        $post->delete();
    }

    private function baseQuery(): Builder
    {
        return BlogPost::query();
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'post-' . Str::random(6);
        $slug = $base;
        $i = 2;
        while (BlogPost::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }
}
