<?php

namespace App\Services\Admin;

use App\Models\Product;
use App\Repositories\Admin\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {}

    public function all(?string $search = null, int $perPage = 50): LengthAwarePaginator
    {
        return $this->productRepository->all($search, $perPage);
    }

    public function findByUuid(string $uuid): Product
    {
        return $this->productRepository->findByUuid($uuid);
    }

    public function create(array $data): Product
    {
        return $this->productRepository->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        return $this->productRepository->update($product, $data);
    }

    public function delete(Product $product): void
    {
        $this->productRepository->delete($product);
    }
}
