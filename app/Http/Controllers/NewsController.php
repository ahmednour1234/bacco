<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');
        $search   = $request->query('search', '');
        $isAr     = app()->getLocale() === 'ar';

        $query = Article::where('active', true)
            ->when($category, fn ($q) => $q->where('name_en', $category))
            ->when($search !== '', function ($q) use ($search, $isAr) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('title_en', 'like', '%' . $search . '%')
                       ->orWhere('title_ar', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('created_at');

        $featured  = (clone $query)->first();
        $rest      = (clone $query)->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))->paginate(8);
        $categories = Article::where('active', true)->distinct()->orderBy('name_en')->pluck('name_en', 'name_ar');

        return view('news', compact('featured', 'rest', 'categories', 'category', 'search', 'isAr'));
    }

    public function show(string $uuid)
    {
        $isAr    = app()->getLocale() === 'ar';
        $article = Article::where('uuid', $uuid)->where('active', true)->firstOrFail();
        $related = Article::where('active', true)
            ->where('name_en', $article->name_en)
            ->where('id', '!=', $article->id)
            ->latest()
            ->take(3)
            ->get();

        return view('news-show', compact('article', 'related', 'isAr'));
    }
}
