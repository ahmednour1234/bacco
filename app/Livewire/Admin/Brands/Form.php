<?php

namespace App\Livewire\Admin\Brands;

use App\Models\Brand;
use App\Models\Website;
use Livewire\Component;

class Form extends Component
{
    public ?Brand $brand = null;

    public string $name_en = '';

    public string $name_ar = '';

    public string $description_en = '';

    public string $description_ar = '';

    public bool $active = true;

    public array $website_ids = [];

    public bool $isEditing = false;

    public function mount(?Brand $brand = null): void
    {
        if ($brand && $brand->exists) {
            $this->brand = $brand;
            $this->isEditing = true;
            $this->name_en = (string) ($brand->name_en ?: $brand->name);
            $this->name_ar = (string) ($brand->name_ar ?? '');
            $this->description_en = (string) ($brand->description_en ?: ($brand->description ?? ''));
            $this->description_ar = (string) ($brand->description_ar ?? '');
            $this->active = (bool) $brand->active;
            $this->website_ids = $brand->websites()->pluck('websites.id')->map(fn ($id) => (int) $id)->toArray();
        }
    }

    public function rules(): array
    {
        return [
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
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

        $legacyName = $data['name_en'] ?: $data['name_ar'];
        $legacyDescription = trim((string) ($data['description_en'] ?? '')) !== ''
            ? $data['description_en']
            : ($data['description_ar'] ?? null);

        if ($this->isEditing && $this->brand) {
            $this->brand->update([
                'name' => $legacyName,
                'name_en' => $data['name_en'],
                'name_ar' => $data['name_ar'],
                'description' => $legacyDescription,
                'description_en' => $data['description_en'] ?? null,
                'description_ar' => $data['description_ar'] ?? null,
                'active' => $data['active'],
            ]);

            $this->brand->websites()->sync($data['website_ids'] ?? []);

            return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully.');
        }

        $brand = Brand::create([
            'name' => $legacyName,
            'name_en' => $data['name_en'],
            'name_ar' => $data['name_ar'],
            'description' => $legacyDescription,
            'description_en' => $data['description_en'] ?? null,
            'description_ar' => $data['description_ar'] ?? null,
            'active' => $data['active'],
        ]);

        $brand->websites()->sync($data['website_ids'] ?? []);

        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully.');
    }

    public function render()
    {
        $websites = Website::query()->where('active', true)->orderBy('name')->get();

        return view('livewire.admin.brands.form', compact('websites'));
    }
}
