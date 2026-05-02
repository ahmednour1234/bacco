<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        return view('admin.articles.index');
    }

    public function create(): View
    {
        return view('admin.articles.create');
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.edit', compact('article'));
    }

    public function destroy(Article $article): RedirectResponse
    {
        if ($article->image) {
            $path = storage_path('app/public/' . $article->image);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article deleted successfully.');
    }
}
