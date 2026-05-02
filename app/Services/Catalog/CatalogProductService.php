<?php

namespace App\Services\Catalog;

use App\Repositories\Catalog\CatalogProductRepository;
use App\Repositories\Catalog\CatalogRepository;
use App\Repositories\Catalog\CatalogCategoryRepository;

class CatalogProductService
{
    public function __construct(
        private CatalogProductRepository  $productRepo,
        private CatalogRepository         $catalogRepo,
        private CatalogCategoryRepository $categoryRepo,
    ) {}

    public function filter(array $filters, int $perPage = 30)
    {
        return $this->productRepo->filter($filters, $perPage);
    }

    public function allCatalogs()
    {
        return $this->catalogRepo->all();
    }

    public function categoriesForCatalog(int $catalogId)
    {
        return $this->categoryRepo->forCatalog($catalogId);
    }

    public function distinctValues(string $column, ?int $catalogId = null): array
    {
        return $this->productRepo->distinctValues($column, $catalogId);
    }
}
