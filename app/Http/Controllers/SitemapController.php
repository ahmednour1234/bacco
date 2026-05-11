<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    /** Main sitemap — static pages + catalog divisions + catalog categories + news */
    public function index()
    {
        $urls = [];

        // ── Static pages (EN + AR) ─────────────────────────────────────────
        $static = [
            ['loc' => '/',               'priority' => '1.0',  'changefreq' => 'weekly'],
            ['loc' => '/about',          'priority' => '0.8',  'changefreq' => 'monthly'],
            ['loc' => '/for-brands',     'priority' => '0.8',  'changefreq' => 'monthly'],
            ['loc' => '/contact',        'priority' => '0.7',  'changefreq' => 'monthly'],
            ['loc' => '/news',           'priority' => '0.8',  'changefreq' => 'daily'],
            ['loc' => '/catalog',        'priority' => '0.9',  'changefreq' => 'weekly'],
        ];

        foreach ($static as $page) {
            $urls[] = array_merge($page, ['loc' => 'https://qimta.com' . $page['loc']]);
            $arLoc  = ($page['loc'] === '/') ? '/ar/' : '/ar' . $page['loc'];
            $urls[] = array_merge($page, ['loc' => 'https://qimta.com' . $arLoc]);
        }

        // ── Catalog divisions ──────────────────────────────────────────────
        try {
            $divisions = DB::connection('catalog')
                ->table('catalog_products')
                ->whereNotNull('division')
                ->where('division', '!=', '')
                ->selectRaw('MAX(division) as division')
                ->groupBy('division')
                ->orderBy('division')
                ->get();

            foreach ($divisions as $row) {
                $slug = Str::slug($row->division);
                $urls[] = [
                    'loc'        => 'https://qimta.com/catalog/' . $slug,
                    'priority'   => '0.7',
                    'changefreq' => 'weekly',
                ];
                $urls[] = [
                    'loc'        => 'https://qimta.com/ar/catalog/' . $slug,
                    'priority'   => '0.7',
                    'changefreq' => 'weekly',
                ];
            }
        } catch (\Exception $e) {
            // Catalog DB unavailable — skip division URLs
        }

        // ── Catalog categories ─────────────────────────────────────────────
        try {
            $categories = DB::connection('catalog')
                ->table('catalog_categories')
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->orderBy('name')
                ->get(['slug']);

            foreach ($categories as $row) {
                $urls[] = [
                    'loc'        => 'https://qimta.com/catalog/category/' . $row->slug,
                    'priority'   => '0.6',
                    'changefreq' => 'weekly',
                ];
                $urls[] = [
                    'loc'        => 'https://qimta.com/ar/catalog/category/' . $row->slug,
                    'priority'   => '0.6',
                    'changefreq' => 'weekly',
                ];
            }
        } catch (\Exception $e) {
            // Catalog DB unavailable — skip category URLs
        }

        // ── News articles ──────────────────────────────────────────────────
        try {
            $articles = Article::whereNotNull('slug')
                ->where('slug', '!=', '')
                ->orderByDesc('created_at')
                ->get(['slug', 'created_at']);

            foreach ($articles as $article) {
                $urls[] = [
                    'loc'        => 'https://qimta.com/news/' . $article->slug,
                    'lastmod'    => $article->created_at?->toAtomString(),
                    'priority'   => '0.7',
                    'changefreq' => 'monthly',
                ];
                $urls[] = [
                    'loc'        => 'https://qimta.com/ar/news/' . $article->slug,
                    'lastmod'    => $article->created_at?->toAtomString(),
                    'priority'   => '0.7',
                    'changefreq' => 'monthly',
                ];
            }
        } catch (\Exception $e) {
            // Articles table unavailable
        }

        return response()->view('sitemap.index', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    /** News sitemap — for Google News / AI Overviews */
    public function news()
    {
        $articles = collect();

        try {
            // Google News only indexes articles from the last 2 days for news sitemaps
            $articles = Article::whereNotNull('slug')
                ->where('slug', '!=', '')
                ->orderByDesc('created_at')
                ->limit(1000)
                ->get(['slug', 'title_en', 'title_ar', 'created_at']);
        } catch (\Exception $e) {
            // Articles table unavailable
        }

        return response()->view('sitemap.news', compact('articles'))
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
