<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use App\Models\Website;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?Category $category = null;

    public string $name = '';

    public string $slug = '';

    public ?int $parent_id = null;

    public string $description = '';

    public bool $active = true;

    public array $website_ids = [];

    public bool $isEditing = false;

    public function mount(?Category $category = null): void
    {
        if ($category) {
            $this->category = $category;
            $this->isEditing = true;
            $this->name = (string) $category->name;
            $this->slug = (string) ($category->slug ?? '');
            $this->parent_id = $category->parent_id ? (int) $category->parent_id : null;
            $this->description = (string) ($category->description ?? '');
            $this->active = (bool) $category->active;
            $this->website_ids = $category->websites()->pluck('websites.id')->map(fn ($id) => (int) $id)->toArray();
        }
    }

    public function rules(): array
    {
        $uuid = $this->category?->uuid;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('categories', 'slug')->ignore($uuid, 'uuid')],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['boolean'],
            'website_ids' => ['nullable', 'array'],
            'website_ids.*' => ['integer', 'exists:websites,id'],
        ];
    }

    public function save()
    {
        $data = $this->validate();
        $slug = trim((string) ($data['slug'] ?? ''));
        $data['slug'] = $slug !== '' ? $slug : Str::slug($data['name']);

        if ($this->isEditing && $this->category) {
            if ($this->parent_id && $this->parent_id === $this->category->id) {
                $this->addError('parent_id', 'A category cannot be its own parent.');

                return null;
            }

            $this->category->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'parent_id' => $data['parent_id'] ?? null,
                'description' => $data['description'] ?? null,
                'active' => $data['active'],
            ]);

            $this->category->websites()->sync($data['website_ids'] ?? []);

            return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
        }

        $category = Category::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'parent_id' => $data['parent_id'] ?? null,
            'description' => $data['description'] ?? null,
            'active' => $data['active'],
        ]);

        $category->websites()->sync($data['website_ids'] ?? []);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function render()
    {
        $parents = Category::query()
            ->when($this->category, fn ($query) => $query->where('id', '!=', $this->category->id))
            ->orderBy('name')
            ->get();

        $websites = Website::query()->where('active', true)->orderBy('name')->get();

        return view('livewire.admin.categories.form', compact('parents', 'websites'));
    }
}
