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

    public string $name_en = '';

    public string $name_ar = '';

    public string $slug = '';

    public ?int $parent_id = null;

    public string $description_en = '';

    public string $description_ar = '';

    public bool $active = true;

    public array $website_ids = [];

    public bool $isEditing = false;

    public function mount(?Category $category = null): void
    {
        if ($category && $category->exists) {
            $this->category = $category;
            $this->isEditing = true;
            $this->name_en = (string) ($category->name_en ?: $category->name);
            $this->name_ar = (string) ($category->name_ar ?? '');
            $this->slug = (string) ($category->slug ?? '');
            $this->parent_id = $category->parent_id ? (int) $category->parent_id : null;
            $this->description_en = (string) ($category->description_en ?: ($category->description ?? ''));
            $this->description_ar = (string) ($category->description_ar ?? '');
            $this->active = (bool) $category->active;
            $this->website_ids = $category->websites()->pluck('websites.id')->map(fn ($id) => (int) $id)->toArray();
        }
    }

    public function rules(): array
    {
        $uuid = $this->category?->uuid;

        return [
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('categories', 'slug')->ignore($uuid, 'uuid')],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description_en' => ['nullable', 'string', 'max:1000'],
            'description_ar' => ['nullable', 'string', 'max:1000'],
            'active' => ['boolean'],
            'website_ids' => ['nullable', 'array'],
            'website_ids.*' => ['integer', 'exists:websites,id'],
        ];
    }

    public function save()
    {
        $data = $this->validate();
        $slug = trim((string) ($data['slug'] ?? ''));
        $data['slug'] = $slug !== '' ? $slug : Str::slug($data['name_en']);
        $legacyName = $data['name_en'] ?: $data['name_ar'];
        $legacyDescription = trim((string) ($data['description_en'] ?? '')) !== ''
            ? $data['description_en']
            : ($data['description_ar'] ?? null);

        if ($this->isEditing && $this->category) {
            if ($this->parent_id && $this->parent_id === $this->category->id) {
                $this->addError('parent_id', 'A category cannot be its own parent.');

                return null;
            }

            $this->category->update([
                'name' => $legacyName,
                'name_en' => $data['name_en'],
                'name_ar' => $data['name_ar'],
                'slug' => $data['slug'],
                'parent_id' => $data['parent_id'] ?? null,
                'description' => $legacyDescription,
                'description_en' => $data['description_en'] ?? null,
                'description_ar' => $data['description_ar'] ?? null,
                'active' => $data['active'],
            ]);

            $this->category->websites()->sync($data['website_ids'] ?? []);

            return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
        }

        $category = Category::create([
            'name' => $legacyName,
            'name_en' => $data['name_en'],
            'name_ar' => $data['name_ar'],
            'slug' => $data['slug'],
            'parent_id' => $data['parent_id'] ?? null,
            'description' => $legacyDescription,
            'description_en' => $data['description_en'] ?? null,
            'description_ar' => $data['description_ar'] ?? null,
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
