<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
@foreach($articles as $article)
    <url>
        <loc>https://qimta.com/news/{{ $article->slug }}</loc>
        <news:news>
            <news:publication>
                <news:name>Qimta</news:name>
                <news:language>en</news:language>
            </news:publication>
            <news:publication_date>{{ $article->created_at?->toAtomString() }}</news:publication_date>
            <news:title>{{ htmlspecialchars($article->title_en, ENT_XML1) }}</news:title>
        </news:news>
    </url>
@endforeach
</urlset>
