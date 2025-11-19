<?php

namespace App\Interface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?Product;

    public function create(array $data): Product;

    public function update(int $id, array $data): ?Product;

    public function delete(int $id): bool;
}
