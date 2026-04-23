<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProjectService
{
    public function listPublished(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        return Project::query()
            ->published()
            ->when(isset($filters['featured']), fn (Builder $q) => $q->where('is_featured', (bool) $filters['featured']))
            ->when(! empty($filters['category']), fn (Builder $q) => $q->where('category', $filters['category']))
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    public function listAll(?string $q = null, ?bool $published = null, int $perPage = 20): LengthAwarePaginator
    {
        return Project::query()
            ->when($q, fn (Builder $b, string $term) => $b->where(function ($w) use ($term) {
                $w->where('title', 'like', "%{$term}%")
                    ->orWhere('tagline', 'like', "%{$term}%")
                    ->orWhere('client', 'like', "%{$term}%");
            }))
            ->when(! is_null($published), fn (Builder $b) => $b->where('is_published', $published))
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Project
    {
        return Project::query()->where('slug', $slug)->first();
    }

    public function findOrFail(int $id): Project
    {
        return Project::query()->findOrFail($id);
    }

    public function create(array $data): Project
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'] ?? '');
        return Project::create($data);
    }

    public function update(Project $project, array $data): Project
    {
        if (! empty($data['slug']) && $data['slug'] !== $project->slug) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $project->id);
        }
        $project->update($data);
        return $project->fresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'project-' . Str::random(6);
        $slug = $base;
        $i = 2;
        while (Project::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }
}
