<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Project;
use App\Services\SeoService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function __construct(private readonly SeoService $seo) {}

    public function sitemap(): Response
    {
        $xml = Cache::remember('seo.sitemap.xml', now()->addMinutes(30), function () {
            return $this->buildSitemap();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function robots(): Response
    {
        $base = $this->seo->baseUrl();
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /api/',
            '',
            'User-agent: GPTBot',
            'Allow: /',
            '',
            'User-agent: Google-Extended',
            'Allow: /',
            '',
            'Host: ' . $base,
            'Sitemap: ' . $base . '/sitemap.xml',
            '',
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    private function buildSitemap(): string
    {
        $base = $this->seo->baseUrl();
        $now  = now()->toAtomString();

        $urls = [];

        $urls[] = $this->url($base . '/',         $now, 'weekly', '1.0');
        $urls[] = $this->url($base . '/projects', $now, 'weekly', '0.9');
        $urls[] = $this->url($base . '/blog',     $now, 'weekly', '0.8');
        $urls[] = $this->url($base . '/contact',  $now, 'yearly', '0.5');

        Project::query()->published()->orderBy('sort_order')->get()
            ->each(function (Project $p) use (&$urls, $base) {
                $urls[] = $this->url(
                    $base . '/projects/' . $p->slug,
                    optional($p->updated_at)->toAtomString() ?? now()->toAtomString(),
                    'monthly',
                    '0.8'
                );
            });

        BlogPost::query()->published()->orderByDesc('published_at')->get()
            ->each(function (BlogPost $post) use (&$urls, $base) {
                $urls[] = $this->url(
                    $base . '/blog/' . $post->slug,
                    optional($post->updated_at)->toAtomString()
                        ?? optional($post->published_at)->toAtomString()
                        ?? now()->toAtomString(),
                    'monthly',
                    '0.7'
                );
            });

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= implode("\n", $urls);
        $xml .= "\n</urlset>\n";

        return $xml;
    }

    private function url(string $loc, string $lastmod, string $changefreq, string $priority): string
    {
        return '  <url>' . "\n"
            . '    <loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>' . "\n"
            . '    <lastmod>' . $lastmod . '</lastmod>' . "\n"
            . '    <changefreq>' . $changefreq . '</changefreq>' . "\n"
            . '    <priority>' . $priority . '</priority>' . "\n"
            . '  </url>';
    }
}
