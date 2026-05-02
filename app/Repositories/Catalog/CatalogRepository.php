<?php

namespace App\Repositories\Catalog;

use App\Models\Catalog\Catalog;
use Illuminate\Support\Str;

class CatalogRepository
{
    public function all()
    {
        return Catalog::where('status', 'active')->orderBy('name')->get();
    }

    public function find(int $id): Catalog
    {
        return Catalog::findOrFail($id);
    }

    public function firstOrCreate(string $name): Catalog
    {
        $slug = Str::slug($name);
        return Catalog::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'slug' => $slug, 'status' => 'active']
        );
    }

    public function create(array $data): Catalog
    {
        $data['slug'] ??= Str::slug($data['name']);
        return Catalog::create($data);
    }

    public function paginate(int $perPage = 20)
    {
        return Catalog::orderBy('name')->paginate($perPage);
    }
}
