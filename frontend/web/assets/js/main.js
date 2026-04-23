/**
 * main.js — page-level hydration from the Laravel API.
 * Each page declares data-page on <body> and main.js dispatches.
 */
(function () {
    const GRADIENTS = {
        'rabehni':              'from-violet-600 via-violet-800 to-blue-700',
        'prostar-tcn':          'from-fuchsia-600 via-purple-900 to-indigo-900',
        'venyo':                'from-amber-600 via-orange-900 to-red-900',
        'jcc-crm':              'from-blue-600 via-indigo-800 to-violet-900',
        'ds-law-firm':          'from-slate-600 via-slate-900 to-black',
        'almashahir-sport':     'from-rose-600 via-red-900 to-slate-900',
        'arab-flc':             'from-teal-600 via-teal-900 to-slate-900',
        'reconnect-investment': 'from-indigo-600 via-blue-800 to-slate-900',
        'alimama-market':       'from-orange-600 via-red-900 to-slate-900',
        'capumbrella':          'from-cyan-500 via-blue-800 to-slate-900',
        'asas-floors':          'from-emerald-600 via-teal-900 to-slate-900',
    };
    const gradientFor = (slug) => GRADIENTS[slug] ?? 'from-violet-600 via-violet-800 to-blue-700';

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;',
    }[c]));

    /* ---------- Skill icon registry (devicon class + brand color) ---------- */
    const SKILL_ICONS = {
        'php':           { icon: 'devicon-php-plain',          color: '#777bb4' },
        'javascript':    { icon: 'devicon-javascript-plain',   color: '#f7df1e' },
        'typescript':    { icon: 'devicon-typescript-plain',   color: '#3178c6' },
        'sql':           { svg: 'database',                    color: '#0064a5' },
        'c++':           { icon: 'devicon-cplusplus-plain',    color: '#00599c' },
        'laravel':       { icon: 'devicon-laravel-plain',      color: '#ff2d20' },
        'mysql':         { icon: 'devicon-mysql-plain',        color: '#00758f' },
        'postgresql':    { icon: 'devicon-postgresql-plain',   color: '#336791' },
        'firebase':      { icon: 'devicon-firebase-plain',     color: '#ffca28' },
        'rest apis':     { svg: 'plug',                        color: '#20b15a' },
        'graphql':       { icon: 'devicon-graphql-plain',      color: '#e10098' },
        'sanctum':       { svg: 'shield',                      color: '#ff2d20' },
        'jwt':           { svg: 'key',                         color: '#d63aff' },
        'redis':         { icon: 'devicon-redis-plain',        color: '#dc382d' },
        'eloquent':      { svg: 'orm',                         color: '#ff2d20' },
        'queues':        { svg: 'queue',                       color: '#ff9f1c' },
        'horizon':       { svg: 'radar',                       color: '#405cf5' },
        'html':          { icon: 'devicon-html5-plain',        color: '#e44d26' },
        'css':           { icon: 'devicon-css3-plain',         color: '#2965f1' },
        'sass':          { icon: 'devicon-sass-original',      color: '#cc6699' },
        'tailwind css':  { icon: 'devicon-tailwindcss-plain',  color: '#38bdf8' },
        'blade':         { icon: 'devicon-laravel-plain',      color: '#ff2d20' },
        'livewire':      { svg: 'bolt',                        color: '#4e56a6' },
        'alpine.js':     { svg: 'alpine',                      color: '#77c1d2' },
        'react':         { icon: 'devicon-react-original',     color: '#61dafb' },
        'redux':         { icon: 'devicon-redux-original',     color: '#764abc' },
        'bootstrap':     { icon: 'devicon-bootstrap-plain',    color: '#7952b3' },
        'git':           { icon: 'devicon-git-plain',          color: '#f05032' },
        'docker':        { icon: 'devicon-docker-plain',       color: '#2496ed' },
        'vs code':       { icon: 'devicon-vscode-plain',       color: '#007acc' },
        'google cloud':  { icon: 'devicon-googlecloud-plain',  color: '#4285f4' },
        'digitalocean':  { icon: 'devicon-digitalocean-plain', color: '#0080ff' },
        'ci/cd':         { svg: 'cicd',                        color: '#20b15a' },
        'github actions':{ icon: 'devicon-githubactions-plain',color: '#2088ff' },
        'elasticsearch': { icon: 'devicon-elasticsearch-plain',color: '#005571' },
        'meilisearch':   { svg: 'search',                      color: '#ff5caa' },
        'pusher':        { svg: 'broadcast',                   color: '#300d4f' },
        'nginx':         { icon: 'devicon-nginx-original',     color: '#009639' },
        'supervisor':    { svg: 'cog',                         color: '#405060' },
    };

    const FALLBACK_SVGS = {
        database:  '<path d="M12 3c4.97 0 9 1.57 9 3.5V17c0 1.93-4.03 3.5-9 3.5S3 18.93 3 17V6.5C3 4.57 7.03 3 12 3Z"/><path d="M3 6.5c0 1.93 4.03 3.5 9 3.5s9-1.57 9-3.5"/><path d="M3 11.5c0 1.93 4.03 3.5 9 3.5s9-1.57 9-3.5"/>',
        plug:      '<path d="M9 2v6"/><path d="M15 2v6"/><path d="M6 8h12v3a6 6 0 0 1-6 6 6 6 0 0 1-6-6V8Z"/><path d="M12 17v5"/>',
        shield:    '<path d="M12 2 4 5v7c0 5 3.4 9.3 8 10 4.6-.7 8-5 8-10V5l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
        key:       '<circle cx="7.5" cy="15.5" r="3.5"/><path d="m10 13 11-11"/><path d="m17 6 3 3"/><path d="m14 9 2 2"/>',
        orm:       '<rect x="3" y="4" width="7" height="7" rx="1.5"/><rect x="14" y="4" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="6" rx="1.5"/><rect x="14" y="14" width="7" height="6" rx="1.5"/><path d="M10 7.5h4"/><path d="M10 17h4"/>',
        queue:     '<rect x="3" y="5" width="4" height="14" rx="1"/><rect x="10" y="5" width="4" height="14" rx="1"/><rect x="17" y="5" width="4" height="14" rx="1"/>',
        radar:     '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><path d="M12 3v9l6 6"/>',
        bolt:      '<path d="m13 2-9 13h7l-2 7 9-13h-7l2-7Z"/>',
        alpine:    '<path d="m3 19 6-10 4 6 3-4 5 8Z"/>',
        cicd:      '<path d="M4 4h6v6H4Z"/><path d="M14 14h6v6h-6Z"/><path d="M10 7h4a3 3 0 0 1 3 3v4"/><path d="m15 12 2 2 2-2"/><path d="M14 17h-4a3 3 0 0 1-3-3v-4"/><path d="m9 12-2-2-2 2"/>',
        search:    '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        broadcast: '<circle cx="12" cy="12" r="2.5"/><path d="M7.5 7.5a6 6 0 0 0 0 9"/><path d="M16.5 7.5a6 6 0 0 1 0 9"/><path d="M4.5 4.5a10 10 0 0 0 0 15"/><path d="M19.5 4.5a10 10 0 0 1 0 15"/>',
        cog:       '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .4 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-1.8-.4 1.6 1.6 0 0 0-1 1.5V21a2 2 0 0 1-4 0v-.1a1.6 1.6 0 0 0-1-1.4 1.6 1.6 0 0 0-1.8.4l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.6 1.6 0 0 0 .4-1.8 1.6 1.6 0 0 0-1.5-1H3a2 2 0 0 1 0-4h.1a1.6 1.6 0 0 0 1.4-1 1.6 1.6 0 0 0-.4-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.6 1.6 0 0 0 1.8.4H9a1.6 1.6 0 0 0 1-1.5V3a2 2 0 0 1 4 0v.1a1.6 1.6 0 0 0 1 1.5 1.6 1.6 0 0 0 1.8-.4l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.4 1.8V9a1.6 1.6 0 0 0 1.5 1H21a2 2 0 0 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1Z"/>',
    };

    const skillIconHTML = (name) => {
        const key = String(name ?? '').toLowerCase().trim();
        const entry = SKILL_ICONS[key];
        if (!entry) {
            const initial = (name || '?').charAt(0).toUpperCase();
            return `<span class="skill-tile-initial" aria-hidden="true">${esc(initial)}</span>`;
        }
        if (entry.icon) {
            return `<i class="${entry.icon} skill-tile-devicon" style="--skill-color:${entry.color}" aria-hidden="true"></i>`;
        }
        const path = FALLBACK_SVGS[entry.svg] ?? '';
        return `<svg class="skill-tile-svg" viewBox="0 0 24 24" fill="none" stroke="${entry.color}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${path}</svg>`;
    };

    const qs = (sel, root = document) => root.querySelector(sel);
    const params = new URLSearchParams(window.location.search);

    // Slug resolution: supports both /projects/my-slug (pretty) and /pages/project.html?slug=my-slug.
    // Pretty URLs win for SEO; ?slug= stays as a fallback for legacy internal links.
    const slugFromPath = (collection) => {
        const m = window.location.pathname.match(new RegExp(`^/${collection}/([^/?#]+)/?$`));
        return m ? decodeURIComponent(m[1]) : null;
    };
    const resolveSlug = (collection) => slugFromPath(collection) || params.get('slug');

    // Client-side SEO touch-up: keeps title / description / canonical / og in sync
    // after JS hydration. Server-side SeoService is still the primary source of truth.
    const updateSeo = ({ title, description, canonical, image } = {}) => {
        if (title) document.title = title;
        const setMeta = (selector, attr, value) => {
            if (!value) return;
            let el = document.querySelector(selector);
            if (!el) {
                el = document.createElement('meta');
                const [, key] = selector.match(/\[(?:name|property)=["']([^"']+)["']\]/) || [];
                if (!key) return;
                if (selector.includes('property=')) el.setAttribute('property', key);
                else el.setAttribute('name', key);
                document.head.appendChild(el);
            }
            el.setAttribute(attr, value);
        };
        setMeta('meta[name="description"]',       'content', description);
        setMeta('meta[property="og:title"]',      'content', title);
        setMeta('meta[property="og:description"]','content', description);
        setMeta('meta[property="og:url"]',        'content', canonical || location.href);
        setMeta('meta[property="og:image"]',      'content', image);
        setMeta('meta[name="twitter:title"]',     'content', title);
        setMeta('meta[name="twitter:description"]','content', description);
        setMeta('meta[name="twitter:image"]',     'content', image);
        if (canonical) {
            let link = document.querySelector('link[rel="canonical"]');
            if (!link) {
                link = document.createElement('link');
                link.rel = 'canonical';
                document.head.appendChild(link);
            }
            link.href = canonical;
        }
    };

    /* ---------- Theme: push admin-controlled colors to :root ---------- */
    // hex -> "r, g, b" tuple (for rgba() interpolation)
    const hexToRgbTuple = (hex) => {
        const m = String(hex).trim().replace('#', '');
        if (m.length !== 3 && m.length !== 6) return null;
        const f = m.length === 3 ? m.split('').map(c => c + c).join('') : m;
        const n = parseInt(f, 16);
        return `${(n >> 16) & 255}, ${(n >> 8) & 255}, ${n & 255}`;
    };
    // Accepts { primary, primary_deep, primary_soft, primary_mist, primary_forest, ink, ... }
    // and writes matching CSS custom props on <html>.
    window.applyTheme = function (colors) {
        if (!colors || typeof colors !== 'object') return;
        const map = {
            primary: 'theme-primary', primary_deep: 'theme-primary-deep',
            primary_soft: 'theme-primary-soft', primary_mist: 'theme-primary-mist',
            primary_forest: 'theme-primary-forest', primary_tint: 'theme-primary-tint',
            ink: 'theme-ink', ink_2: 'theme-ink-2', ink_3: 'theme-ink-3', ink_4: 'theme-ink-4',
            surface: 'theme-surface', bg: 'theme-bg', error: 'theme-error',
        };
        const root = document.documentElement.style;
        for (const [key, varName] of Object.entries(map)) {
            const hex = colors[key];
            if (!hex) continue;
            root.setProperty(`--${varName}`, hex);
            const tuple = hexToRgbTuple(hex);
            if (tuple) root.setProperty(`--${varName}-rgb`, tuple);
        }
    };

    /* ============================================================ */
    /*   HOME                                                         */
    /* ============================================================ */
    async function hydrateHome() {
        try {
            const [profile, experiences, skills, certifications, projectsRes, postsRes, clients] = await Promise.all([
                API.profile(),
                API.experiences(),
                API.skills(),
                API.certifications(),
                API.projects({ per_page: 50 }),
                API.posts({ per_page: 3 }),
                API.clients().catch(() => []),
            ]);
            const projects = projectsRes.data ?? [];
            const featured = projects.filter(p => p.is_featured);
            const posts = postsRes.data ?? [];

            // --- Theme (admin-controlled colors, keyed under profile.theme_colors) ---
            window.applyTheme(profile.theme_colors);

            // --- Identity ---
            qs('[data-identity="name"]')?.replaceChildren(document.createTextNode(profile.owner_name ?? ''));
            qs('[data-identity="role"]')?.replaceChildren(document.createTextNode(profile.owner_role ?? ''));
            const tagEl = qs('[data-identity="tagline"]'); if (tagEl) tagEl.textContent = profile.owner_tagline ?? '';
            document.querySelectorAll('[data-identity="location"]').forEach(el => el.textContent = profile.owner_location ?? '');
            document.querySelectorAll('[data-identity="location-2"]').forEach(el => el.textContent = profile.owner_location ?? '');
            document.querySelectorAll('[data-identity="email"]').forEach((el) => {
                el.textContent = profile.owner_email ?? '';
                if (el.tagName === 'A') el.href = 'mailto:' + profile.owner_email;
            });
            const statusEl = qs('[data-identity="status"]');
            if (statusEl) statusEl.textContent = profile.available_status ?? '';

            // Re-render nav with the real owner name + status
            window.renderNav?.(document.body.dataset.page, profile.owner_name, profile.available_status);

            // --- Background music (admin-uploaded, optional) ---
            // Must run AFTER renderNav — the nav template contains the toggle button
            // that starts hidden until initMusic is called with a URL.
            window.initMusic?.(profile.background_music);

            // --- Portrait photo ---
            const portrait = qs('#portrait-card');
            if (portrait && profile.owner_photo) {
                const raw = profile.owner_photo;
                const src = raw.startsWith('http') || raw.startsWith('/')
                    ? raw
                    : '/storage/' + raw.replace(/^\/+/, '');
                portrait.querySelector('.portrait-placeholder')?.remove();
                const img = document.createElement('img');
                img.src = src;
                img.alt = profile.owner_name ?? 'Portrait';
                img.className = 'portrait-img';
                img.loading = 'eager';
                portrait.insertBefore(img, portrait.firstChild);
            }

            // --- Stats ---
            const pc = qs('[data-stat="projects"]');
            if (pc) { pc.dataset.countTo = projects.length; pc.textContent = projects.length + '+'; }

            // --- Featured projects — two-row auto-scrolling marquee ---
            const featEl = qs('#featured-projects');
            if (featEl) {
                const renderCard = (p) => {
                    const hasImage = !!p.cover_image;
                    const mediaInner = hasImage
                        ? `<img src="${esc(p.cover_image)}" alt="${esc(p.title)}" loading="lazy">`
                        : `<span class="pc-fallback-letter">${esc((p.title || '?').charAt(0))}</span>`;
                    const mediaClass = hasImage
                        ? 'pc-image has-image'
                        : `pc-image no-image bg-gradient ${gradientFor(p.slug)}`;
                    return `
                        <a href="/projects/${encodeURIComponent(p.slug)}" class="project-card-3d" aria-label="${esc(p.title)}">
                            <div class="pc-frame">
                                <div class="${mediaClass}">${mediaInner}</div>
                                <div class="pc-veil" aria-hidden="true"></div>
                                <div class="pc-sheen" aria-hidden="true"></div>
                                <div class="pc-info">
                                    <h3 class="pc-title">${esc(p.title)}</h3>
                                    ${p.tagline ? `<p class="pc-tagline">${esc(p.tagline)}</p>` : ''}
                                </div>
                            </div>
                            <span class="pc-arrow" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17l10-10M7 7h10v10"/></svg>
                            </span>
                        </a>`;
                };

                // Split featured into two rows (first half + second half).
                // Each row gets its content duplicated for seamless infinite marquee.
                const mid = Math.ceil(featured.length / 2);
                const rowA = featured.slice(0, mid);
                const rowB = featured.slice(mid).length ? featured.slice(mid) : [...featured].reverse();
                const renderRow = (items) => items.map(renderCard).join('') + items.map(renderCard).join('');

                featEl.innerHTML = `
                    <div class="projects-marquee">
                        <div class="projects-track projects-track--ltr">${renderRow(rowA)}</div>
                    </div>
                    <div class="projects-marquee">
                        <div class="projects-track projects-track--rtl">${renderRow(rowB)}</div>
                    </div>
                `;
            }

            // --- Experience ---
            const expEl = qs('#experience-list');
            if (expEl) {
                expEl.innerHTML = experiences.map((e, idx) => {
                    const isCurrent = /present/i.test(e.period || '');
                    const highlights = (e.highlights || []).slice(0, 3);
                    const dir = idx % 2 === 0 ? 'left' : 'right';
                    const delay = (idx % 2 === 0 ? 0 : 120);
                    return `
                    <article class="exp-card reveal" data-tilt data-enter="${dir}" style="--exp-delay:${delay}ms;">
                        <span class="exp-card-glow" aria-hidden="true"></span>
                        <span class="exp-decor" aria-hidden="true">
                            <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M40 4L72 22v36L40 76 8 58V22L40 4z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round" opacity=".6"/>
                                <path d="M40 4v36M40 40L8 22M40 40l32-18M40 40v36" stroke="currentColor" stroke-width="1" stroke-linejoin="round" opacity=".35"/>
                            </svg>
                        </span>
                        <div class="exp-card-inner">
                            <div class="exp-meta">
                                <span class="exp-period-chip${isCurrent ? ' is-live' : ''}">
                                    ${isCurrent ? '<span class="exp-dot-live"></span>' : '<span class="exp-dot"></span>'}
                                    <span class="mono">${esc(e.period)}</span>
                                </span>
                                ${e.location ? `<span class="exp-location"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1118 0z"/><circle cx="12" cy="10" r="3"/></svg>${esc(e.location)}</span>` : ''}
                            </div>
                            <div class="exp-heading">
                                <h3 class="exp-role">${esc(e.role)}</h3>
                                <p class="exp-company-line"><span class="exp-at">@</span><span class="exp-company">${esc(e.company)}</span></p>
                            </div>
                            ${e.summary ? `<p class="exp-summary">${esc(e.summary)}</p>` : ''}
                            ${highlights.length ? `
                                <ul class="exp-highlights">
                                    ${highlights.map(h => `<li><span class="exp-bullet">▸</span><span>${esc(h)}</span></li>`).join('')}
                                </ul>` : ''}
                        </div>
                    </article>
                `;
                }).join('');
                window.wireExpTilt?.(expEl);
            }

            // --- Skills ---
            const skillsEl = qs('#skills-list');
            if (skillsEl) {
                skillsEl.innerHTML = Object.entries(skills).map(([category, list], idx) => `
                    <article class="skill-category reveal" style="--cat-i:${idx}">
                        <header class="skill-category-head">
                            <span class="skill-category-index">0${idx + 1}</span>
                            <h3 class="skill-category-title">${esc(category)}</h3>
                            <span class="skill-category-count">${list.length}</span>
                        </header>
                        <div class="skill-tiles">
                            ${list.map(s => `
                                <span class="skill-tile" title="${esc(s.name)}">
                                    ${skillIconHTML(s.name)}
                                    <span class="skill-tile-name">${esc(s.name)}</span>
                                </span>
                            `).join('')}
                        </div>
                    </article>
                `).join('');
            }

            // --- Certifications ---
            const certsEl = qs('#certs-list');
            if (certsEl && certifications.length) {
                certsEl.innerHTML = certifications.map((c, idx) => {
                    const img = c.image ? '/storage/' + c.image : '';
                    const dir = idx % 2 === 0 ? 'left' : 'right';
                    const delay = idx * 110;
                    const wrap = c.credential_url ? 'a' : 'div';
                    const linkAttrs = c.credential_url
                        ? ` href="${esc(c.credential_url)}" target="_blank" rel="noopener"`
                        : '';
                    return `
                    <${wrap} class="cert-card reveal" data-cert-tilt data-enter="${dir}" style="--cert-delay:${delay}ms;"${linkAttrs}>
                        <div class="cert-card-glow" aria-hidden="true"></div>
                        <div class="cert-media">
                            ${img
                                ? `<img src="${esc(img)}" alt="${esc(c.title)} certificate" loading="lazy">`
                                : `<div class="cert-placeholder" aria-hidden="true">
                                       <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                                           <circle cx="32" cy="24" r="11"/>
                                           <path d="M24 33l-4 22 12-8 12 8-4-22"/>
                                           <circle cx="32" cy="24" r="5"/>
                                       </svg>
                                   </div>`}
                            <span class="cert-media-shine" aria-hidden="true"></span>
                            ${c.year ? `<span class="cert-year-badge">${esc(c.year)}</span>` : ''}
                        </div>
                        <div class="cert-body">
                            <h4 class="cert-title">${esc(c.title)}</h4>
                            <p class="cert-issuer"><span class="cert-issuer-dot"></span>${esc(c.issuer)}</p>
                            ${c.credential_url ? `<span class="cert-view">View credential
                                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17l10-10M7 7h10v10"/></svg>
                            </span>` : ''}
                        </div>
                    </${wrap}>`;
                }).join('');
                window.wireCertTilt?.(certsEl);
            }

            // --- Blog (latest 3) — 3D claymorphic cards ---
            const blogEl = qs('#notes-list');
            if (blogEl) {
                blogEl.innerHTML = posts.slice(0, 3).map((p, i) => {
                    const date = p.published_at ? new Date(p.published_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '';
                    const cover = p.cover_image
                        ? `<img src="${esc(p.cover_image)}" alt="${esc(p.title)}" loading="lazy">`
                        : `<span class="note-fallback-letter">${esc((p.title || '?').charAt(0))}</span>`;
                    const coverClass = p.cover_image ? 'note-cover has-image' : 'note-cover no-image';
                    return `
                        <a href="/blog/${encodeURIComponent(p.slug)}" class="note-card-3d reveal" data-tilt-note style="--i:${i}">
                            <div class="note-cover-wrap">
                                <div class="${coverClass}">${cover}</div>
                                <div class="note-sheen" aria-hidden="true"></div>
                                ${p.reading_minutes ? `<span class="note-read-badge">${p.reading_minutes} min read</span>` : ''}
                            </div>
                            <div class="note-body">
                                ${date ? `<time class="note-date">${date}</time>` : ''}
                                <h3 class="note-title">${esc(p.title)}</h3>
                                ${p.excerpt ? `<p class="note-excerpt">${esc(p.excerpt)}</p>` : ''}
                                <span class="note-cta">
                                    Read note
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17l10-10M7 7h10v10"/></svg>
                                </span>
                            </div>
                        </a>`;
                }).join('');

                // 3D mouse-tracking tilt on each note card
                const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (!prefersReduced) {
                    blogEl.querySelectorAll('[data-tilt-note]').forEach((card) => {
                        card.addEventListener('mousemove', (e) => {
                            const r = card.getBoundingClientRect();
                            const px = (e.clientX - r.left) / r.width  - 0.5;
                            const py = (e.clientY - r.top)  / r.height - 0.5;
                            card.style.setProperty('--ry', (px *  7).toFixed(2) + 'deg');
                            card.style.setProperty('--rx', (-py * 5).toFixed(2) + 'deg');
                        });
                        card.addEventListener('mouseleave', () => {
                            card.style.setProperty('--ry', '0deg');
                            card.style.setProperty('--rx', '0deg');
                        });
                    });
                }
            }

            // --- Clients logo strip ---
            renderClientsStrip(Array.isArray(clients) ? clients : []);

            // --- Rehydrate scroll-reveal + count-up for new nodes ---
            window.observeReveal();
            document.querySelectorAll('[data-count-to]').forEach((el) => {
                el.textContent = '0' + (el.dataset.countSuffix ?? '');
            });
        } catch (err) {
            console.error('hydrateHome failed', err);
        }
    }

    /* ============================================================ */
    /*   CLIENTS LOGO STRIP                                           */
    /* ============================================================ */
    function resolveLogoUrl(raw) {
        if (!raw) return '';
        return (raw.startsWith('http') || raw.startsWith('/'))
            ? raw
            : '/storage/' + raw.replace(/^\/+/, '');
    }
    function renderClientsStrip(clients) {
        const section = qs('#clients-strip');
        const track1  = qs('#clients-track-1');
        const track2  = qs('#clients-track-2');
        if (!section || !track1 || !track2) return;
        if (!clients.length) { section.hidden = true; return; }

        const tile = (c) => {
            const url = resolveLogoUrl(c.logo);
            const inner = `
                <span class="client-logo-inner">
                    <img src="${esc(url)}" alt="${esc(c.name)}" loading="lazy" decoding="async">
                </span>`;
            return c.website
                ? `<a class="client-logo" href="${esc(c.website)}" target="_blank" rel="noopener noreferrer" aria-label="${esc(c.name)}">${inner}</a>`
                : `<span class="client-logo" role="img" aria-label="${esc(c.name)}">${inner}</span>`;
        };

        // Row 1 — original order, scrolls left-to-right (translateX negative)
        const runA = clients.map(tile).join('');
        track1.innerHTML = runA + runA;

        // Row 2 — REVERSED order, scrolls in the opposite direction
        const reversed = clients.slice().reverse();
        const runB = reversed.map(tile).join('');
        track2.innerHTML = runB + runB;

        // Tune animation duration to the number of logos (~3.5s per logo, slightly
        // different per row so they never perfectly sync and the eye stays engaged)
        const base = Math.max(26, clients.length * 3.5);
        track1.style.setProperty('--clients-duration', base + 's');
        track2.style.setProperty('--clients-duration', (base + 5) + 's');
        section.hidden = false;
    }

    /* ============================================================ */
    /*   PROJECTS ARCHIVE                                             */
    /* ============================================================ */
    async function hydrateArchive() {
        const host = qs('#archive-grid');
        const skel = qs('#archive-skeleton');
        if (!host) return;
        try {
            const res = await API.projects({ per_page: 50 });
            const items = res.data ?? [];
            host.innerHTML = items.map(p => {
                const techChips = (p.tech_stack ?? []).slice(0, 5).map(t =>
                    `<span class="chip-sm">${esc(t)}</span>`
                ).join('');
                return `
                    <a href="/projects/${encodeURIComponent(p.slug)}" class="archive-card reveal">
                        <div class="archive-mockup bg-gradient ${gradientFor(p.slug)}">
                            ${p.cover_image
                                ? `<img src="${esc(p.cover_image)}" alt="${esc(p.title)}" class="absolute-cover">`
                                : `<div class="abs-flex-col p-6">
                                       <div class="flex items-center gap-2 text-white-60 mono-xs uppercase"><span class="dot-pulse"></span>${esc(p.category)}</div>
                                       <div class="mt-auto">
                                           <div class="heading-serif text-white mockup-title">${esc(p.title)}</div>
                                           <p class="mt-2 text-white-70 small max-w-md">${esc(p.tagline)}</p>
                                           <div class="mt-4 flex flex-wrap gap-1.5">${techChips}</div>
                                       </div>
                                   </div>`
                            }
                            <div class="archive-corner">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17l10-10M7 7h10v10"/></svg>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="tag-chip">${esc(p.category)}</span>
                                ${p.year ? `<span class="muted-2">· ${p.year}</span>` : ''}
                            </div>
                            <h3 class="mt-3 text-2xl md-text-3xl font-semibold card-title">${esc(p.title)}</h3>
                            <p class="mt-1 text-sm muted-2">${esc(p.tagline)}</p>
                        </div>
                    </a>`;
            }).join('');
            skel?.remove();
            window.observeReveal();
        } catch (err) {
            console.error('hydrateArchive failed', err);
            host.innerHTML = `<div class="empty">Couldn't load projects — try again later.</div>`;
            skel?.remove();
        }
    }

    /* ============================================================ */
    /*   PROJECT DETAIL                                               */
    /* ============================================================ */
    async function hydrateProject() {
        const slug = resolveSlug('projects');
        if (!slug) { window.location.href = '/projects'; return; }
        try {
            const res  = await API.project(slug);
            const p    = res.data ?? res;
            updateSeo({
                title:       `${p.title} · Case Study · Ahmed Abo Dooh`,
                description: (p.summary || p.tagline || `Case study of ${p.title} by Ahmed Abo Dooh.`).slice(0, 160),
                canonical:   `${location.origin}/projects/${p.slug}`,
                image:       p.cover_image || undefined,
            });
            qs('[data-field="title"]').textContent    = p.title;
            qs('[data-field="tagline"]').textContent  = p.tagline ?? '';
            qs('[data-field="category"]').textContent = p.category ?? '';
            qs('[data-field="year"]').textContent     = p.year ? '· ' + p.year : '';
            qs('[data-field="role"]').textContent     = p.role  ? '· ' + p.role : '';
            qs('[data-field="summary"]').textContent  = p.summary ?? '';
            const descEl = qs('[data-field="description"]');
            if (descEl) descEl.innerHTML = (p.description ?? '').split('\n').map(s => esc(s)).join('<br>');
            const live = qs('[data-field="live"]'); if (live) { if (p.live_url) { live.href = p.live_url; live.hidden = false; } else { live.hidden = true; } }
            const repo = qs('[data-field="repo"]'); if (repo) { if (p.repo_url) { repo.href = p.repo_url; repo.hidden = false; } else { repo.hidden = true; } }

            const hero = qs('#project-hero');
            if (hero) {
                hero.classList.add(...gradientFor(p.slug).split(' '));
                if (p.cover_image) hero.innerHTML = `<img src="${esc(p.cover_image)}" alt="${esc(p.title)}" class="absolute-cover">`;
                else hero.innerHTML = `<div class="abs-center"><span class="heading-serif text-white-25 project-hero-title">${esc(p.title)}</span></div>`;
            }

            const sidebar = qs('#project-meta');
            if (sidebar) {
                const rows = [];
                if (p.client) rows.push(`<div><dt>Client</dt><dd>${esc(p.client)}</dd></div>`);
                if (p.role)   rows.push(`<div><dt>Role</dt><dd>${esc(p.role)}</dd></div>`);
                if (p.year)   rows.push(`<div><dt>Year</dt><dd>${esc(p.year)}</dd></div>`);
                if ((p.tech_stack || []).length) {
                    rows.push(`<div><dt>Stack</dt><dd class="flex flex-wrap gap-2 mt-2">${p.tech_stack.map(t => `<span class="tag-chip">${esc(t)}</span>`).join('')}</dd></div>`);
                }
                sidebar.innerHTML = rows.join('');
            }

            const highlights = qs('#project-highlights');
            if (highlights && (p.highlights || []).length) {
                highlights.innerHTML = p.highlights.map(h => `<li class="flex gap-3 text-base muted"><span class="accent shrink-0 mt-1">▸</span><span>${esc(h)}</span></li>`).join('');
                qs('#project-highlights-wrap')?.removeAttribute('hidden');
            }

            // Gallery
            if ((p.gallery || []).length) {
                const gal = qs('#project-gallery');
                gal.innerHTML = p.gallery.map((src, i) => `
                    <button type="button" class="gallery-thumb" data-lb-index="${i}">
                        <img src="${esc(src)}" alt="${esc(p.title)} — ${i+1}" loading="lazy">
                    </button>
                `).join('');
                qs('#gallery-wrap')?.removeAttribute('hidden');
                gal.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-lb-index]');
                    if (btn) openLightbox(p.gallery, parseInt(btn.dataset.lbIndex));
                });
            }

            // Next project
            const allRes = await API.projects({ per_page: 50 });
            const all = allRes.data ?? [];
            const idx = all.findIndex(x => x.slug === slug);
            const next = all[idx + 1] ?? all[0];
            if (next && next.slug !== slug) {
                const nextEl = qs('#next-project');
                nextEl.innerHTML = `<a href="/projects/${encodeURIComponent(next.slug)}" class="glass next-card">
                    <p class="next-kicker">Next project</p>
                    <div class="flex flex-wrap items-end justify-between gap-6 mt-3">
                        <h3 class="text-5xl md-text-7xl font-semibold">${esc(next.title)}</h3>
                        <span class="arrow-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17l10-10M7 7h10v10"/></svg>
                        </span>
                    </div>
                </a>`;
            }

            window.observeReveal();
        } catch (err) {
            console.error('hydrateProject failed', err);
            document.body.innerHTML = '<div class="not-found"><h1>Project not found</h1><p><a href="/projects">Back to archive</a></p></div>';
        }
    }

    /* ============================================================ */
    /*   BLOG LIST                                                    */
    /* ============================================================ */
    async function hydrateBlog() {
        const host = qs('#blog-grid');
        const skel = qs('#blog-skeleton');
        if (!host) return;
        try {
            const res = await API.posts({ per_page: 50 });
            const items = res.data ?? [];
            host.innerHTML = items.map(p => {
                const date = p.published_at ? new Date(p.published_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '';
                const firstTag = (p.tags || [])[0];
                const initial = esc((p.title || '?').charAt(0).toUpperCase());
                const hasCover = !!p.cover_image;
                const cover = hasCover
                    ? `<img class="post-card__img" src="${esc(p.cover_image)}" alt="${esc(p.title)}" loading="lazy">
                       <div class="post-card__shine" aria-hidden="true"></div>`
                    : `<span class="post-card__mono" aria-hidden="true">${initial}</span>`;
                return `
                    <a href="/blog/${encodeURIComponent(p.slug)}" class="card-project post-card block reveal">
                        <div class="post-card__cover${hasCover ? '' : ' post-card__cover--fallback'}">
                            ${cover}
                            ${firstTag ? `<span class="post-card__tag">${esc(firstTag)}</span>` : ''}
                        </div>
                        <div class="card-inner post-card__body">
                            <div class="post-card__meta">
                                <time>${date}</time>
                                ${p.reading_minutes ? `<span aria-hidden="true">·</span><span>${p.reading_minutes} min read</span>` : ''}
                            </div>
                            <h2 class="card-title post-card__title">${esc(p.title)}</h2>
                            ${p.excerpt ? `<p class="post-card__excerpt muted">${esc(p.excerpt)}</p>` : ''}
                        </div>
                    </a>`;
            }).join('');
            skel?.remove();
            window.observeReveal();
        } catch (err) {
            console.error('hydrateBlog failed', err);
            host.innerHTML = '<div class="empty">Couldn\'t load posts.</div>';
            skel?.remove();
        }
    }

    /* ============================================================ */
    /*   POST DETAIL                                                  */
    /* ============================================================ */
    async function hydratePost() {
        const slug = resolveSlug('blog');
        if (!slug) { window.location.href = '/blog'; return; }
        try {
            const res = await API.post(slug);
            const p = res.data ?? res;
            updateSeo({
                title:       `${p.title} · Notes · Ahmed Abo Dooh`,
                description: (p.excerpt || `${p.title} — a note by Ahmed Abo Dooh on Laravel and PHP.`).slice(0, 160),
                canonical:   `${location.origin}/blog/${p.slug}`,
                image:       p.cover_image || undefined,
            });
            qs('[data-field="title"]').textContent = p.title;
            qs('[data-field="excerpt"]').textContent = p.excerpt ?? '';
            qs('[data-field="date"]').textContent = p.published_at ? new Date(p.published_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '';
            qs('[data-field="read"]').textContent = p.reading_minutes ? `· ${p.reading_minutes} min read` : '';
            const body = qs('#post-body');
            if (body) body.innerHTML = p.body_html ?? '';
            if ((p.tags || []).length) {
                const tagsEl = qs('#post-tags');
                tagsEl.innerHTML = p.tags.map(t => `<span class="tag-chip">${esc(t)}</span>`).join('');
                tagsEl.hidden = false;
            }
            const cov = qs('#post-cover');
            if (cov) {
                if (p.cover_image) {
                    cov.innerHTML = `
                        <div class="post-cover-stack">
                            <span class="post-cover-stack__slab" aria-hidden="true"></span>
                            <img src="${esc(p.cover_image)}" alt="${esc(p.title)}" class="post-cover-img">
                        </div>`;
                } else {
                    const initial = esc((p.title || '?').charAt(0).toUpperCase());
                    cov.innerHTML = `
                        <div class="post-cover-stack">
                            <span class="post-cover-stack__slab" aria-hidden="true"></span>
                            <div class="post-cover-img post-cover-fallback">
                                <span class="post-cover-mono" aria-hidden="true">${initial}</span>
                            </div>
                        </div>`;
                }
                cov.hidden = false;
            }

            // more posts
            const allRes = await API.posts({ per_page: 50 });
            const all = (allRes.data ?? []).filter(x => x.slug !== slug).slice(0, 3);
            const more = qs('#more-posts');
            if (more && all.length) {
                more.innerHTML = all.map(m => `
                    <a href="/blog/${encodeURIComponent(m.slug)}" class="card-project block">
                        <div class="card-inner">
                            <p class="text-xs uppercase widest muted-2">${m.published_at ? new Date(m.published_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : ''}</p>
                            <h4 class="mt-3 text-xl font-semibold card-title">${esc(m.title)}</h4>
                        </div>
                    </a>
                `).join('');
                qs('#more-posts-wrap').hidden = false;
            }
            window.observeReveal();
        } catch (err) {
            console.error('hydratePost failed', err);
            document.body.innerHTML = '<div class="not-found"><h1>Post not found</h1><p><a href="/blog">Back to notes</a></p></div>';
        }
    }

    /* ============================================================ */
    /*   LIGHTBOX                                                     */
    /* ============================================================ */
    let lbImages = [], lbIndex = 0;
    function openLightbox(images, start = 0) {
        lbImages = images; lbIndex = start;
        let lb = document.getElementById('lightbox');
        if (!lb) {
            lb = document.createElement('div');
            lb.id = 'lightbox';
            lb.className = 'lightbox';
            lb.innerHTML = `
                <button class="lightbox-close" data-lb-close aria-label="Close">×</button>
                <button class="lightbox-btn left" data-lb-prev>‹</button>
                <img alt="Preview">
                <button class="lightbox-btn right" data-lb-next>›</button>
            `;
            document.body.appendChild(lb);
            lb.addEventListener('click', (e) => {
                if (e.target === lb) closeLightbox();
                if (e.target.closest('[data-lb-close]')) closeLightbox();
                if (e.target.closest('[data-lb-next]')) { lbIndex = (lbIndex + 1) % lbImages.length; renderLb(); }
                if (e.target.closest('[data-lb-prev]')) { lbIndex = (lbIndex - 1 + lbImages.length) % lbImages.length; renderLb(); }
            });
            document.addEventListener('keydown', (e) => {
                if (!lb.classList.contains('is-open')) return;
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowRight') { lbIndex = (lbIndex + 1) % lbImages.length; renderLb(); }
                if (e.key === 'ArrowLeft')  { lbIndex = (lbIndex - 1 + lbImages.length) % lbImages.length; renderLb(); }
            });
        }
        renderLb();
        lb.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function renderLb() {
        const lb = document.getElementById('lightbox');
        if (lb) lb.querySelector('img').src = lbImages[lbIndex];
    }
    function closeLightbox() {
        document.getElementById('lightbox')?.classList.remove('is-open');
        document.body.style.overflow = '';
    }
    window.openLightbox = openLightbox;

    /* ============================================================ */
    /*   CONTACT FORM                                                 */
    /* ============================================================ */
    function wireContactForm() {
        const form = document.getElementById('contact-form');
        if (!form) return;
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(form));
            const ok = document.getElementById('contact-ok');
            const err = document.getElementById('contact-err');
            ok.hidden = true; err.hidden = true; err.textContent = '';
            try {
                await API.contact(data);
                ok.hidden = false;
                form.reset();
            } catch (ex) {
                err.textContent = ex.message || 'Something went wrong.';
                err.hidden = false;
            }
        });
    }

    /* ============================================================ */
    /*   ROUTER (by data-page on <body>)                              */
    /* ============================================================ */
    document.addEventListener('DOMContentLoaded', () => {
        const page = document.body.dataset.page;
        window.renderSidebar?.(page);
        if (page === 'home') { hydrateHome(); wireContactForm(); }
        if (page === 'projects') hydrateArchive();
        if (page === 'project')  hydrateProject();
        if (page === 'blog')     hydrateBlog();
        if (page === 'post')     hydratePost();
    });
})();
