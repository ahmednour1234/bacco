<?php

namespace App\Repositories\Admin;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    /**
     * Return all parent categories with children count (for listing).
     */
    public function all(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Category::withCount('products')
            ->with(['parent', 'websites'])
            ->when($search, fn ($query) => $query->where('name', 'like', '%' . $search . '%'))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Return only parent (top-level) categories for parent select.
     */
    public function parents(): Collection
    {
        return Category::whereNull('parent_id')->orderBy('name')->get();
    }

    /**
     * Find a category by UUID.
     */
    public function findByUuid(string $uuid): Category
    {
        return Category::byUuid($uuid)->with('websites')->firstOrFail();
    }

    /**
     * Create a category and sync websites.
     */
    public function create(array $data, array $websiteIds = []): Category
    {
        $category = Category::create($data);
        $category->websites()->sync($websiteIds);

        return $category;
    }

    /**
     * Update a category and sync websites.
     */
    public function update(Category $category, array $data, array $websiteIds = []): Category
    {
        $category->update($data);
        $category->websites()->sync($websiteIds);

        return $category;
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category): void
    {
        $category->websites()->detach();
        $category->delete();
    }
}
