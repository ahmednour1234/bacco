<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function uploadMedia(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,mp4,webm,ogg', 'max:20480'],
        ]);

        $path = $request->file('file')->store('article-media', 'public');

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }
}
