<?php

namespace App\Livewire\Admin\Articles;

use App\Models\Article;
use Livewire\Component;
use Livewire\WithPagination;

class IndexTable extends Component
{
    use WithPagination;

    public string $search  = '';
    public int    $perPage = 10;

    protected array $allowedPerPage = [5, 10, 25, 50];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, $this->allowedPerPage, true)) {
            $this->perPage = 10;
        }
        $this->resetPage();
    }

    public function delete(string $uuid): void
    {
        $article = Article::where('uuid', $uuid)->firstOrFail();

        if ($article->image) {
            $path = storage_path('app/public/' . $article->image);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $article->delete();
        session()->flash('success', 'Article deleted successfully.');
    }

    public function render()
    {
        $articles = Article::query()
            ->when($this->search !== '', fn ($q) =>
                $q->where('name_en', 'like', '%' . $this->search . '%')
                  ->orWhere('name_ar', 'like', '%' . $this->search . '%')
                  ->orWhere('title_en', 'like', '%' . $this->search . '%')
            )
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.admin.articles.index-table', compact('articles'));
    }
}
