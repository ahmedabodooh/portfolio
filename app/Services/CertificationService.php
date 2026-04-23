<?php

namespace App\Services;

use App\Models\Certification;
use Illuminate\Database\Eloquent\Collection;

class CertificationService
{
    public function listPublished(): Collection
    {
        return Certification::query()
            ->published()
            ->orderBy('sort_order')
            ->get();
    }

    public function all(): Collection
    {
        return Certification::query()->orderBy('sort_order')->get();
    }

    public function findOrFail(int $id): Certification
    {
        return Certification::query()->findOrFail($id);
    }

    public function create(array $data): Certification
    {
        return Certification::create($data);
    }

    public function update(Certification $certification, array $data): Certification
    {
        $certification->update($data);
        return $certification->fresh();
    }

    public function delete(Certification $certification): void
    {
        $certification->delete();
    }
}
