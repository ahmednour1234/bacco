<?php

namespace App\Livewire\Admin\Seo;

use App\Models\SeoMeta;
use App\Services\Seo\SeoCopyGenerator;
use App\Services\SeoResolver;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
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

    // ── AI assist ─────────────────────────────────────────────
    /** Optional steer: terms the business wants this page to rank for. */
    public string $aiFocus = '';

    /** Set after a generation so the admin knows the fields are unsaved. */
    public bool $aiGenerated = false;

    /** Inline feedback — the admin layout has no toast listener. */
    public string $aiError = '';

    /** Whether the key is present; the view hides the button without it. */
    #[Locked]
    public bool $aiAvailable = false;

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
        $this->aiAvailable     = SeoCopyGenerator::isConfigured();
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

    /**
     * Ask Claude for copy and drop it into the form fields.
     *
     * Nothing is persisted here — the admin reviews, edits, and presses Save.
     * Search rankings are slow to recover from bad copy, so a human stays in
     * the loop by design rather than by omission.
     */
    public function generateWithAi(): void
    {
        $this->aiError = '';

        if (! $this->aiAvailable) {
            $this->aiError = __('app.seo_ai_unavailable');

            return;
        }

        try {
            $generated = SeoCopyGenerator::make()->generate($this->seo, [
                'page_url'     => $this->publicUrl(),
                'page_content' => $this->pageText(),
                'focus'        => trim($this->aiFocus),
            ]);
        } catch (\Throwable $e) {
            Log::warning('SEO AI generation failed.', [
                'route_name' => $this->seo->route_name,
                'message'    => $e->getMessage(),
            ]);

            $this->aiError = $e->getMessage();

            return;
        }

        // Only overwrite what came back, so a partial response cannot blank a
        // field the admin had already written.
        foreach ($generated as $field => $value) {
            if ($value !== '' && property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }

        $this->aiGenerated = true;
    }

    /** Public URL of the page this record drives, for the model's context. */
    private function publicUrl(): ?string
    {
        try {
            return route($this->seo->route_name);
        } catch (\Throwable) {
            // Routes needing parameters (catalog.item, news.show) have no single
            // URL; the route name alone is enough context for those.
            return null;
        }
    }

    /** Path portion of the page URL, for the internal sub-request. */
    private function publicPath(): ?string
    {
        $url = $this->publicUrl();

        if (! $url) {
            return null;
        }

        return parse_url($url, PHP_URL_PATH) ?: '/';
    }

    /**
     * Render the public page internally and strip it to visible text.
     *
     * A sub-request rather than an outbound fetch: many hosts cannot resolve
     * their own public hostname, and this avoids depending on that.
     */
    private function pageText(): ?string
    {
        $path = $this->publicPath();

        if (! $path) {
            return null;
        }

        try {
            $response = app()->handle(
                \Illuminate\Http\Request::create($path, 'GET')
            );

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $html = (string) $response->getContent();
        } catch (\Throwable) {
            return null;
        }

        // Drop the parts that carry no copy before stripping tags, otherwise
        // the model reads minified CSS as page content.
        $html = preg_replace('#<(script|style|noscript|svg)[^>]*>.*?</>#is', ' ', $html) ?? $html;
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        return $text !== '' ? mb_substr($text, 0, 6000) : null;
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
