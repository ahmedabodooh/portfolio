<?php

namespace App\Http\Controllers\Shell;

use App\Http\Controllers\Controller;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves the static HTML frontend shells (web + admin) without Blade.
 * Each instance is bound to one frontend root (e.g. frontend/web or frontend/admin).
 *
 * For the public web shell we inject server-side SEO (meta + OG + JSON-LD) into
 * the <head> of every HTML response so crawlers see fully-rendered tags.
 */
class StaticShellController extends Controller
{
    public function __construct(
        private readonly string $root,
        private readonly bool $injectSeo = false,
    ) {}

    public static function web(): self
    {
        return new self(base_path('frontend/web'), injectSeo: true);
    }

    public static function admin(): self
    {
        return new self(base_path('frontend/admin'), injectSeo: false);
    }

    /**
     * Pretty URL → template map for detail pages.
     * /projects/{slug} → pages/project.html, /blog/{slug} → pages/post.html
     */
    private const PRETTY_DETAIL_ROUTES = [
        'projects' => 'pages/project.html',
        'blog'     => 'pages/post.html',
    ];

    public function __invoke(Request $request, string $path = '')
    {
        $path = trim($path, '/');

        if ($path === '') {
            return $this->file($request, 'index.html', '/');
        }

        $full = $this->root . '/' . $path;

        if ($this->isFile($full)) {
            return $this->file($request, $path, '/' . $path);
        }

        if (is_dir($full) && $this->isFile($full . '/index.html')) {
            return $this->file($request, $path . '/index.html', '/' . $path);
        }

        if (! str_contains($path, '.')) {
            $pageFile = 'pages/' . $path . '.html';
            if ($this->isFile($this->root . '/' . $pageFile)) {
                return $this->file($request, $pageFile, '/' . $path);
            }

            // Pretty detail URLs: /projects/slug-here → pages/project.html
            $segments = explode('/', $path);
            if (count($segments) === 2 && isset(self::PRETTY_DETAIL_ROUTES[$segments[0]])) {
                $template = self::PRETTY_DETAIL_ROUTES[$segments[0]];
                if ($this->isFile($this->root . '/' . $template)) {
                    return $this->file($request, $template, '/' . $path, slug: $segments[1]);
                }
            }
        }

        return $this->file($request, 'index.html', '/' . $path);
    }

    private function isFile(string $path): bool
    {
        $real = realpath($path);
        $rootReal = realpath($this->root);
        return $real && $rootReal && str_starts_with($real, $rootReal) && is_file($real);
    }

    private function file(Request $request, string $relative, string $logicalPath = '/', ?string $slug = null)
    {
        $full = $this->root . '/' . $relative;
        $ext  = strtolower(pathinfo($full, PATHINFO_EXTENSION));

        $mimes = [
            'html' => 'text/html; charset=UTF-8',
            'css'  => 'text/css; charset=UTF-8',
            'js'   => 'application/javascript; charset=UTF-8',
            'mjs'  => 'application/javascript; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'svg'  => 'image/svg+xml',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            'ico'  => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2'=> 'font/woff2',
            'ttf'  => 'font/ttf',
            'otf'  => 'font/otf',
        ];

        // HTML pages on the public web shell get server-side SEO injected.
        if ($ext === 'html' && $this->injectSeo) {
            return $this->htmlWithSeo($full, $logicalPath, $slug);
        }

        $response = Response::file($full, [
            'Content-Type' => $mimes[$ext] ?? 'application/octet-stream',
        ]);

        $this->applyCacheHeaders($response, $ext);

        return $response;
    }

    private function htmlWithSeo(string $full, string $logicalPath, ?string $slug = null)
    {
        $html = (string) file_get_contents($full);
        $seoPath = $this->resolveSeoPath($full, $logicalPath, $slug);

        try {
            /** @var SeoService $seo */
            $seo = app(SeoService::class);
            $payload = $seo->forPath($seoPath);
            $head    = $seo->renderHead($payload);
            $html    = $this->injectHead($html, $head);
        } catch (\Throwable $e) {
            // Never block the page for SEO failures — just log and serve as-is.
            report($e);
        }

        $response = Response::make($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
        $this->applyCacheHeaders($response, 'html');
        return $response;
    }

    /**
     * Map a served HTML file to the canonical SEO path.
     * E.g. /pages/blog.html served for request /blog → SEO path /blog.
     * Detail pages (project.html, post.html) receive the slug from query string ?slug=...
     * so SPA deep links like /projects/my-thing resolve correctly.
     */
    private function resolveSeoPath(string $full, string $logicalPath, ?string $prettySlug = null): string
    {
        $file = strtolower(basename($full));
        $slug = $prettySlug ?? request()->query('slug');

        if ($file === 'project.html' && $slug) {
            return '/projects/' . trim((string) $slug, '/');
        }
        if ($file === 'post.html' && $slug) {
            return '/blog/' . trim((string) $slug, '/');
        }

        // Pretty URL takes precedence (/projects, /blog, etc).
        return $logicalPath === '' ? '/' : $logicalPath;
    }

    /**
     * Inject a block of head tags right before </head>, stripping any tags
     * from the template that we now own (title/description/keywords/canonical/og/twitter/jsonld).
     */
    private function injectHead(string $html, string $headBlock): string
    {
        $patterns = [
            '/<title>.*?<\/title>/is',
            '/<meta\s+name=(["\'])description\1[^>]*>\s*/i',
            '/<meta\s+name=(["\'])keywords\1[^>]*>\s*/i',
            '/<meta\s+name=(["\'])author\1[^>]*>\s*/i',
            '/<meta\s+name=(["\'])robots\1[^>]*>\s*/i',
            '/<meta\s+name=(["\'])googlebot\1[^>]*>\s*/i',
            '/<meta\s+name=(["\'])twitter:[^"\']+\1[^>]*>\s*/i',
            '/<meta\s+property=(["\'])og:[^"\']+\1[^>]*>\s*/i',
            '/<link\s+rel=(["\'])canonical\1[^>]*>\s*/i',
            '/<script\s+type=(["\'])application\/ld\+json\1[^>]*>.*?<\/script>\s*/is',
        ];
        foreach ($patterns as $p) {
            $html = (string) preg_replace($p, '', $html);
        }

        if (stripos($html, '</head>') !== false) {
            return (string) preg_replace('/<\/head>/i', $headBlock . '</head>', $html, 1);
        }
        // Fallback: prepend at the top.
        return $headBlock . $html;
    }

    private function applyCacheHeaders($response, string $ext): void
    {
        if (app()->environment('production')) {
            if ($ext === 'html') {
                $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
            } elseif (in_array($ext, ['css', 'js', 'mjs'], true)) {
                $response->headers->set('Cache-Control', 'public, max-age=3600');
            } elseif (in_array($ext, ['png','jpg','jpeg','webp','svg','avif','woff2','woff','ttf','ico'], true)) {
                $response->headers->set('Cache-Control', 'public, max-age=2592000, immutable');
            }
        } else {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
        }
    }
}
