<?php

namespace App\Repositories;

use App\Interface\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function paginate(int $perPage = 15, array $filters = [], array $sort = []): LengthAwarePaginator
    {
        $query = Product::query();
        if (isset($filters['price_min'])) {
            $query->where('price', '>=', (float) $filters['price_min']);
        }
        if (isset($filters['price_max'])) {
            $query->where('price', '<=', (float) $filters['price_max']);
        }
        if (isset($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }
        if (isset($filters['availability'])) {
            if ($filters['availability'] === 'in_stock') {
                $query->where('stock_quantity', '>', 0);
            } elseif ($filters['availability'] === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            }
        }
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(fn (Builder $qBuilder) => $qBuilder
                ->where('name', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%"));
        }
        $allowedSorts = ['price', 'created_at', 'name'];
        $sortBy = Arr::get($sort, 'by', 'created_at');
        $direction = strtolower(Arr::get($sort, 'dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $query->orderBy($sortBy, $direction);
        $perPage = max(1, min(100, $perPage));
        return $query->paginate($perPage);
    }

    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(int $id, array $data): ?Product
    {
        $product = $this->find($id);
        if ($product === null) {
            return null;
        }
        $product->fill($data);
        $product->save();
        return $product;
    }

    public function delete(int $id): bool
    {
        $product = $this->find($id);
        if ($product === null) {
            return false;
        }
        return (bool) $product->delete();
    }
}
