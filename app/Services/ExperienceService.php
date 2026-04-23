<?php

namespace App\Services;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Collection;

class ExperienceService
{
    public function listPublished(): Collection
    {
        return Experience::query()
            ->published()
            ->orderBy('sort_order')
            ->get();
    }

    public function all(): Collection
    {
        return Experience::query()->orderBy('sort_order')->get();
    }

    public function findOrFail(int $id): Experience
    {
        return Experience::query()->findOrFail($id);
    }

    public function create(array $data): Experience
    {
        return Experience::create($data);
    }

    public function update(Experience $experience, array $data): Experience
    {
        $experience->update($data);
        return $experience->fresh();
    }

    public function delete(Experience $experience): void
    {
        $experience->delete();
    }
}
