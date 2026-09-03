<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($allPages as $path => $lastmod)
    <url>
        <loc>{{ $baseUrl }}{{ $path }}</loc>
        <lastmod>{{ optional($lastmod)->toW3cDateString() ?? now()->toW3cDateString() }}</lastmod>
        <changefreq>{{ str_starts_with($path, '/i/') ? 'weekly' : 'monthly' }}</changefreq>
        <priority>{{ $path === '/' ? '1.0' : (str_starts_with($path, '/templates/') ? '0.8' : '0.6') }}</priority>
    </url>
@endforeach
</urlset>
