<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

class ClientService
{
    public function listPublished(): Collection
    {
        return Client::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function all(): Collection
    {
        return Client::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function findOrFail(int $id): Client
    {
        return Client::query()->findOrFail($id);
    }

    public function create(array $data): Client
    {
        return Client::create($data);
    }

    public function update(Client $client, array $data): Client
    {
        $client->update($data);
        return $client->fresh();
    }

    public function delete(Client $client): void
    {
        $client->delete();
    }
}
