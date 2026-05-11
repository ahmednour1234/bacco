<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemaps = [
            ['loc' => 'https://qimta.com/sitemap-en.xml',   'lastmod' => now()->toAtomString()],
            ['loc' => 'https://qimta.com/sitemap-ar.xml',   'lastmod' => now()->toAtomString()],
            ['loc' => 'https://qimta.com/sitemap-news.xml', 'lastmod' => now()->toAtomString()],
        ];
        return response()->view('sitemap.index', compact('sitemaps'))
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function en()
    {
        $urls = $this->buildUrls('en');
        return response()->view('sitemap.lang', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function ar()
    {
        $urls = $this->buildUrls('ar');
        return response()->view('sitemap.lang', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function news()
    {
        $articles = collect();
        try {
            $articles = Article::whereNotNull('slug')->where('slug', '!=', '')
                ->orderByDesc('created_at')->limit(1000)
                ->get(['slug', 'title_en', 'title_ar', 'created_at']);
        } catch (\Exception $e) {}
        return response()->view('sitemap.news', compact('articles'))
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    private function buildUrls(string $lang): array
    {
        $isAr   = ($lang === 'ar');
        $prefix = $isAr ? '/ar' : '';
        $urls   = [];
        $static = [
            ['path' => '',            'priority' => '1.0', 'changefreq' => 'weekly'],
            ['path' => '/about',      'priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => '/for-brands', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => '/contact',    'priority' => '0.7', 'changefreq' => 'monthly'],
            ['path' => '/news',       'priority' => '0.8', 'changefreq' => 'daily'],
            ['path' => '/catalog',    'priority' => '0.9', 'changefreq' => 'weekly'],
        ];
        foreach ($static as $page) {
            $loc = 'https://qimta.com' . $prefix . ($page['path'] ?: '/');
            $urls[] = ['loc' => $loc, 'priority' => $page['priority'], 'changefreq' => $page['changefreq']];
        }
        try {
            $divisions = DB::connection('catalog')->table('catalog_products')
                ->whereNotNull('division')->where('division', '!=', '')
                ->selectRaw('MAX(division) as division')
                ->groupBy('division')->orderBy('division')->get();
            foreach ($divisions as $row) {
                $urls[] = ['loc' => 'https://qimta.com' . $prefix . '/catalog/' . Str::slug($row->division), 'priority' => '0.7', 'changefreq' => 'weekly'];
            }
        } catch (\Exception $e) {}
        try {
            $categories = DB::connection('catalog')->table('catalog_categories')
                ->whereNotNull('slug')->where('slug', '!=', '')->orderBy('name')->get(['slug']);
            foreach ($categories as $row) {
                $urls[] = ['loc' => 'https://qimta.com' . $prefix . '/catalog/category/' . $row->slug, 'priority' => '0.6', 'changefreq' => 'weekly'];
            }
        } catch (\Exception $e) {}
        try {
            $articles = Article::whereNotNull('slug')->where('slug', '!=', '')
                ->orderByDesc('created_at')->get(['slug', 'created_at']);
            foreach ($articles as $article) {
                $urls[] = ['loc' => 'https://qimta.com' . $prefix . '/news/' . $article->slug, 'lastmod' => $article->created_at?->toAtomString(), 'priority' => '0.7', 'changefreq' => 'monthly'];
            }
        } catch (\Exception $e) {}
        return $urls;
    }
}
