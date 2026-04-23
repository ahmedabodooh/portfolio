<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'slug'             => $this->slug,
            'title'            => $this->title,
            'excerpt'          => $this->excerpt,
            'body_markdown'    => $this->when($request->boolean('expand'), $this->body),
            'body_html'        => $this->when(
                $request->boolean('expand'),
                fn () => Str::markdown($this->body ?? '', ['html_input' => 'escape', 'allow_unsafe_links' => false])
            ),
            'cover_image'      => $this->cover_image ? asset('storage/' . $this->cover_image) : null,
            'tags'             => $this->tags,
            'reading_minutes'  => $this->reading_minutes,
            'is_published'     => (bool) $this->is_published,
            'published_at'     => $this->published_at?->toIso8601String(),
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
            'links' => [
                'self' => url("/api/v1/blog/{$this->slug}"),
            ],
        ];
    }
}
