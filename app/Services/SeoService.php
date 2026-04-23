<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Project;
use Illuminate\Support\Str;

class SeoService
{
    /**
     * The owner's name in every form a search engine might see it.
     * These are the queries that MUST land on the site.
     */
    public const OWNER_QUERIES = [
        'Ahmed Abo Dooh',
        'Ahmed Abodooh',
        'Ahmed Abu Dooh',
        'Ahmed AboDooh',
        'ahmedabodooh',
        'أحمد أبو دعدوع',
        'احمد ابو دعدوع',
        'احمد ابو دوح',
    ];

    /**
     * Core keywords for organic discovery. Ordered by weight.
     * Anyone searching for these in the Egypt / MENA region should find this site.
     */
    public const CORE_KEYWORDS = [
        'Laravel developer Egypt',
        'Laravel developer Cairo',
        'Laravel developer New Cairo',
        'PHP developer Egypt',
        'PHP developer Cairo',
        'Full-stack developer Egypt',
        'Backend developer Egypt',
        'Freelance Laravel developer',
        'Laravel expert',
        'Laravel API developer',
        'Laravel Filament developer',
        'Laravel Livewire developer',
        'MySQL developer Egypt',
        'Redis Laravel',
        'REST API developer',
        'Sanctum authentication Laravel',
        'Elasticsearch PHP',
        'Docker PHP developer',
        'SaaS Laravel developer',
        'ERP Laravel developer',
        'Ahmed Abo Dooh Laravel',
        'Ahmed Abo Dooh portfolio',
        'مطور Laravel مصر',
        'مطور PHP مصر',
        'مطور لارافيل القاهرة',
        'مطور ويب القاهرة الجديدة',
        'مطور full-stack مصر',
    ];

    private array $profile;
    private array $branding;
    private string $baseUrl;
    private ?string $ogImage;

    public function __construct(
        private readonly SiteSettingService $settings,
    ) {
        $this->profile  = $this->settings->profile();
        $this->branding = $this->settings->branding();
        $this->baseUrl  = rtrim(config('app.url', 'http://localhost'), '/');
        $this->ogImage  = $this->absoluteUrl(
            $this->branding['og_image'] ?? $this->profile['owner_photo'] ?? null
        );
    }

    /**
     * Build the full SEO payload for a given request path.
     * The StaticShellController uses this to inject <head> tags before sending HTML.
     */
    public function forPath(string $path): array
    {
        $path = '/' . ltrim($path, '/');

        return match (true) {
            $path === '/' || $path === ''    => $this->home(),
            $path === '/projects'            => $this->projectsIndex(),
            str_starts_with($path, '/projects/') => $this->projectShow(trim(Str::after($path, '/projects/'), '/')),
            $path === '/blog'                => $this->blogIndex(),
            str_starts_with($path, '/blog/') => $this->blogShow(trim(Str::after($path, '/blog/'), '/')),
            $path === '/contact'             => $this->contact(),
            default                          => $this->generic($path),
        };
    }

    /* ===================================================================== */
    /* Page resolvers                                                         */
    /* ===================================================================== */

    private function home(): array
    {
        $name    = $this->profile['owner_name']     ?? 'Ahmed Abo Dooh';
        $role    = $this->profile['owner_role']     ?? 'Full-Stack Developer · Laravel & PHP';
        $tagline = $this->profile['owner_tagline']  ?? '';

        $title = "{$name} — {$role}";
        $description = $this->truncate(
            $tagline ?: "Portfolio of {$name}, a Full-Stack Laravel & PHP developer from New Cairo, Egypt. Shipping production-grade APIs, ERPs, and SaaS platforms with MySQL, Redis, and Docker.",
            160
        );

        return $this->compose(
            url:         $this->baseUrl . '/',
            title:       $title,
            description: $description,
            keywords:    array_merge(self::OWNER_QUERIES, self::CORE_KEYWORDS),
            type:        'website',
            jsonLd:      [
                $this->personSchema(),
                $this->websiteSchema(),
                $this->professionalServiceSchema(),
                $this->breadcrumbSchema([['Home', $this->baseUrl . '/']]),
            ],
        );
    }

    private function projectsIndex(): array
    {
        $name  = $this->profile['owner_name'] ?? 'Ahmed Abo Dooh';
        $title = "Projects Archive — Laravel, PHP & Full-Stack Work · {$name}";
        $description = "Every production system {$name} has shipped — Laravel APIs, full-stack platforms, ERPs, SaaS tools, and performance-tuned backends built with PHP, MySQL, Redis, and Docker.";

        $projects = Project::query()->published()->orderBy('sort_order')->get();

        return $this->compose(
            url:         $this->baseUrl . '/projects',
            title:       $title,
            description: $description,
            keywords:    array_merge(
                ['Laravel projects', 'PHP projects portfolio', 'Full-stack case studies'],
                self::CORE_KEYWORDS
            ),
            type:        'website',
            jsonLd:      [
                $this->collectionPageSchema($title, $description, $this->baseUrl . '/projects', $projects),
                $this->breadcrumbSchema([
                    ['Home',     $this->baseUrl . '/'],
                    ['Projects', $this->baseUrl . '/projects'],
                ]),
            ],
        );
    }

    private function projectShow(string $slug): array
    {
        /** @var Project|null $project */
        $project = Project::query()->where('slug', $slug)->first();

        if (! $project || ! $project->is_published) {
            return $this->generic('/projects/' . $slug, title: 'Project not found');
        }

        $name = $this->profile['owner_name'] ?? 'Ahmed Abo Dooh';
        $tech = is_array($project->tech_stack) ? implode(', ', $project->tech_stack) : '';
        $title = "{$project->title} — " . ($project->category ?: 'Case Study') . " · {$name}";
        $description = $this->truncate(
            $project->summary ?: $project->tagline ?: "Case study of {$project->title}, built by {$name} using {$tech}.",
            160
        );
        $url = $this->baseUrl . '/projects/' . $project->slug;
        $image = $this->absoluteUrl($project->cover_image) ?? $this->ogImage;

        $keywords = array_values(array_unique(array_filter(array_merge(
            [$project->title, $project->category, $project->client],
            is_array($project->tech_stack) ? $project->tech_stack : [],
            ['Laravel case study', "{$project->title} Laravel", "{$name} " . ($project->category ?: 'project')],
            self::CORE_KEYWORDS,
        ))));

        return $this->compose(
            url:         $url,
            title:       $title,
            description: $description,
            keywords:    $keywords,
            type:        'article',
            image:       $image,
            jsonLd: [
                $this->creativeWorkSchema($project, $url, $image),
                $this->breadcrumbSchema([
                    ['Home',     $this->baseUrl . '/'],
                    ['Projects', $this->baseUrl . '/projects'],
                    [$project->title, $url],
                ]),
            ],
        );
    }

    private function blogIndex(): array
    {
        $name  = $this->profile['owner_name'] ?? 'Ahmed Abo Dooh';
        $title = "Notes — Writing on Laravel, PHP & Shipping Software · {$name}";
        $description = "Field notes by {$name} on Laravel architecture, PHP performance, MySQL optimization, API design, and what it actually takes to ship real software.";

        $posts = BlogPost::query()->published()->orderByDesc('published_at')->limit(50)->get();

        return $this->compose(
            url:         $this->baseUrl . '/blog',
            title:       $title,
            description: $description,
            keywords:    array_merge(
                ['Laravel blog', 'PHP blog', 'Laravel articles', 'Laravel tutorials'],
                self::CORE_KEYWORDS
            ),
            type:        'website',
            jsonLd:      [
                $this->blogSchema($title, $description, $this->baseUrl . '/blog', $posts),
                $this->breadcrumbSchema([
                    ['Home', $this->baseUrl . '/'],
                    ['Blog', $this->baseUrl . '/blog'],
                ]),
            ],
        );
    }

    private function blogShow(string $slug): array
    {
        /** @var BlogPost|null $post */
        $post = BlogPost::query()->where('slug', $slug)->first();

        if (! $post || ! $post->is_published) {
            return $this->generic('/blog/' . $slug, title: 'Post not found');
        }

        $name = $this->profile['owner_name'] ?? 'Ahmed Abo Dooh';
        $title = "{$post->title} · {$name}";
        $description = $this->truncate(
            $post->excerpt ?: strip_tags((string) $post->body),
            160
        );
        $url = $this->baseUrl . '/blog/' . $post->slug;
        $image = $this->absoluteUrl($post->cover_image) ?? $this->ogImage;

        $keywords = array_values(array_unique(array_filter(array_merge(
            [$post->title],
            is_array($post->tags) ? $post->tags : [],
            ['Laravel article', "{$name} blog"],
            self::CORE_KEYWORDS,
        ))));

        return $this->compose(
            url:         $url,
            title:       $title,
            description: $description,
            keywords:    $keywords,
            type:        'article',
            image:       $image,
            jsonLd: [
                $this->articleSchema($post, $url, $image),
                $this->breadcrumbSchema([
                    ['Home',  $this->baseUrl . '/'],
                    ['Blog',  $this->baseUrl . '/blog'],
                    [$post->title, $url],
                ]),
            ],
        );
    }

    private function contact(): array
    {
        $name  = $this->profile['owner_name']  ?? 'Ahmed Abo Dooh';
        $email = $this->profile['owner_email'] ?? 'zalfyhima@gmail.com';

        return $this->compose(
            url:         $this->baseUrl . '/contact',
            title:       "Contact {$name} — Hire a Laravel & PHP Developer",
            description: "Reach out to {$name} for Laravel and PHP engagements — backend systems, APIs, ERPs, and full-stack builds. Reply within 24 hours at {$email}.",
            keywords:    array_merge(['Hire Laravel developer', 'Contact Laravel developer Egypt'], self::CORE_KEYWORDS),
            type:        'website',
            jsonLd:      [
                $this->contactPageSchema(),
                $this->breadcrumbSchema([
                    ['Home',    $this->baseUrl . '/'],
                    ['Contact', $this->baseUrl . '/contact'],
                ]),
            ],
        );
    }

    private function generic(string $path, ?string $title = null): array
    {
        $name = $this->profile['owner_name'] ?? 'Ahmed Abo Dooh';

        return $this->compose(
            url:         $this->baseUrl . $path,
            title:       $title ? "{$title} · {$name}" : "{$name} — Full-Stack Developer",
            description: $this->profile['owner_tagline'] ?? "Portfolio of {$name}, a Full-Stack Laravel & PHP developer based in New Cairo, Egypt.",
            keywords:    array_merge(self::OWNER_QUERIES, self::CORE_KEYWORDS),
            type:        'website',
            jsonLd:      [$this->personSchema(), $this->websiteSchema()],
        );
    }

    /* ===================================================================== */
    /* Composition                                                            */
    /* ===================================================================== */

    /**
     * Compose the final SEO head payload.
     */
    private function compose(
        string $url,
        string $title,
        string $description,
        array $keywords = [],
        string $type = 'website',
        ?string $image = null,
        array $jsonLd = [],
    ): array {
        $image = $image ?? $this->ogImage;
        $name  = $this->profile['owner_name'] ?? 'Ahmed Abo Dooh';

        return [
            'title'       => $this->escape($title),
            'description' => $this->escape($description),
            'keywords'    => $this->escape(implode(', ', array_values(array_unique(array_filter($keywords))))),
            'canonical'   => $this->escape($url),
            'og' => [
                'type'        => $type,
                'url'         => $this->escape($url),
                'title'       => $this->escape($title),
                'description' => $this->escape($description),
                'site_name'   => $this->escape($name),
                'image'       => $image ? $this->escape($image) : null,
                'locale'      => 'en_US',
                'locale_alt'  => 'ar_EG',
            ],
            'twitter' => [
                'card'        => $image ? 'summary_large_image' : 'summary',
                'title'       => $this->escape($title),
                'description' => $this->escape($description),
                'image'       => $image ? $this->escape($image) : null,
            ],
            'robots'   => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            'author'   => $this->escape($name),
            'json_ld'  => array_values(array_filter($jsonLd)),
        ];
    }

    /**
     * Render the SEO payload into a single HTML <head> fragment.
     */
    public function renderHead(array $seo): string
    {
        $lines = [];

        $lines[] = '<title>' . $seo['title'] . '</title>';
        $lines[] = '<meta name="description" content="' . $seo['description'] . '">';
        if (! empty($seo['keywords'])) {
            $lines[] = '<meta name="keywords" content="' . $seo['keywords'] . '">';
        }
        $lines[] = '<meta name="author" content="' . $seo['author'] . '">';
        $lines[] = '<meta name="robots" content="' . $seo['robots'] . '">';
        $lines[] = '<meta name="googlebot" content="' . $seo['robots'] . '">';
        $lines[] = '<link rel="canonical" href="' . $seo['canonical'] . '">';

        // Favicon — centralised here so every page gets the branded "a" mark.
        $lines[] = '<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">';
        $lines[] = '<link rel="alternate icon" href="/favicon.ico">';
        $lines[] = '<link rel="apple-touch-icon" href="/assets/images/favicon.svg">';
        $lines[] = '<meta name="msapplication-TileColor" content="#20B15A">';

        // Open Graph
        $lines[] = '<meta property="og:type" content="' . $seo['og']['type'] . '">';
        $lines[] = '<meta property="og:url" content="' . $seo['og']['url'] . '">';
        $lines[] = '<meta property="og:title" content="' . $seo['og']['title'] . '">';
        $lines[] = '<meta property="og:description" content="' . $seo['og']['description'] . '">';
        $lines[] = '<meta property="og:site_name" content="' . $seo['og']['site_name'] . '">';
        $lines[] = '<meta property="og:locale" content="' . $seo['og']['locale'] . '">';
        $lines[] = '<meta property="og:locale:alternate" content="' . $seo['og']['locale_alt'] . '">';
        if (! empty($seo['og']['image'])) {
            $lines[] = '<meta property="og:image" content="' . $seo['og']['image'] . '">';
            $lines[] = '<meta property="og:image:width" content="1200">';
            $lines[] = '<meta property="og:image:height" content="630">';
            $lines[] = '<meta property="og:image:alt" content="' . $seo['og']['title'] . '">';
        }

        // Twitter
        $lines[] = '<meta name="twitter:card" content="' . $seo['twitter']['card'] . '">';
        $lines[] = '<meta name="twitter:title" content="' . $seo['twitter']['title'] . '">';
        $lines[] = '<meta name="twitter:description" content="' . $seo['twitter']['description'] . '">';
        if (! empty($seo['twitter']['image'])) {
            $lines[] = '<meta name="twitter:image" content="' . $seo['twitter']['image'] . '">';
        }
        $lines[] = '<meta name="twitter:creator" content="@ahmedabodooh">';

        // JSON-LD
        foreach ($seo['json_ld'] as $schema) {
            $lines[] = '<script type="application/ld+json">'
                . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                . '</script>';
        }

        return "\n    " . implode("\n    ", $lines) . "\n";
    }

    /* ===================================================================== */
    /* JSON-LD schemas                                                        */
    /* ===================================================================== */

    public function personSchema(): array
    {
        $name     = $this->profile['owner_name']     ?? 'Ahmed Abo Dooh';
        $role     = $this->profile['owner_role']     ?? 'Full-Stack Developer';
        $email    = $this->profile['owner_email']    ?? null;
        $photo    = $this->absoluteUrl($this->profile['owner_photo'] ?? null);
        $location = $this->profile['owner_location'] ?? 'New Cairo, Egypt';

        $sameAs = array_values(array_filter([
            $this->profile['owner_github']   ?? null,
            $this->profile['owner_linkedin'] ?? null,
        ]));

        return array_filter([
            '@context'    => 'https://schema.org',
            '@type'       => 'Person',
            '@id'         => $this->baseUrl . '/#person',
            'name'        => $name,
            'alternateName' => self::OWNER_QUERIES,
            'jobTitle'    => $role,
            'description' => $this->profile['owner_tagline'] ?? null,
            'email'       => $email ? "mailto:{$email}" : null,
            'image'       => $photo,
            'url'         => $this->baseUrl . '/',
            'sameAs'      => $sameAs ?: null,
            'address'     => [
                '@type'           => 'PostalAddress',
                'addressLocality' => 'New Cairo',
                'addressRegion'   => 'Cairo Governorate',
                'addressCountry'  => 'EG',
            ],
            'knowsAbout' => [
                'PHP', 'Laravel', 'MySQL', 'Redis', 'REST APIs', 'Sanctum', 'JWT',
                'Laravel Livewire', 'Laravel Filament', 'Elasticsearch', 'Docker',
                'Backend architecture', 'SaaS platforms', 'ERP systems', 'Query optimization',
            ],
            'knowsLanguage' => ['en', 'ar'],
            'worksFor' => [
                '@type' => 'Organization',
                'name'  => 'Rabehni',
            ],
            'alumniOf' => [
                '@type' => 'CollegeOrUniversity',
                'name'  => 'New Cairo Academy',
            ],
        ], fn ($v) => ! is_null($v) && $v !== []);
    }

    public function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            '@id'      => $this->baseUrl . '/#website',
            'url'      => $this->baseUrl . '/',
            'name'     => $this->profile['owner_name'] ?? 'Ahmed Abo Dooh',
            'description' => $this->profile['owner_tagline'] ?? null,
            'inLanguage' => 'en',
            'publisher' => ['@id' => $this->baseUrl . '/#person'],
        ];
    }

    public function professionalServiceSchema(): array
    {
        $name  = $this->profile['owner_name']  ?? 'Ahmed Abo Dooh';
        $email = $this->profile['owner_email'] ?? null;

        return array_filter([
            '@context'    => 'https://schema.org',
            '@type'       => 'ProfessionalService',
            '@id'         => $this->baseUrl . '/#service',
            'name'        => "{$name} — Laravel & PHP Development",
            'provider'    => ['@id' => $this->baseUrl . '/#person'],
            'url'         => $this->baseUrl . '/',
            'email'       => $email,
            'areaServed'  => ['EG', 'AE', 'SA', 'Worldwide (Remote)'],
            'serviceType' => [
                'Laravel development', 'PHP development', 'REST API development',
                'Backend engineering', 'Full-stack web development', 'ERP development',
                'SaaS development', 'Database design & optimization',
            ],
            'address' => [
                '@type'           => 'PostalAddress',
                'addressLocality' => 'New Cairo',
                'addressRegion'   => 'Cairo',
                'addressCountry'  => 'EG',
            ],
        ], fn ($v) => ! is_null($v) && $v !== []);
    }

    public function breadcrumbSchema(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            'itemListElement' => array_map(function (array $item, int $idx) {
                [$name, $url] = $item;
                return [
                    '@type'    => 'ListItem',
                    'position' => $idx + 1,
                    'name'     => $name,
                    'item'     => $url,
                ];
            }, $items, array_keys($items)),
        ];
    }

    public function creativeWorkSchema(Project $project, string $url, ?string $image): array
    {
        $name = $this->profile['owner_name'] ?? 'Ahmed Abo Dooh';

        return array_filter([
            '@context'    => 'https://schema.org',
            '@type'       => 'CreativeWork',
            '@id'         => $url . '#work',
            'name'        => $project->title,
            'headline'    => $project->title,
            'description' => $project->summary ?: $project->tagline,
            'url'         => $url,
            'image'       => $image,
            'author'      => ['@id' => $this->baseUrl . '/#person'],
            'creator'     => ['@id' => $this->baseUrl . '/#person'],
            'dateCreated' => $project->year ? $project->year . '-01-01' : null,
            'keywords'    => is_array($project->tech_stack) ? implode(', ', $project->tech_stack) : null,
            'about'       => $project->category,
        ], fn ($v) => ! is_null($v) && $v !== '');
    }

    public function articleSchema(BlogPost $post, string $url, ?string $image): array
    {
        $name = $this->profile['owner_name'] ?? 'Ahmed Abo Dooh';

        return array_filter([
            '@context'      => 'https://schema.org',
            '@type'         => 'BlogPosting',
            '@id'           => $url . '#article',
            'mainEntityOfPage' => $url,
            'headline'      => $post->title,
            'description'   => $post->excerpt,
            'image'         => $image,
            'datePublished' => optional($post->published_at)->toIso8601String(),
            'dateModified'  => optional($post->updated_at)->toIso8601String(),
            'author'        => ['@id' => $this->baseUrl . '/#person'],
            'publisher'     => ['@id' => $this->baseUrl . '/#person'],
            'keywords'      => is_array($post->tags) ? implode(', ', $post->tags) : null,
            'inLanguage'    => 'en',
            'wordCount'     => $post->body ? str_word_count(strip_tags($post->body)) : null,
        ], fn ($v) => ! is_null($v) && $v !== '');
    }

    public function collectionPageSchema(string $title, string $description, string $url, $projects): array
    {
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'CollectionPage',
            '@id'         => $url . '#collection',
            'name'        => $title,
            'description' => $description,
            'url'         => $url,
            'isPartOf'    => ['@id' => $this->baseUrl . '/#website'],
            'hasPart'     => $projects->map(fn (Project $p) => [
                '@type' => 'CreativeWork',
                'name'  => $p->title,
                'url'   => $this->baseUrl . '/projects/' . $p->slug,
            ])->values()->all(),
        ];
    }

    public function blogSchema(string $title, string $description, string $url, $posts): array
    {
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'Blog',
            '@id'         => $url . '#blog',
            'name'        => $title,
            'description' => $description,
            'url'         => $url,
            'publisher'   => ['@id' => $this->baseUrl . '/#person'],
            'blogPost'    => $posts->map(fn (BlogPost $p) => [
                '@type'         => 'BlogPosting',
                'headline'      => $p->title,
                'url'           => $this->baseUrl . '/blog/' . $p->slug,
                'datePublished' => optional($p->published_at)->toIso8601String(),
            ])->values()->all(),
        ];
    }

    public function contactPageSchema(): array
    {
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'ContactPage',
            '@id'         => $this->baseUrl . '/contact#page',
            'url'         => $this->baseUrl . '/contact',
            'mainEntity'  => ['@id' => $this->baseUrl . '/#person'],
        ];
    }

    /* ===================================================================== */
    /* Helpers                                                                */
    /* ===================================================================== */

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    private function absoluteUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }
        return $this->baseUrl . '/' . ltrim($value, '/');
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function truncate(string $value, int $max): string
    {
        $value = trim(preg_replace('/\s+/', ' ', strip_tags($value)));
        if (mb_strlen($value) <= $max) {
            return $value;
        }
        return rtrim(mb_substr($value, 0, $max - 1)) . '…';
    }
}
