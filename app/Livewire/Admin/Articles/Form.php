<?php

namespace App\Livewire\Admin\Articles;

use App\Models\Article;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public ?int $articleId = null;
    public bool $isEditing = false;

    public string $name_en  = '';
    public string $name_ar  = '';
    public string $title_en = '';
    public string $title_ar = '';
    public string $desc_en  = '';
    public string $desc_ar  = '';
    public bool   $active   = true;
    public $image = null;         // new upload (TemporaryUploadedFile or null)
    public ?string $existingImage = null; // current stored path

    public function mount(?Article $article = null): void
    {
        if ($article && $article->exists) {
            $this->articleId     = $article->id;
            $this->isEditing     = true;
            $this->name_en       = (string) ($article->name_en ?? '');
            $this->name_ar       = (string) ($article->name_ar ?? '');
            $this->title_en      = (string) ($article->title_en ?? '');
            $this->title_ar      = (string) ($article->title_ar ?? '');
            $this->desc_en       = (string) ($article->desc_en ?? '');
            $this->desc_ar       = (string) ($article->desc_ar ?? '');
            $this->active        = (bool)   $article->active;
            $this->existingImage = $article->image;
        }
    }

    protected function rules(): array
    {
        return [
            'name_en'  => ['required', 'string', 'max:255'],
            'name_ar'  => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'title_ar' => ['required', 'string', 'max:255'],
            'desc_en'  => ['nullable', 'string'],
            'desc_ar'  => ['nullable', 'string'],
            'active'   => ['boolean'],
            'image'    => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function save()
    {
        $data = $this->validate();

        // Handle image upload
        $imagePath = $this->existingImage;
        if ($this->image) {
            // Delete old image if replacing
            if ($imagePath) {
                $old = storage_path('app/public/' . $imagePath);
                if (file_exists($old)) {
                    unlink($old);
                }
            }
            $imagePath = $this->image->store('articles', 'public');
        }

        $payload = [
            'name_en'  => $data['name_en'],
            'name_ar'  => $data['name_ar'],
            'title_en' => $data['title_en'],
            'title_ar' => $data['title_ar'],
            'desc_en'  => $this->stripDataUris($data['desc_en'] ?? null),
            'desc_ar'  => $this->stripDataUris($data['desc_ar'] ?? null),
            'active'   => $data['active'],
            'image'    => $imagePath,
        ];

        if ($this->isEditing && $this->articleId) {
            Article::findOrFail($this->articleId)->update($payload);
            return redirect()->route('admin.articles.index')->with('success', 'Article updated successfully.');
        }

        Article::create($payload);
        return redirect()->route('admin.articles.index')->with('success', 'Article created successfully.');
    }

    // Called by the JS editor via Livewire.dispatch or $wire.set
    public function setDescEn(string $value): void
    {
        $this->desc_en = (string) $this->stripDataUris($value);
    }

    public function setDescAr(string $value): void
    {
        $this->desc_ar = (string) $this->stripDataUris($value);
    }

    /**
     * Remove inline base64 payloads from editor HTML.
     *
     * Browsers inline pasted images as data: URIs. A single screenshot can add
     * several MB to the description, which then ships on every Livewire sync
     * and trips PayloadTooLargeException. The client-side paste guard already
     * routes real images through the upload-media endpoint; this is the
     * server-side backstop for when that guard does not run.
     */
    protected function stripDataUris(?string $html): ?string
    {
        if ($html === null || $html === '' || stripos($html, 'data:') === false) {
            return $html;
        }

        // <a href="data:...">text</a> -> unwrap, keeping the visible text.
        $cleaned = preg_replace('#<a\b[^>]*?href\s*=\s*[\'"]?\s*data:[^>]*>(.*?)</a\s*>#is', '$1', $html);

        if ($cleaned === null) {
            return $html; // preg failure (e.g. backtrack limit) - keep original
        }

        // Self-contained media whose src is an inline payload -> drop entirely.
        $cleaned = preg_replace(
            '#<(img|video|audio|source|embed|iframe)\b[^>]*?(?:src|href)\s*=\s*[\'"]?\s*data:[^>]*>(?:\s*</\1\s*>)?#is',
            '',
            $cleaned
        ) ?? $cleaned;

        // Neutralise any remaining base64 payload left in an attribute.
        return preg_replace('#data:[a-z0-9.+-]+/[a-z0-9.+-]+;base64,[a-zA-Z0-9+/=\s]+#i', '', $cleaned) ?? $cleaned;
    }

    public function render(): View
    {
        return view('livewire.admin.articles.form');
    }
}
