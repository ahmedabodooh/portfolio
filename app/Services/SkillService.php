<?php

namespace App\Services;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Collection;

class SkillService
{
    public function groupedPublished(): array
    {
        return Skill::query()
            ->published()
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category')
            ->toArray();
    }

    public function all(): Collection
    {
        return Skill::query()->orderBy('sort_order')->get();
    }

    public function findOrFail(int $id): Skill
    {
        return Skill::query()->findOrFail($id);
    }

    public function create(array $data): Skill
    {
        return Skill::create($data);
    }

    public function update(Skill $skill, array $data): Skill
    {
        $skill->update($data);
        return $skill->fresh();
    }

    public function delete(Skill $skill): void
    {
        $skill->delete();
    }
}
