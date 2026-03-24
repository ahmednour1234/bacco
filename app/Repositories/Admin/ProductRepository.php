<?php

namespace App\Repositories\Admin;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ProductRepository
{
    public function all(?string $search = null, int $perPage = 50): LengthAwarePaginator
    {
        return Product::query()
            ->with(['brand', 'category', 'unit'])
            ->when($search, fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'ilike', '%' . $search . '%')
                      ->orWhere('sku', 'ilike', '%' . $search . '%')
                      ->orWhere('division', 'ilike', '%' . $search . '%');
            }))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findByUuid(string $uuid): Product
    {
        return Product::byUuid($uuid)->with(['brand', 'category', 'unit'])->firstOrFail();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh(['brand', 'category', 'unit']);
    }

    public function delete(Product $product): void
    {
        if ($product->datasheet_path) {
            Storage::disk('public')->delete($product->datasheet_path);
        }

        $product->delete();
    }
}
