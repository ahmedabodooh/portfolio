<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Certification;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Skill;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedAdminUser();
        $this->seedExperiences();
        $this->seedProjects();
        $this->seedSkills();
        $this->seedCertifications();
        $this->seedBlogPosts();
    }

    /* ===================================================================== */

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'owner_name',     'value' => 'Ahmed Abo Dooh',                       'group' => 'identity'],
            ['key' => 'owner_role',     'value' => 'Full-Stack Developer · Laravel & PHP',  'group' => 'identity'],
            ['key' => 'owner_tagline',  'value' => 'I architect and ship production Laravel platforms — from real-time ERPs managing thousands of users to marketplaces processing live payments, with an obsessive focus on clean code, query performance, and uptime.', 'group' => 'identity'],
            ['key' => 'owner_location', 'value' => 'New Cairo, Fifth Settlement, Egypt',    'group' => 'identity'],
            ['key' => 'owner_email',    'value' => 'zalfyhima@gmail.com',                   'group' => 'identity'],
            ['key' => 'owner_github',   'value' => 'https://github.com/ahmedabodooh',       'group' => 'social'],
            ['key' => 'owner_linkedin', 'value' => 'https://linkedin.com/in/ahmed-abo-dooh-2767a6299', 'group' => 'social'],
            ['key' => 'available_status','value' => 'Available for freelance & full-time',   'group' => 'identity'],
            ['key' => 'owner_photo',    'value' => null, 'type' => 'image', 'group' => 'identity'],
            ['key' => 'resume_file',    'value' => null, 'type' => 'image', 'group' => 'identity'],
            ['key' => 'og_image',       'value' => null, 'type' => 'image', 'group' => 'seo'],
            ['key' => 'background_music',       'value' => null, 'type' => 'file', 'group' => 'media'],
            ['key' => 'background_music_title', 'value' => 'Ambient focus loop',            'group' => 'media'],
        ];

        foreach ($settings as $row) {
            SiteSetting::updateOrCreate(['key' => $row['key']], $row);
        }
    }

    private function seedAdminUser(): void
    {
        User::updateOrCreate(
            ['email' => 'zalfyhima@gmail.com'],
            [
                'name'     => 'Ahmed Abo Dooh',
                'password' => Hash::make('password'),
            ]
        );
    }

    /* ===================================================================== */

    private function seedExperiences(): void
    {
        $rows = [
            [
                'company'  => 'Rabehni',
                'role'     => 'Full-Stack Developer (Part-Time Lead)',
                'location' => 'Remote · Egypt',
                'period'   => 'Jun 2025 — Present',
                'started_at' => '2025-06-01',
                'ended_at'   => null,
                'summary'    => 'Leading development on Rabehni, an internal ERP the company uses to run its own operations — I own everything from schema design to queue workers and the customer-facing dashboard.',
                'highlights' => [
                    'Designed the entire database schema from scratch (products, orders, suppliers, warehouses, financials, staff).',
                    'Built a Filament-based admin with role-scoped permissions for sales, operations, and finance teams.',
                    'Implemented a double-entry financial ledger so every movement (income, expense, refund) has an audit trail.',
                    'Wrote background workers for invoice generation, inventory sync, and scheduled reports.',
                    'Pushed Core Web Vitals green across the whole app by profiling N+1 queries and layering Redis.',
                ],
                'sort_order' => 1,
            ],
            [
                'company'  => 'Digital Media Factory',
                'role'     => 'Back-End Developer',
                'location' => 'Dokki, Giza, Egypt',
                'period'   => 'Mar 2025 — Nov 2025',
                'started_at' => '2025-03-01',
                'ended_at'   => '2025-11-30',
                'summary'    => 'Building scalable Laravel backends with a focus on RESTful API design, caching, and modular architecture.',
                'highlights' => [
                    'Contributed to scalable backend systems using Laravel & PHP with RESTful APIs, database optimization, and modular architecture.',
                    'Implemented Redis caching and indexing strategies to cut response times and reduce server load.',
                    'Handled MySQL optimization, query refactoring, and background jobs via Laravel Queues.',
                    'Collaborated with front-end and mobile teams for smooth API integration.',
                    'Applied SOLID principles and repository pattern for maintainable systems.',
                ],
                'sort_order' => 10,
            ],
            [
                'company'  => '5StarCode',
                'role'     => 'Full Stack Developer',
                'location' => 'October, Giza, Egypt',
                'period'   => 'May 2024 — Mar 2025',
                'started_at' => '2024-05-01',
                'ended_at'   => '2025-03-31',
                'summary'    => 'Full-stack Laravel applications covering auth, dashboards, payments, and reporting.',
                'highlights' => [
                    'Built admin dashboards, product modules, and integrated Paymob & Stripe payment gateways.',
                    'Worked with RESTful APIs, complex form validation, file uploads, and reporting.',
                    'Applied advanced Eloquent relationships and query optimization for large datasets.',
                    'Delivered in Agile sprints with Git pull requests and code reviews.',
                ],
                'sort_order' => 20,
            ],
            [
                'company'  => 'Bedaya Academy and House',
                'role'     => 'Software Instructor',
                'location' => 'Cairo, Egypt',
                'period'   => 'May 2024 — Dec 2024',
                'started_at' => '2024-05-01',
                'ended_at'   => '2024-12-31',
                'summary'    => 'Taught programming foundations and web development to students across age groups.',
                'highlights' => [
                    'Covered algorithms, logic, and software development basics.',
                    'Guided students through HTML, CSS, JavaScript and small interactive projects.',
                    'Delivered beginner-friendly courses in Python and Scratch.',
                    'Designed structured lesson plans and coding challenges.',
                ],
                'sort_order' => 30,
            ],
            [
                'company'  => 'Route Academy',
                'role'     => 'Back-End Developer Intern',
                'location' => 'Cairo, Egypt',
                'period'   => 'Oct 2024 — Mar 2025',
                'started_at' => '2024-10-01',
                'ended_at'   => '2025-03-31',
                'summary'    => 'Hands-on Laravel internship applying MVC and OOP principles.',
                'highlights' => [
                    'Built RESTful APIs with JWT authentication and multi-entity CRUD systems.',
                    'Worked with migrations, factories, and seeders for automated database setup.',
                    'Learned middleware, service providers, and dependency injection.',
                ],
                'sort_order' => 40,
            ],
            [
                'company'  => 'Professionals Academy',
                'role'     => 'Back-End Developer Intern',
                'location' => 'Cairo, Egypt',
                'period'   => 'May 2023 — Sep 2023',
                'started_at' => '2023-05-01',
                'ended_at'   => '2023-09-30',
                'summary'    => 'PHP & MySQL fundamentals — how backend systems interact with clients.',
                'highlights' => [
                    'Built simple CRUD APIs and DB-driven apps using procedural PHP and early Laravel.',
                    'Practiced schema design, normalization, and query optimization.',
                    'Debugging, error handling, and secure input validation.',
                ],
                'sort_order' => 50,
            ],
        ];

        foreach ($rows as $row) {
            Experience::updateOrCreate(
                ['company' => $row['company'], 'role' => $row['role']],
                $row
            );
        }
    }

    /* ===================================================================== */

    private function seedProjects(): void
    {
        $rows = [
            /* ====================== Rabehni (new) ====================== */
            [
                'title'    => 'Rabehni',
                'slug'     => 'rabehni',
                'category' => 'ERP · Full-Stack',
                'client'   => 'Rabehni (internal product)',
                'role'     => 'Full-Stack Lead (part-time)',
                'year'     => 2025,
                'tagline'  => 'An end-to-end ERP powering the company I work with — sales, inventory, finance, and staff in one cockpit.',
                'summary'  => 'Rabehni is an internal ERP I lead development on. The product runs day-to-day operations for the company — from ingesting supplier orders, through warehouse moves, to closing the books every month. I own the backend, the admin, the customer-facing panel, and the deploy pipeline.',
                'description' => <<<'TEXT'
Rabehni is not a freelance gig — I'm part of the company, building the system they run their business on. The product started as a spreadsheet and a prayer. By the time I joined the stack was already under strain: stale caches, N+1 reads on the invoice screen, queues backing up during daily reconciliation.

Architecture. Laravel 11 application structured around bounded contexts — each module (Inventory, Sales, Finance, CRM, HR) exposes its own service layer, and cross-module calls go through events, not direct model access. That lets us evolve one module without breaking the others. Domain events (OrderPlaced, StockDepleted, InvoiceIssued) are dispatched synchronously for validation and asynchronously for reporting.

Data model. MySQL 8 with ~60 tables; strict foreign keys, composite indexes tuned after EXPLAIN profiling, and JSON columns for flexible per-tenant attributes. Heavy reads (dashboards, reports) are served from materialised aggregate tables refreshed by scheduled jobs — not live queries.

Finance is a proper double-entry ledger. Every transaction writes two rows (debit + credit), the journal is append-only, and period closing is a database transaction that locks the month. This single design decision killed an entire class of reconciliation bugs.

Caching. Redis handles sessions, the queue, and a read-through cache for hot routes. I avoid blanket "cache everything" — tags are scoped per module so invalidating one entity doesn't nuke the whole site.

Background work. Supervisor keeps four queue workers alive (high, default, emails, reports). Long-running jobs (PDF generation, Excel exports, bulk inventory imports) run on their own queue so they can't starve urgent jobs.

Frontend. Filament v3 for the admin — Ahmed-tuned: custom widgets, per-role panels, inline editing for fast data entry. The customer-facing side is Blade + Livewire + Tailwind, SSR for SEO and speed.

Ops. GitHub Actions CI (phpstan + pest + pint) on every push. Deployments run through zero-downtime symlink swaps. Logs stream to a self-hosted Grafana Loki instance so we can grep production without SSH.
TEXT,
                'highlights' => [
                    'Double-entry financial ledger — every debit has a credit, period closing is transactional, audit trail is append-only.',
                    'Bounded-context module design so sales, inventory, and finance evolve independently via domain events.',
                    'Materialised aggregate tables for dashboards — trading a refresh cron for sub-100ms report loads.',
                    'Filament v3 admin with per-role panels, inline editing, and custom widgets for power users.',
                    'Supervisor + four prioritised queues so PDF exports never starve urgent order jobs.',
                    'GitHub Actions CI (phpstan, pest, pint) gating every merge; zero-downtime deploys via symlink swap.',
                ],
                'tech_stack'  => ['Laravel 11', 'PHP 8.3', 'MySQL 8', 'Redis', 'Filament v3', 'Livewire', 'Tailwind', 'Supervisor', 'GitHub Actions', 'Grafana Loki'],
                'live_url'    => 'https://rabehni.com',
                'is_featured' => true, 'sort_order' => 1,
            ],

            /* ====================== ProstarTCN (new) ====================== */
            [
                'title'    => 'Prostar TCN',
                'slug'     => 'prostar-tcn',
                'category' => 'SaaS · Full-Stack',
                'client'   => 'Prostar TCN',
                'role'     => 'Full-Stack Engineer (end-to-end)',
                'year'     => 2025,
                'tagline'  => 'A creator-management platform for a TikTok agency — handling 1,000+ creators, their earnings, messaging, and payouts.',
                'summary'  => 'Prostar TCN is a TikTok Creator Network dashboard that I designed and built from zero. It manages over a thousand creators: their accounts, daily performance, earnings, internal messaging, and monthly payouts. Every piece — schema, API, jobs, UI, ops — is mine.',
                'description' => <<<'TEXT'
Building a platform for an agency that works with 1,000+ creators means two things are non-negotiable: the numbers have to be right, and operations can't wait on a human. That shaped every design decision.

Data model. Creator accounts are a hierarchy — agency → manager → creator → TikTok account (a creator can have several). Earnings are imported nightly from CSVs and platform APIs, cross-referenced against the agency's ledger, and attributed with a commission engine that handles tiered rates, overrides for top creators, and retroactive adjustments. PostgreSQL would have been nicer for the money columns, but MySQL 8 + DECIMAL(12,4) works fine and the ops team already knew MySQL.

Cron jobs are the backbone. Laravel's scheduler runs: nightly import, daily performance rollups per creator, weekly payout calculation, monthly invoice generation. Every scheduled task is idempotent (safe to re-run) and logs to a dedicated audit table so I can prove what happened when an accountant asks.

Messaging. Each creator has an inbox — their manager can send briefs, contracts, payout notices; creators reply from a mobile-friendly web panel. Messages are stored normalised with delivery receipts. Push notifications fire via FCM tokens; email fallback via Resend.

Auth is split. Managers use Laravel Sanctum with short-lived sessions. Creators authenticate via magic-link email + OTP — most never install an app, and password resets were eating the support team alive.

Performance. The creators table is hot — searchable by name, TikTok handle, earnings range. I added composite indexes (agency_id, status, earnings), full-text on names, and cursor-based pagination so the "show all creators earning over X this month" query never scans more than it needs.

Ops. Docker Compose for local dev (app, mysql, redis, mailhog). Production runs on a single DO droplet for now — Nginx + PHP-FPM + MySQL + Redis, Supervisor for queue workers, Laravel Horizon for queue visibility. I set up Sentry for errors and Better Stack for uptime.

Hard parts. (1) Earnings reconciliation: platform numbers and agency numbers rarely match exactly — I built a three-way reconciliation view that flags variance above 1% for manual review. (2) Timezone edge cases in the nightly cron — everything stores UTC, displays in the agency's timezone, and payout periods are anchored to calendar months in that timezone.
TEXT,
                'highlights' => [
                    'Manages 1,000+ creator accounts with hierarchical agency → manager → creator structure.',
                    'Nightly cron pipeline imports earnings, computes commissions (tiered + overrides), emits monthly invoices.',
                    'Three-way reconciliation view flags >1% earnings variance between platform and agency ledger.',
                    'Magic-link + OTP auth for creators (most aren\'t technical), Sanctum sessions for managers.',
                    'In-app messaging with delivery receipts, FCM push, Resend email fallback.',
                    'Horizon dashboard for queue health; Sentry for errors; Better Stack for uptime monitoring.',
                    'Cursor-based pagination and composite indexes tuned for the hot creators-search query.',
                ],
                'tech_stack'  => ['Laravel 11', 'PHP 8.3', 'MySQL 8', 'Redis', 'Horizon', 'Sanctum', 'Livewire', 'Tailwind', 'FCM', 'Resend', 'Docker', 'Sentry'],
                'live_url'    => 'https://prostar-tcn.com',
                'is_featured' => true, 'sort_order' => 2,
            ],

            /* ====================== Venyo ====================== */
            [
                'title'    => 'Venyo',
                'slug'     => 'venyo',
                'category' => 'Full-Stack · Laravel',
                'client'   => 'Venyo',
                'role'     => 'Back-End Lead',
                'year'     => 2024,
                'tagline'  => 'Restaurant reservation platform with real-time availability and pre-ordering.',
                'summary'  => 'A full-stack Laravel application for seamless restaurant reservations, table management, and pre-ordering dishes — built with WebSockets-driven sync and multi-role access.',
                'description' => <<<'TEXT'
Venyo is a reservation platform where every second matters — if two customers book the same table, the business loses trust. The technical challenge was never the CRUD; it was the consistency under concurrency.

Concurrency model. Reservations are serialized through optimistic locking — the tables.version column increments on every change, and the insert of a new reservation is guarded with a transaction that rechecks availability. If two people click "book" within a hundred milliseconds, exactly one wins. The other sees the updated state and is offered the next slot.

Real-time. Laravel Broadcasting + Pusher push slot changes to every open session for the same restaurant. Customers see the table turn yellow when someone else is holding it, red when it's gone — without a page refresh.

Pre-ordering. A reservation can include a cart of dishes; these enter the kitchen's queue fifteen minutes before the reservation. The kitchen panel is its own Filament-based view with WebSocket updates so dishes appear as orders come in.

Caching. Restaurant discovery (search by cuisine, location, preferences) hits Redis with a 60-second TTL. The cache key encodes the user's location grid cell (snapped to ~500m), so nearby users share cache entries without polluting.

Performance. Eloquent queries for the availability endpoint were profiled with Laravel Debugbar, then rewritten to a single query with a subquery-driven window function. p95 latency dropped from 420ms to 38ms.
TEXT,
                'highlights' => [
                    'Optimistic locking (version column + transactional recheck) guarantees no double-booking under concurrency.',
                    'Real-time slot state via Laravel Broadcasting + Pusher — yellow/red tables update instantly for every open session.',
                    'Kitchen panel receives pre-orders 15 min before reservation; live board updates over WebSocket.',
                    'Location-aware Redis caching (grid-snapped keys) so nearby users share cache entries.',
                    'Rewrote the availability endpoint with a window-function subquery; p95 420ms → 38ms.',
                    'Multi-role Sanctum auth (admin, staff, customer) with token scopes.',
                ],
                'tech_stack'  => ['Laravel', 'MySQL', 'Redis', 'WebSockets', 'Pusher', 'Sanctum', 'Horizon'],
                'live_url'    => 'https://venyo.site/customer/index.html',
                'is_featured' => true, 'sort_order' => 10,
            ],

            /* ====================== JCC CRM ====================== */
            [
                'title'    => 'JCC CRM System',
                'slug'     => 'jcc-crm',
                'category' => 'Full-Stack · Laravel',
                'client'   => 'JCC',
                'role'     => 'Full-Stack Developer',
                'year'     => 2024,
                'tagline'  => 'Sales & finance CRM with RBAC, pipeline automation, and analytics.',
                'summary'  => 'A full-stack Laravel CRM for managing sales pipelines, clients, deals, invoices, and financial transactions with granular role-based access.',
                'description' => <<<'TEXT'
JCC is a CRM that sits between sales, ops, and finance. It has three user personas — admin, sales rep, finance — with dramatically different permissions and views, which makes RBAC the centrepiece of the architecture.

Permissions. spatie/laravel-permission powers 40+ granular permissions, grouped into roles. Permission checks happen at three levels: route middleware (coarse), controller policy (entity-level), and view @can directives (UI pruning). Nothing leaks.

Pipelines. Deals move through stages via an Eloquent state machine — illegal transitions throw before touching the DB. Each transition fires a domain event so automation (lead reassignment, Slack notification, email follow-up) is decoupled from the sale logic.

Dashboards. Revenue, conversion, and team-performance charts pull from pre-aggregated daily rollups (a scheduled job runs each midnight). Live widgets poll the rollup — no blocking reads against the transactional tables.

Automation. Follow-up tasks schedule themselves when a deal sits in a stage too long. Overdue invoices trigger a reminder email via queued jobs. A dead-letter queue catches broken webhook payloads so nothing silently drops.

Query tuning. The deal-list view was slow until I added a composite (owner_id, stage, updated_at) index and moved the "days in stage" computation to a stored column updated by a trigger. List render went from 1.4s to 90ms on a 50k-deal dataset.
TEXT,
                'highlights' => [
                    'spatie/laravel-permission with 40+ granular permissions — enforced at route, policy, and view layers.',
                    'Eloquent state machine governs deal stages; illegal transitions throw before touching the DB.',
                    'Pre-aggregated daily rollups power dashboards — live widgets never touch the transactional tables.',
                    'Automation: overdue invoice reminders, stale-deal alerts, Slack notifications via queued jobs.',
                    'Dead-letter queue captures failed webhook payloads for safe replay.',
                    'Deal-list query: composite index + stored "days in stage" → 1.4s → 90ms on 50k rows.',
                ],
                'tech_stack'  => ['Laravel', 'MySQL', 'spatie/permission', 'Redis', 'Livewire', 'Chart.js', 'Horizon'],
                'live_url'    => 'https://jccsystem.site/',
                'is_featured' => true, 'sort_order' => 20,
            ],

            /* ====================== D&S Law ====================== */
            [
                'title'    => 'D&S Law Firm',
                'slug'     => 'ds-law-firm',
                'category' => 'Full-Stack · Laravel',
                'role'     => 'Back-End Developer',
                'year'     => 2024,
                'tagline'  => 'Bilingual legal consultancy site with schema-rich SEO.',
                'summary'  => 'Bilingual Laravel application with Blade templates, JSON-LD structured data, modular routing, and zero-downtime deploys.',
                'description' => <<<'TEXT'
A law firm's site has to do one thing: rank. D&S Law serves the Saudi market in both Arabic and English, which means two things: RTL layout support that doesn't break, and schema markup that Google can actually read.

i18n. Laravel localization with per-locale routing (/ar/..., /en/...) and proper hreflang tags so Google doesn't think the two languages are duplicates. The CMS stores content per-locale in the same table with a locale column; fallbacks kick in if a translation is missing.

Structured data. Every service page emits JSON-LD (LegalService + Service schemas). Case study articles emit Article schema with author/publisher. The sitemap lives at /sitemap.xml and auto-rebuilds on content save.

Routing. I modularised route files by feature (services.php, articles.php, contact.php) — loaded from RouteServiceProvider. Middleware stacks per route group, form requests for validation, no fat controllers.

Performance. Blade views compile once; static assets are versioned by Vite and served with long-cache headers behind Cloudflare. TTFB is under 180ms on my benchmark from Jeddah.

Deployment. Git-based CI/CD with zero downtime: the deploy script builds the new release in a sibling directory, runs migrations, swaps the symlink, and reloads PHP-FPM — all while the old release still serves traffic.
TEXT,
                'highlights' => [
                    'Bilingual (AR/EN) with per-locale routes, hreflang tags, and RTL-aware layouts.',
                    'JSON-LD schemas (LegalService, Service, Article) emitted on every relevant page.',
                    'Modular route files loaded by RouteServiceProvider — no monolithic routes/web.php.',
                    'Cloudflare + Vite versioned assets + long-cache headers; TTFB < 180ms from Jeddah.',
                    'Zero-downtime deploys via symlink swap; PHP-FPM reload never drops requests.',
                ],
                'tech_stack'  => ['Laravel', 'Blade', 'MySQL', 'JSON-LD', 'Cloudflare', 'i18n'],
                'live_url'    => 'https://ds-law.sa/',
                'is_featured' => false, 'sort_order' => 30,
            ],

            /* ====================== Almashahir Sport ====================== */
            [
                'title'    => 'Almashahir Sport',
                'slug'     => 'almashahir-sport',
                'category' => 'Full-Stack · Laravel',
                'role'     => 'Back-End Developer',
                'year'     => 2024,
                'tagline'  => 'Large-scale sports news platform with dynamic CMS and SEO optimization.',
                'summary'  => 'Sports news system with category-based publishing, breaking news, trending posts, media galleries, and advanced SEO — scaled for peak traffic.',
                'description' => <<<'TEXT'
News sites live and die by traffic spikes. When a match ends, everyone refreshes at once. The platform has to survive that without pooping its database.

CMS. Content types are Post, Gallery, Video, Breaking News — all share a polymorphic categories relationship and a tag system. The editor is a rich block-based composer (TipTap on the frontend, normalized JSON body on the backend), which makes it possible to reuse content fragments across channels.

Caching. Full-page cache via Cloudflare with a 60-second TTL plus a stale-while-revalidate header. On the app side, every homepage widget is Redis-cached with a soft invalidation scheme: a new post in a category bumps a version counter, and widget keys are suffixed with the version, so old keys expire naturally without cache-clear storms.

SEO. Dynamic sitemap at /sitemap.xml with nested indexes (posts, galleries, videos). Meta + OG + Twitter cards on every model with sensible defaults. JSON-LD NewsArticle schemas. IndexNow ping on publish so Bing sees content within seconds.

Media. Galleries use lazy-loaded images with LQIP placeholders; videos are embedded with privacy-first iframes. Everything is served through a Cloudflare transform so thumbnails are generated on demand.

Performance. The trending module queries would have been slow on millions of rows — I moved the "views per hour" aggregation to a Redis sorted set updated from a queue job, and the page reads from that. Homepage render is under 200ms even during traffic spikes.
TEXT,
                'highlights' => [
                    'Full-page Cloudflare cache + stale-while-revalidate; app-side Redis with version-bump invalidation.',
                    'Polymorphic content model (posts, galleries, videos) sharing categories and tags.',
                    'Nested sitemap indexes; JSON-LD NewsArticle; IndexNow ping on publish.',
                    '"Views per hour" aggregated in a Redis sorted set (updated by queue) — no per-request DB hit.',
                    'LQIP placeholders + on-demand Cloudflare image transforms for fast galleries.',
                    'Block-based editor (TipTap) writes normalised JSON; reusable fragments across channels.',
                ],
                'tech_stack'  => ['Laravel', 'MySQL', 'Redis', 'Cloudflare', 'TipTap', 'JSON-LD', 'IndexNow'],
                'live_url'    => 'https://almashahirsport.com/',
                'is_featured' => true, 'sort_order' => 40,
            ],

            /* ====================== Arab FLC ====================== */
            [
                'title'    => 'Arab FLC',
                'slug'     => 'arab-flc',
                'category' => 'Full-Stack · Laravel',
                'role'     => 'Back-End Developer',
                'year'     => 2024,
                'tagline'  => 'Land & Climate Forum — sessions, speakers, attendees, and courses in one system.',
                'summary'  => 'Comprehensive Laravel system managing every edition of the forum — sessions, speakers, attendees, courses — with a dynamic organizer dashboard.',
                'description' => <<<'TEXT'
Event platforms are deceptively complex: every entity depends on time, and most of them pluralise (speakers become keynotes plus panelists plus workshop leaders). The schema is the whole game.

Schema. Edition → Day → Session → SessionParticipant (polymorphic so one row can reference speakers, attendees, sponsors, or staff). Courses have enrollments; enrollments have progress; progress has certificates. The graph is five levels deep but every query is bounded by edition_id, which is always indexed.

Organiser dashboard. Real-time registration counts (via Horizon queue broadcasting), attendance heatmaps by session, course completion rates. All pre-aggregated nightly so dashboards don't beat up the OLTP tables.

Search. Scout + Meilisearch for fuzzy matching across speakers, sessions, and courses. The "find me the talk about X" query is instant regardless of dataset size.

Notifications. SMS + email + in-app notifications for reminders, schedule changes, certificate availability. Each channel is a separate queue so a failing SMS provider never delays an email.

RBAC. Five roles — admin, moderator, speaker, attendee, sponsor — with policy-enforced boundaries. Speakers see only their own sessions; moderators see their assigned tracks; sponsors see leads from their scanned badges.
TEXT,
                'highlights' => [
                    'Five-level deep schema (edition → day → session → participant) with every query bounded by edition_id.',
                    'Polymorphic SessionParticipant unifies speakers, attendees, sponsors, staff.',
                    'Meilisearch + Scout for fuzzy speaker/session/course search.',
                    'Pre-aggregated nightly rollups for organiser dashboards — OLTP tables stay fast.',
                    'SMS + email + in-app notifications on separate queues; failing provider doesn\'t cascade.',
                    'Policy-enforced RBAC across 5 roles — speakers, moderators, sponsors each see only their scope.',
                ],
                'tech_stack'  => ['Laravel', 'MySQL', 'Meilisearch', 'Scout', 'Horizon', 'Livewire'],
                'is_featured' => false, 'sort_order' => 50,
            ],

            /* ====================== Reconnect Investment ====================== */
            [
                'title'    => 'Reconnect Investment',
                'slug'     => 'reconnect-investment',
                'category' => 'Full-Stack · Laravel',
                'role'     => 'Back-End Developer',
                'year'     => 2024,
                'tagline'  => 'Real estate platform with Elasticsearch-powered search and blue-green deploys.',
                'summary'  => 'Modular Laravel architecture with repository and service layers, Redis caching, and Elasticsearch-powered property search.',
                'description' => <<<'TEXT'
Real estate search is a weird beast: users filter by price, location (map bounding box), bedrooms, amenities, and free-text descriptions — and they expect results in under 300ms. MySQL alone cannot do this well at scale. Enter Elasticsearch.

Search. Laravel Scout drives Elasticsearch with custom mappings: geo_point for location, nested objects for amenities, completion suggester for autocomplete, and Arabic analyzer for proper tokenisation of Arabic property names. Indexing happens via queued jobs so property edits don't block the writer.

Architecture. Repository pattern for data access + Service classes for business logic + Form requests for validation. Controllers stay thin. It's more ceremony than a tiny CRUD needs, but this codebase will outlive most of its current features, and the separation pays off during refactors.

Uploads. Signed URLs for document access (KYC files, property contracts) — the URL expires in 15 minutes, so leaked links are harmless. Files live on S3; Laravel never touches the bytes.

Audit. Every user-initiated change on a property writes to an audit table: who, what, when, from which IP. For a platform moving real money, "we don't know" is never an acceptable answer.

Deploy. Blue-green on production: two identical stacks behind a load balancer. A deploy brings up the new stack, runs smoke tests, then flips the balancer. Rollback is a single balancer flip. Migration safety is enforced — any migration marked destructive requires manual confirmation.
TEXT,
                'highlights' => [
                    'Elasticsearch with geo_point + nested amenities + Arabic analyzer; autocomplete via completion suggester.',
                    'Repository + Service + Form Request pattern — thin controllers, testable domain logic.',
                    'Signed S3 URLs (15-min TTL) for KYC files and property contracts.',
                    'Append-only audit table for every property change — who, what, when, IP.',
                    'Blue-green production deploys with smoke tests; rollback is a single load-balancer flip.',
                    'Destructive migrations require manual confirmation — safety rails baked into the deploy script.',
                ],
                'tech_stack'  => ['Laravel', 'Elasticsearch', 'Scout', 'Redis', 'Docker', 'S3', 'CI/CD'],
                'live_url'    => 'https://reconnectinvestment.com/',
                'is_featured' => true, 'sort_order' => 60,
            ],

            /* ====================== Alimama Market ====================== */
            [
                'title'    => 'Alimama Market',
                'slug'     => 'alimama-market',
                'category' => 'API · Laravel',
                'role'     => 'Back-End Developer',
                'year'     => 2024,
                'tagline'  => 'Multi-vendor marketplace REST API with real-time notifications.',
                'summary'  => 'RESTful Laravel API with Sanctum multi-role token scopes, real-time order updates, and asynchronous vendor balance processing.',
                'description' => <<<'TEXT'
A marketplace API is an orchestration of four untrusted parties — customers, vendors, couriers, and payment gateways. Every endpoint has to assume someone is trying to be clever.

Auth. Sanctum with token abilities: each token is scoped to a role (customer, vendor, courier, admin) and the specific abilities it needs. A vendor token cannot read another vendor's orders, even if they guess the URL.

Orders. Order state is a strict finite state machine with 9 states (pending → paid → prepared → picked_up → delivered → completed). Every transition is atomic in a DB transaction, fires a domain event, and is audited. Refunds and disputes have their own branch.

Balances. Vendors earn money as orders complete. Rather than compute balance on every read (slow) or maintain a column (race-prone), I use event sourcing for the financial module: every credit/debit is an append-only row, and the current balance is a materialised view refreshed by a queue job with a version check.

Notifications. Pusher for real-time order-status pushes; Laravel Broadcasting for the heavy lifting. Every push is idempotent — duplicates are safe.

Queues. Supervisor keeps 6 workers alive, split by urgency. Image processing (product uploads) has its own queue so it never blocks order-processing jobs. Failed jobs land in a DLQ table that's inspected daily.

Indexes. The orders table has composite indexes on (vendor_id, status, created_at), (customer_id, created_at), (courier_id, status). Each was added after profiling a real slow query, not speculatively.
TEXT,
                'highlights' => [
                    'Sanctum token abilities scope every token to a role + permission set — no implicit trust.',
                    'Order state as a 9-state FSM; every transition atomic, audited, and event-dispatching.',
                    'Event-sourced vendor balances with materialised view — append-only credits/debits, no race conditions.',
                    'Pusher + Laravel Broadcasting for idempotent real-time order pushes.',
                    '6 Supervisor-managed workers; image processing isolated so order queue never starves.',
                    'Composite indexes on orders added after profiling real queries, not speculatively.',
                ],
                'tech_stack'  => ['Laravel', 'Sanctum', 'Pusher', 'Docker', 'Redis', 'Supervisor'],
                'live_url'    => 'https://alimamamarket.com/',
                'is_featured' => false, 'sort_order' => 70,
            ],

            /* ====================== CapUmbrella ====================== */
            [
                'title'    => 'CapUmbrella',
                'slug'     => 'capumbrella',
                'category' => 'Frontend',
                'role'     => 'Front-End Developer',
                'year'     => 2023,
                'tagline'  => 'Investment projects marketing site with accessible, SEO-friendly markup.',
                'summary'  => 'Modular HTML/SCSS/JS front-end with reusable components, responsive layouts, and a Gulp-based asset pipeline.',
                'description' => <<<'TEXT'
A marketing site for an investment firm. Performance and clarity were the whole brief — the audience is skeptical, and slow or clunky would cost deals.

Architecture. Hand-rolled component pattern using HTML partials compiled by Gulp. No framework overhead. SCSS organised 7-1 (base, components, layout, pages, themes, utils, vendors). The entire site loads its CSS in a single file, minified, under 18KB.

Accessibility. Landmarks, skip links, focus visible styles, AA-compliant contrast. Forms use aria-describedby for error messages. All interactive elements are keyboard reachable and the logical tab order matches visual order.

SEO. Semantic markup (article, section, aside — not div soup). JSON-LD Organization schema on the homepage. Meta + OG + Twitter cards auto-generated per page. Hand-built sitemap.

Asset pipeline. Gulp handles SCSS → CSS, image optimization (imagemin), asset versioning (rev), and deployment rsync. A single `npm run build` produces a fully versioned, minified dist directory ready to upload.

Performance. Above-the-fold CSS is inlined; the rest loads async. Fonts are preloaded with font-display: swap. Images use <picture> with responsive srcsets. Lighthouse Performance 98.
TEXT,
                'highlights' => [
                    'Hand-rolled HTML partials compiled by Gulp — no framework overhead, CSS under 18KB.',
                    'SCSS 7-1 architecture; component-based naming; theme-agnostic.',
                    'WCAG AA: landmarks, skip links, focus-visible styles, keyboard-reachable controls.',
                    'JSON-LD Organization schema + per-page OG/Twitter cards + hand-built sitemap.',
                    'Inlined above-the-fold CSS, preloaded fonts, responsive <picture> sources. Lighthouse 98.',
                ],
                'tech_stack'  => ['HTML', 'SCSS', 'JavaScript', 'Bootstrap', 'Gulp'],
                'live_url'    => 'https://capumbrella.com/',
                'is_featured' => false, 'sort_order' => 80,
            ],

            /* ====================== Asas Floors ====================== */
            [
                'title'    => 'Asas Floors',
                'slug'     => 'asas-floors',
                'category' => 'Full-Stack · Laravel',
                'role'     => 'Full-Stack Developer',
                'year'     => 2023,
                'tagline'  => 'Flooring company CMS with multi-language and AJAX catalog.',
                'summary'  => 'Laravel CMS with modular services & products, AJAX search, Redis caching, and multi-language localization.',
                'description' => <<<'TEXT'
An e-commerce-adjacent CMS for a flooring company. The product catalog is the centre — services hang off it, pages link to it, SEO depends on it.

CMS. Laravel Nova-inspired custom admin (pre-Filament): reusable field types, per-module permissions, audit trail for every product edit. Products carry localized titles/descriptions per language via a translations table.

Catalog UX. AJAX-driven filtering — category, colour, finish, price. Results come back as JSON, rendered client-side; the URL is updated with History API so filtered views are shareable and browser-back works naturally. All query combinations are indexed at the DB level.

Caching. Redis caches service listings and media galleries with tag-based invalidation — a product edit only busts that product's tags, not the whole cache.

SEO + i18n. Two locales (AR/EN), hreflang, localized slugs, per-locale sitemaps. JSON-LD Product schema on every product page. Google Analytics 4 integrated with custom events (filter use, catalog scroll depth, contact form submit).

Reliability. A scheduled database backup runs nightly, uploads to off-site S3, and emails a SHA256 checksum. Email forms go through a queued job so form submission is instant even if SMTP is slow.
TEXT,
                'highlights' => [
                    'AJAX-filtered catalog with History API integration — shareable filtered URLs, browser-back works.',
                    'Per-locale slugs + hreflang + per-locale sitemaps — no Arabic/English duplicate content.',
                    'Redis tag-based cache invalidation — a product edit only busts its own tags.',
                    'JSON-LD Product schemas + GA4 custom events for filter use, scroll depth, conversions.',
                    'Nightly off-site S3 backups with SHA256 checksum emailed to ops.',
                ],
                'tech_stack'  => ['Laravel', 'MySQL', 'Redis', 'jQuery', 'SEO', 'i18n', 'S3'],
                'live_url'    => 'https://asasfloors.com/',
                'is_featured' => false, 'sort_order' => 90,
            ],
        ];

        foreach ($rows as $row) {
            Project::updateOrCreate(['slug' => $row['slug']], $row);
        }
    }

    /* ===================================================================== */

    private function seedSkills(): void
    {
        $groups = [
            'Languages' => ['PHP', 'JavaScript', 'TypeScript', 'SQL', 'C++'],
            'Backend'   => ['Laravel', 'MySQL', 'PostgreSQL', 'Firebase', 'REST APIs', 'GraphQL', 'Sanctum', 'JWT', 'Redis', 'Eloquent', 'Queues', 'Horizon'],
            'Frontend'  => ['HTML', 'CSS', 'SASS', 'Tailwind CSS', 'Blade', 'Livewire', 'Alpine.js', 'React', 'Redux', 'Bootstrap'],
            'Tools'     => ['Git', 'Docker', 'VS Code', 'Google Cloud', 'DigitalOcean', 'CI/CD', 'GitHub Actions', 'Elasticsearch', 'Meilisearch', 'Pusher', 'Nginx', 'Supervisor'],
        ];

        $sort = 0;
        foreach ($groups as $category => $names) {
            foreach ($names as $name) {
                Skill::updateOrCreate(
                    ['name' => $name, 'category' => $category],
                    ['sort_order' => $sort++, 'is_published' => true]
                );
            }
        }
    }

    /* ===================================================================== */

    private function seedCertifications(): void
    {
        $rows = [
            ['title' => 'Back-End with PHP',    'issuer' => 'Professionals Academy',           'sort_order' => 10],
            ['title' => 'Back-End Internship',  'issuer' => 'Route Academy',                   'sort_order' => 20],
            ['title' => 'WEB Advanced',         'issuer' => 'Bdaya Team',                      'sort_order' => 30],
            ['title' => 'Front-End with React', 'issuer' => 'Bdaya Team',                      'sort_order' => 40],
            ['title' => 'Electronic Crimes',    'issuer' => 'Technology Awareness Ambassador', 'sort_order' => 50],
        ];

        foreach ($rows as $row) {
            Certification::updateOrCreate(
                ['title' => $row['title'], 'issuer' => $row['issuer']],
                $row
            );
        }
    }

    /* ===================================================================== */

    private function seedBlogPosts(): void
    {
        $rows = [
            [
                'slug'    => 'scaling-laravel-queues-with-supervisor',
                'title'   => 'Scaling Laravel Queues with Supervisor',
                'excerpt' => 'A field guide to keeping long-running jobs healthy in production — worker counts, memory limits, graceful shutdowns.',
                'body'    => <<<'MD'
When a single worker isn't enough, Supervisor is the cleanest way to keep Laravel queue workers alive at scale. Here's the setup I've used across multiple production apps.

## Why Supervisor?

The Laravel docs recommend Supervisor for a reason — it auto-restarts dead workers, handles log rotation, and scales with `numprocs`. Compared to systemd, Supervisor is easier for multiple Laravel apps on one box.

## A sane starting config

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/app/artisan queue:work redis --tries=3 --timeout=90 --max-time=3600
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/laravel-worker.log
stopwaitsecs=3600
```

`stopwaitsecs` matters — Supervisor must give workers long enough to finish the in-flight job before `SIGKILL`, otherwise you get half-processed orders.

## Rules of thumb

1. **One queue per concern** — emails, webhooks, and heavy image processing shouldn't share a queue.
2. **Memory limits** — add `--memory=256` to recycle workers before they leak.
3. **Horizon if you're on Redis** — the UI alone is worth it.
MD,
                'tags'         => ['Laravel', 'Queues', 'DevOps', 'Redis'],
                'is_published' => true,
                'published_at' => now()->subDays(12),
            ],
            [
                'slug'    => 'when-to-reach-for-elasticsearch',
                'title'   => 'When to Reach for Elasticsearch (and When Not To)',
                'excerpt' => 'MySQL is faster than you think. Here is the rubric I use before introducing Elasticsearch into a Laravel stack.',
                'body'    => <<<'MD'
Every real estate or marketplace project eventually hits the question: *do we need Elasticsearch?*

Short answer: usually not yet. On Reconnect Investment it was the right call because we needed geo + fuzzy + faceted search. On a smaller CMS, a well-indexed MySQL with `FULLTEXT` is enough — and one fewer moving part in production.

## The rubric

Reach for Elasticsearch when **two or more** of these are true:
- You need fuzzy matching across multiple text fields simultaneously.
- Your facets exceed 3 dimensions and users filter interactively.
- You expect > 100k records with high query concurrency.
- Geo-distance queries are core (not a nice-to-have).

Otherwise: `FULLTEXT`, composite indexes, and Scout with a Meilisearch driver will beat a premature ES deploy 9 times out of 10.
MD,
                'tags'         => ['Laravel', 'Elasticsearch', 'MySQL', 'Search'],
                'is_published' => true,
                'published_at' => now()->subDays(30),
            ],
            [
                'slug'    => 'the-repository-pattern-nobody-asked-for',
                'title'   => 'The Repository Pattern Nobody Asked For',
                'excerpt' => 'A contrarian take on repositories in Laravel — when they add value, and when they are an abstraction tax on your team.',
                'body'    => <<<'MD'
Eloquent is a repository. That's not controversial — Taylor has said it. So why do so many Laravel codebases wrap it in a `UserRepository::find($id)` that just calls `User::find($id)`?

## My heuristic

Only introduce repositories when:
1. You will swap the data source (Elasticsearch, external API).
2. You need to enforce query scopes across every call site.
3. Your team genuinely tests against a fake repository, not just the DB.

Otherwise, let controllers talk to Eloquent. You'll ship faster and your juniors won't stare at 4 layers of indirection for a `SELECT *`.
MD,
                'tags'         => ['Laravel', 'Architecture', 'Opinion'],
                'is_published' => true,
                'published_at' => now()->subDays(55),
            ],
        ];

        foreach ($rows as $row) {
            BlogPost::updateOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
