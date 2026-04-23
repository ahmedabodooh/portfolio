<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'slug'         => $this->slug,
            'title'        => $this->title,
            'tagline'      => $this->tagline,
            'summary'      => $this->summary,
            'description'  => $this->when($request->boolean('expand'), $this->description),
            'category'     => $this->category,
            'client'       => $this->client,
            'role'         => $this->role,
            'year'         => $this->year,
            'tech_stack'   => $this->tech_stack,
            'highlights'   => $this->highlights,
            'cover_image'  => $this->cover_image ? asset('storage/' . $this->cover_image) : null,
            'gallery'      => collect($this->gallery ?? [])->map(fn ($p) => asset('storage/' . $p))->values(),
            'live_url'     => $this->live_url,
            'repo_url'     => $this->repo_url,
            'is_featured'  => (bool) $this->is_featured,
            'is_published' => (bool) $this->is_published,
            'sort_order'   => (int) $this->sort_order,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
            'links' => [
                'self' => url("/api/v1/projects/{$this->slug}"),
            ],
        ];
    }
}
