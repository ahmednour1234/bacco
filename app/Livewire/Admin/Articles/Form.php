<?php

namespace App\Livewire\Admin\Articles;

use App\Models\Article;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public ?Article $article = null;
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
            $this->article       = $article;
            $this->isEditing     = true;
            $this->name_en       = $article->name_en;
            $this->name_ar       = $article->name_ar;
            $this->title_en      = $article->title_en;
            $this->title_ar      = $article->title_ar;
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
            'desc_en'  => $data['desc_en'] ?? null,
            'desc_ar'  => $data['desc_ar'] ?? null,
            'active'   => $data['active'],
            'image'    => $imagePath,
        ];

        if ($this->isEditing && $this->article) {
            $this->article->update($payload);
            return redirect()->route('admin.articles.index')->with('success', 'Article updated successfully.');
        }

        Article::create($payload);
        return redirect()->route('admin.articles.index')->with('success', 'Article created successfully.');
    }

    // Called by the JS editor via Livewire.dispatch or $wire.set
    public function setDescEn(string $value): void
    {
        $this->desc_en = $value;
    }

    public function setDescAr(string $value): void
    {
        $this->desc_ar = $value;
    }

    public function render()
    {
        return view('livewire.admin.articles.form');
    }
}
