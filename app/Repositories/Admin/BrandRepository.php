<?php

namespace App\Repositories\Admin;

use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BrandRepository
{
    /**
     * Return all brands with their website counts (for listing).
     */
    public function all(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Brand::query()
            ->withCount('products')
            ->with('websites')
            ->when($search, fn ($query) => $query->where('name', 'like', '%' . $search . '%'))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find a brand by UUID.
     */
    public function findByUuid(string $uuid): Brand
    {
        return Brand::byUuid($uuid)->with('websites')->firstOrFail();
    }

    /**
     * Create a brand and sync websites.
     */
    public function create(array $data, array $websiteIds = []): Brand
    {
        $brand = Brand::create($data);
        $brand->websites()->sync($websiteIds);

        return $brand;
    }

    /**
     * Update a brand and sync websites.
     */
    public function update(Brand $brand, array $data, array $websiteIds = []): Brand
    {
        $brand->update($data);
        $brand->websites()->sync($websiteIds);

        return $brand;
    }

    /**
     * Delete a brand.
     */
    public function delete(Brand $brand): void
    {
        $brand->websites()->detach();
        $brand->delete();
    }
}
