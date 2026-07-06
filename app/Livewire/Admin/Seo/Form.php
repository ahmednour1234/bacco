<?php

namespace App\Livewire\Admin\Seo;

use App\Models\SeoMeta;
use App\Services\SeoResolver;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public SeoMeta $seo;

    public string $title_en = '';
    public string $title_ar = '';
    public string $meta_desc_en = '';
    public string $meta_desc_ar = '';
    public string $keywords_en = '';
    public string $keywords_ar = '';
    public string $og_type = 'website';
    public string $schema_en = '';
    public string $schema_ar = '';
    public bool   $active = true;

    public ?string $existingOgImage = null;
    public $og_image = null; // new upload (TemporaryUploadedFile or null)

    public function mount(SeoMeta $seo): void
    {
        $this->seo             = $seo;
        $this->title_en        = (string) ($seo->title_en ?? '');
        $this->title_ar        = (string) ($seo->title_ar ?? '');
        $this->meta_desc_en    = (string) ($seo->meta_desc_en ?? '');
        $this->meta_desc_ar    = (string) ($seo->meta_desc_ar ?? '');
        $this->keywords_en     = (string) ($seo->keywords_en ?? '');
        $this->keywords_ar     = (string) ($seo->keywords_ar ?? '');
        $this->og_type         = (string) ($seo->og_type ?: 'website');
        $this->schema_en       = (string) ($seo->schema_en ?? '');
        $this->schema_ar       = (string) ($seo->schema_ar ?? '');
        $this->active          = (bool) $seo->active;
        $this->existingOgImage = $seo->og_image;
    }

    protected function rules(): array
    {
        return [
            'title_en'     => ['nullable', 'string', 'max:255'],
            'title_ar'     => ['nullable', 'string', 'max:255'],
            'meta_desc_en' => ['nullable', 'string', 'max:500'],
            'meta_desc_ar' => ['nullable', 'string', 'max:500'],
            'keywords_en'  => ['nullable', 'string', 'max:255'],
            'keywords_ar'  => ['nullable', 'string', 'max:255'],
            'og_type'      => ['nullable', 'string', 'max:50'],
            'schema_en'    => ['nullable', 'string'],
            'schema_ar'    => ['nullable', 'string'],
            'active'       => ['boolean'],
            'og_image'     => ['nullable', 'image', 'max:4096'],
        ];
    }

    /**
     * Reject malformed JSON-LD before saving so the public page never emits
     * broken structured data.
     */
    protected function validateSchema(): void
    {
        foreach (['schema_en' => $this->schema_en, 'schema_ar' => $this->schema_ar] as $field => $value) {
            if (trim($value) !== '' && json_decode($value) === null) {
                $this->addError($field, __('app.seo_invalid_json'));
            }
        }
    }

    public function save()
    {
        $data = $this->validate();
        $this->validateSchema();
        if ($this->getErrorBag()->isNotEmpty()) {
            return null;
        }

        // Handle OG image upload
        $ogImagePath = $this->existingOgImage;
        if ($this->og_image) {
            if ($ogImagePath) {
                $old = storage_path('app/public/' . $ogImagePath);
                if (file_exists($old)) {
                    unlink($old);
                }
            }
            $ogImagePath = $this->og_image->store('seo', 'public');
        }

        $this->seo->update([
            'title_en'     => $data['title_en'] ?? null,
            'title_ar'     => $data['title_ar'] ?? null,
            'meta_desc_en' => $data['meta_desc_en'] ?? null,
            'meta_desc_ar' => $data['meta_desc_ar'] ?? null,
            'keywords_en'  => $data['keywords_en'] ?? null,
            'keywords_ar'  => $data['keywords_ar'] ?? null,
            'og_image'     => $ogImagePath,
            'og_type'      => $data['og_type'] ?: 'website',
            'schema_en'    => $data['schema_en'] ?? null,
            'schema_ar'    => $data['schema_ar'] ?? null,
            'active'       => $data['active'],
        ]);

        // Invalidate the cached SEO record so the change shows immediately.
        SeoResolver::forget($this->seo->route_name);

        return redirect()->route('admin.seo.index')
            ->with('success', __('app.seo_saved'));
    }

    public function render()
    {
        return view('livewire.admin.seo.form');
    }
}
