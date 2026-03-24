<?php

namespace App\Livewire\Admin\Brands;

use App\Models\Brand;
use App\Models\Website;
use Livewire\Component;

class Form extends Component
{
    public ?Brand $brand = null;

    public string $name = '';

    public string $description = '';

    public bool $active = true;

    public array $website_ids = [];

    public bool $isEditing = false;

    public function mount(?Brand $brand = null): void
    {
        if ($brand) {
            $this->brand = $brand;
            $this->isEditing = true;
            $this->name = (string) $brand->name;
            $this->description = (string) ($brand->description ?? '');
            $this->active = (bool) $brand->active;
            $this->website_ids = $brand->websites()->pluck('websites.id')->map(fn ($id) => (int) $id)->toArray();
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['boolean'],
            'website_ids' => ['nullable', 'array'],
            'website_ids.*' => ['integer', 'exists:websites,id'],
        ];
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->isEditing && $this->brand) {
            $this->brand->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'active' => $data['active'],
            ]);

            $this->brand->websites()->sync($data['website_ids'] ?? []);

            return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully.');
        }

        $brand = Brand::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
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
