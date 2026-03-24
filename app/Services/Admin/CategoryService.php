<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Repositories\Admin\CategoryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {}

    public function all(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->categoryRepository->all($search, $perPage);
    }

    public function parents(): Collection
    {
        return $this->categoryRepository->parents();
    }

    public function findByUuid(string $uuid): Category
    {
        return $this->categoryRepository->findByUuid($uuid);
    }

    public function create(array $data): Category
    {
        $websiteIds = $data['website_ids'] ?? [];
        unset($data['website_ids']);

        // Auto-generate slug from name if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->categoryRepository->create($data, $websiteIds);
    }

    public function update(Category $category, array $data): Category
    {
        $websiteIds = $data['website_ids'] ?? [];
        unset($data['website_ids']);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->categoryRepository->update($category, $data, $websiteIds);
    }

    public function delete(Category $category): void
    {
        $this->categoryRepository->delete($category);
    }
}
