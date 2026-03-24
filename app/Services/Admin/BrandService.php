<?php

namespace App\Services\Admin;

use App\Models\Brand;
use App\Repositories\Admin\BrandRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BrandService
{
    public function __construct(
        private readonly BrandRepository $brandRepository
    ) {}

    public function all(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->brandRepository->all($search, $perPage);
    }

    public function findByUuid(string $uuid): Brand
    {
        return $this->brandRepository->findByUuid($uuid);
    }

    public function create(array $data): Brand
    {
        $websiteIds = $data['website_ids'] ?? [];
        unset($data['website_ids']);

        return $this->brandRepository->create($data, $websiteIds);
    }

    public function update(Brand $brand, array $data): Brand
    {
        $websiteIds = $data['website_ids'] ?? [];
        unset($data['website_ids']);

        return $this->brandRepository->update($brand, $data, $websiteIds);
    }

    public function delete(Brand $brand): void
    {
        $this->brandRepository->delete($brand);
    }
}
