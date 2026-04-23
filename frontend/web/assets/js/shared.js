/**
 * Shared UI — theme, typewriter, scroll reveal, smooth scroll,
 * parallax, count-up, magnetic, tilt, top nav, CV modal.
 */
(function () {
    /* ---------- Theme (light default) ---------- */
    const THEME_KEY = 'ahmed-theme';
    (function initTheme() {
        const saved = localStorage.getItem(THEME_KEY) ?? 'light';
        document.documentElement.classList.toggle('dark', saved === 'dark');
    })();
    window.toggleTheme = function () {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');
    };

    /* ---------- Horizontal gallery navigation ---------- */
    window.wireGalleryNav = function (trackSel = '#projects-track', prevSel = '[data-gallery-prev]', nextSel = '[data-gallery-next]') {
        const track = document.querySelector(trackSel);
        const prev  = document.querySelector(prevSel);
        const next  = document.querySelector(nextSel);
        if (!track || !prev || !next) return;

        const step = () => {
            const card = track.querySelector(':scope > *');
            return card ? card.getBoundingClientRect().width + 24 : 400;
        };
        prev.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
        next.addEventListener('click', () => track.scrollBy({ left:  step(), behavior: 'smooth' }));

        // 3D tilt per card based on mouse position
        track.querySelectorAll('.project-card-3d').forEach((card) => {
            card.addEventListener('mousemove', (e) => {
                const r = card.getBoundingClientRect();
                const px = (e.clientX - r.left) / r.width  - 0.5;
                const py = (e.clientY - r.top)  / r.height - 0.5;
                card.style.transform = `perspective(1400px) rotateY(${px * 10}deg) rotateX(${-py * 8}deg) translateZ(20px) scale(1.02)`;
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });

        // Scroll-linked rotation (cards off-center get slight Y rotation)
        const onScroll = () => {
            const center = track.scrollLeft + track.clientWidth / 2;
            track.querySelectorAll('.project-card-3d').forEach((card) => {
                if (card.matches(':hover')) return;
                const cardCenter = card.offsetLeft + card.offsetWidth / 2;
                const delta = (cardCenter - center) / track.clientWidth; // -1..1
                const clamp = Math.max(-0.5, Math.min(0.5, delta));
                card.style.transform = `perspective(1400px) rotateY(${-clamp * 12}deg) translateZ(0)`;
            });
        };
        track.addEventListener('scroll', onScroll, { passive: true });
        requestAnimationFrame(onScroll);
    };

    /* ---------- Scroll reveal ---------- */
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                e.target.classList.add('is-visible');
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.05, rootMargin: '0px 0px -8% 0px' });

    window.observeReveal = function (root = document) {
        root.querySelectorAll('.reveal').forEach((el) => io.observe(el));
    };

    /* ---------- 3D tilt on certificate cards (mouse-follow) ---------- */
    window.wireCertTilt = function (root) {
        const rm = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (!root || rm.matches) return;
        root.querySelectorAll('[data-cert-tilt]').forEach((card) => {
            let raf = 0;
            card.addEventListener('mousemove', (ev) => {
                const r = card.getBoundingClientRect();
                const px = (ev.clientX - r.left) / r.width;
                const py = (ev.clientY - r.top)  / r.height;
                const rx = (py - 0.5) * -10;
                const ry = (px - 0.5) *  12;
                if (raf) cancelAnimationFrame(raf);
                raf = requestAnimationFrame(() => {
                    card.style.setProperty('--cert-rx', rx.toFixed(2) + 'deg');
                    card.style.setProperty('--cert-ry', ry.toFixed(2) + 'deg');
                    card.style.setProperty('--cert-mx', (px * 100).toFixed(1) + '%');
                    card.style.setProperty('--cert-my', (py * 100).toFixed(1) + '%');
                });
            });
            card.addEventListener('mouseleave', () => {
                if (raf) cancelAnimationFrame(raf);
                card.style.setProperty('--cert-rx', '0deg');
                card.style.setProperty('--cert-ry', '0deg');
            });
        });
    };

    /* ---------- 3D tilt on experience cards (mouse-follow) ---------- */
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    window.wireExpTilt = function (root) {
        if (!root || reducedMotion.matches) return;
        const cards = root.querySelectorAll('[data-tilt]');
        cards.forEach((card) => {
            let raf = 0;
            const onMove = (ev) => {
                const rect = card.getBoundingClientRect();
                const px = (ev.clientX - rect.left) / rect.width;   // 0..1
                const py = (ev.clientY - rect.top) / rect.height;   // 0..1
                const rx = (py - 0.5) * -8;   // tilt X (degrees)
                const ry = (px - 0.5) *  10;  // tilt Y
                if (raf) cancelAnimationFrame(raf);
                raf = requestAnimationFrame(() => {
                    card.style.setProperty('--tilt-rx', rx.toFixed(2) + 'deg');
                    card.style.setProperty('--tilt-ry', ry.toFixed(2) + 'deg');
                    card.style.setProperty('--mx', (px * 100).toFixed(1) + '%');
                    card.style.setProperty('--my', (py * 100).toFixed(1) + '%');
                });
            };
            const onLeave = () => {
                if (raf) cancelAnimationFrame(raf);
                card.style.setProperty('--tilt-rx', '0deg');
                card.style.setProperty('--tilt-ry', '0deg');
            };
            card.addEventListener('mousemove', onMove);
            card.addEventListener('mouseleave', onLeave);
        });
    };

    /* ---------- Typewriter ---------- */
    window.typewriter = function (el) {
        const text = el.dataset.type ?? el.textContent;
        const speed = parseFloat(el.dataset.typeSpeed ?? 35);
        el.textContent = '';
        let i = 0;
        const tick = () => {
            el.textContent = text.slice(0, i);
            if (++i <= text.length) setTimeout(tick, speed);
            else el.classList.add('is-done');
        };
        tick();
    };

    /* ---------- Count-up ---------- */
    function initCountUp(root = document) {
        root.querySelectorAll('[data-count-to]').forEach((el) => {
            const countIo = new IntersectionObserver((entries) => {
                entries.forEach((e) => {
                    if (!e.isIntersecting) return;
                    const target = parseFloat(el.dataset.countTo);
                    const suffix = el.dataset.countSuffix ?? '';
                    const duration = 1400;
                    const start = performance.now();
                    const step = (now) => {
                        const t = Math.min(1, (now - start) / duration);
                        const eased = 1 - Math.pow(1 - t, 3);
                        el.textContent = Math.floor(target * eased) + suffix;
                        if (t < 1) requestAnimationFrame(step);
                        else el.textContent = target + suffix;
                    };
                    requestAnimationFrame(step);
                    countIo.unobserve(el);
                });
            }, { threshold: 0.4 });
            countIo.observe(el);
        });
    }

    /* ---------- Magnetic / tilt ---------- */
    function initMagnetic(root = document) {
        root.querySelectorAll('.magnetic').forEach((el) => {
            const strength = parseFloat(el.dataset.magnet ?? '0.2');
            el.addEventListener('mousemove', (e) => {
                const r = el.getBoundingClientRect();
                const dx = (e.clientX - (r.left + r.width / 2)) * strength;
                const dy = (e.clientY - (r.top + r.height / 2)) * strength;
                el.style.transform = `translate(${dx}px, ${dy}px)`;
            });
            el.addEventListener('mouseleave', () => { el.style.transform = ''; });
        });
    }
    function initTilt(root = document) {
        root.querySelectorAll('[data-tilt]').forEach((el) => {
            const strength = parseFloat(el.dataset.tilt ?? '6');
            el.addEventListener('mousemove', (e) => {
                const r = el.getBoundingClientRect();
                const px = (e.clientX - r.left) / r.width - 0.5;
                const py = (e.clientY - r.top)  / r.height - 0.5;
                el.style.transform = `perspective(1000px) rotateY(${px * strength}deg) rotateX(${-py * strength}deg)`;
            });
            el.addEventListener('mouseleave', () => {
                el.style.transform = 'perspective(1000px) rotateY(0) rotateX(0)';
            });
        });
    }

    /* ---------- Smooth-scroll anchor clicks (native behavior, no hijack) ---------- */
    // Native CSS scroll-behavior: smooth handles most cases cleanly for a cream/editorial site.
    document.documentElement.style.scrollBehavior = 'smooth';

    /* ---------- Scroll-progress bar ---------- */
    function initScrollProgress() {
        const bar = document.getElementById('scroll-progress');
        if (!bar) return;
        const update = () => {
            const h = document.documentElement;
            const p = h.scrollTop / Math.max(1, h.scrollHeight - h.clientHeight);
            bar.style.transform = `scaleX(${p})`;
        };
        window.addEventListener('scroll', update, { passive: true });
        update();
    }

    /* ---------- Background music ---------- */
    // window.initMusic(url) — called from main.js once profile data loads.
    // Falsy url → keep the toggle button hidden (no music configured).
    // Policy: every fresh page load attempts to play. Pressing pause only
    // stops it for this session; re-opening / refreshing starts it again.
    const MUSIC_URL_CACHE = 'ahmed-music-url';
    // Hydrate from localStorage so repeat visitors can autoplay the instant
    // shared.js parses — no need to wait on the /site/profile API call.
    let currentMusicUrl = localStorage.getItem(MUSIC_URL_CACHE) || null;
    // Clean up the legacy persistent-mute preference from earlier builds.
    localStorage.removeItem('ahmed-music-muted');
    // Tracks the user's manual pause within this tab/session only.
    let userPausedThisSession = false;
    window.initMusic = function (url) {
        if (url !== undefined) {
            currentMusicUrl = url || null;
            if (url) localStorage.setItem(MUSIC_URL_CACHE, url);
            else     localStorage.removeItem(MUSIC_URL_CACHE);
        }
        const toggle = document.querySelector('[data-music-toggle]');
        if (!toggle) return;
        if (!currentMusicUrl) {
            toggle.hidden = true;
            return;
        }
        url = currentMusicUrl;

        // Lazy-create the shared <audio> element once per page lifetime.
        let audio = document.getElementById('bg-music');
        if (!audio) {
            audio = document.createElement('audio');
            audio.id = 'bg-music';
            audio.loop = true;
            audio.preload = 'auto';
            audio.volume = 0.35;
            document.body.appendChild(audio);
        }
        if (audio.src !== url) audio.src = url;

        toggle.hidden = false;
        const syncButton = () => {
            const playing = !audio.paused && !audio.ended;
            toggle.classList.toggle('is-playing', playing);
            toggle.setAttribute('aria-pressed', playing ? 'true' : 'false');
            toggle.title = playing ? 'Pause music' : 'Play music';
        };

        const tryPlay = () => audio.play().then(syncButton).catch(() => syncButton());
        if (!userPausedThisSession) {
            // Try to autoplay — most browsers block this without user interaction.
            tryPlay();
            // Fallback: resume on the first meaningful user gesture that is NOT
            // the music toggle itself (the toggle has its own click handler).
            const KICK_EVENTS = ['click', 'keydown', 'touchstart', 'pointerdown', 'scroll'];
            const removeKick = () => KICK_EVENTS.forEach((ev) =>
                document.removeEventListener(ev, kick, true)
            );
            const kick = (e) => {
                // Ignore events that originate from the music toggle — otherwise
                // the first tap there would race with the toggle's own handler
                // (capture fires first and sets audio playing; then bubble
                // handler flips it back to paused, canceling the user out).
                if (e && e.target && e.target.closest && e.target.closest('[data-music-toggle]')) return;
                removeKick();
                if (audio.paused && !userPausedThisSession) tryPlay();
            };
            KICK_EVENTS.forEach((ev) =>
                document.addEventListener(ev, kick, { capture: true, passive: true })
            );
        }
        syncButton();

        if (toggle.dataset.wired) return;
        toggle.dataset.wired = '1';
        toggle.addEventListener('click', () => {
            if (audio.paused) {
                userPausedThisSession = false;
                tryPlay();
            } else {
                userPausedThisSession = true;
                audio.pause();
                syncButton();
            }
        });
        audio.addEventListener('play',  syncButton);
        audio.addEventListener('pause', syncButton);
    };

    /* ---------- Boot screen ---------- */
    function initBootScreen() {
        const s = document.getElementById('boot-screen');
        if (!s) return;
        document.body.style.overflow = 'hidden';
        const hide = () => {
            s.classList.add('is-gone');
            document.body.style.overflow = '';
            setTimeout(() => s.remove(), 700);
        };
        const minShow = 700;
        const start = performance.now();
        const finish = () => setTimeout(hide, Math.max(0, minShow - (performance.now() - start)));
        if (document.readyState === 'complete') finish();
        else window.addEventListener('load', finish, { once: true });
        setTimeout(hide, 4000);
    }

    /* ============================================================
       SHARED FOOTER
       ============================================================ */
    window.renderFooter = function () {
        const host = document.getElementById('footer-host');
        if (!host) return;
        host.innerHTML = `
            <footer class="site-footer">
                <div class="container-wide relative">
                    <div class="footer-grid">
                        <div>
                            <p class="text-xs uppercase widest accent" style="font-weight:600;">Let's build something</p>
                            <h3 class="display-italic" style="font-size: 1.9rem; line-height: 1.05; margin-top: .65rem;">Have a project in mind?</h3>
                            <a href="mailto:zalfyhima@gmail.com" data-identity="email" class="font-semibold" style="display:inline-flex; align-items:center; gap:.5rem; margin-top: .8rem; font-size: 1rem;">zalfyhima@gmail.com</a>
                        </div>
                        <div class="footer-cols">
                            <div>
                                <p class="text-xs uppercase widest" style="color: rgba(245,239,230,0.45); font-weight:600;">Navigate</p>
                                <ul style="margin-top: .65rem; list-style: none; padding: 0; display: grid; gap: .35rem;">
                                    <li><a href="/#work" class="link-underline">Selected Work</a></li>
                                    <li><a href="/projects" class="link-underline">Archive</a></li>
                                    <li><a href="/blog" class="link-underline">Notes</a></li>
                                    <li><a href="/#contact" class="link-underline">Contact</a></li>
                                </ul>
                            </div>
                            <div>
                                <p class="text-xs uppercase widest" style="color: rgba(245,239,230,0.45); font-weight:600;">Elsewhere</p>
                                <ul style="margin-top: .65rem; list-style: none; padding: 0; display: grid; gap: .35rem;">
                                    <li><a href="https://github.com/ahmedabodooh" target="_blank" rel="noopener noreferrer" class="link-underline">GitHub ↗</a></li>
                                    <li><a href="https://www.linkedin.com/in/ahmed-abo-dooh-2767a6299" target="_blank" rel="noopener noreferrer" class="link-underline">LinkedIn ↗</a></li>
                                    <li><button type="button" data-cv-open class="link-underline" style="background:none; border:0; color:inherit; cursor:pointer; font:inherit; padding:0;">Résumé</button></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="footer-bottom">
                        <p>© <span id="current-year">${new Date().getFullYear()}</span> Ahmed Abo Dooh · built on Laravel + vanilla JS.</p>
                        <p data-identity="location-2"></p>
                    </div>
                </div>
            </footer>
        `;
    };

    /* ============================================================
       SHARED IDENTITY HYDRATION
       Runs on every page — hydrates [data-identity="*"] elements
       from the public profile API so footer + nav stay consistent.
       ============================================================ */
    window.hydrateIdentity = async function () {
        if (!window.API?.profile) return;
        try {
            const profile = await window.API.profile();
            if (!profile) return;
            document.querySelectorAll('[data-identity="email"]').forEach((el) => {
                if (profile.owner_email) {
                    if (el.tagName === 'A') el.href = 'mailto:' + profile.owner_email;
                    if (el.tagName === 'SPAN' || el.tagName === 'A') el.textContent = profile.owner_email;
                }
            });
            document.querySelectorAll('[data-identity="location-2"]').forEach((el) => {
                if (profile.owner_location) el.textContent = profile.owner_location;
            });
        } catch (_) { /* silent — markup already has sane defaults */ }
    };

    /* ============================================================
       TOP NAV
       ============================================================ */
    window.renderNav = function (activeKey = 'home', ownerName = 'Ahmed Abo Dooh', status = '') {
        const host = document.getElementById('nav-host');
        if (!host) return;

        // Map data-page values to nav item keys so detail pages still
        // highlight their parent section (e.g. /post → 'blog').
        const PAGE_TO_NAV = {
            home: 'home',
            projects: 'archive',
            project: 'archive',
            blog: 'blog',
            post: 'blog',
        };
        activeKey = PAGE_TO_NAV[activeKey] ?? activeKey;

        const firstName = (ownerName || 'Ahmed').split(' ')[0];

        // Items split around the centered logo, JCREA-style
        const leftItems = [
            { key: 'home',     label: 'Home',     href: '/'                    },
            { key: 'projects', label: 'Projects', href: '/#work'               },
            { key: 'archive',  label: 'Archive',  href: '/projects'            },
        ];
        const rightItems = [
            { key: 'experience', label: 'Experience', href: '/#experience'       },
            { key: 'blog',       label: 'Notes',      href: '/blog'              },
            { key: 'contact',    label: 'Contact',    href: '/#contact'          },
        ];
        const allItems = [...leftItems, ...rightItems];

        host.innerHTML = `
            <header class="topnav" id="topnav">
                <div class="topnav-inner">
                    <!-- Left links -->
                    <nav class="topnav-links" aria-label="Primary left">
                        ${leftItems.map(it => `
                            <a href="${it.href}" class="topnav-link ${it.key === activeKey ? 'is-active' : ''}">${it.label}</a>
                        `).join('')}
                    </nav>

                    <!-- Centered logo (always visible) -->
                    <a href="/" class="topnav-center" aria-label="Home">
                        <span class="topnav-center-logo">a</span>
                    </a>

                    <!-- Right links -->
                    <nav class="topnav-links" aria-label="Primary right">
                        ${rightItems.map(it => `
                            <a href="${it.href}" class="topnav-link ${it.key === activeKey ? 'is-active' : ''}">${it.label}</a>
                        `).join('')}
                    </nav>

                    <!-- Action buttons — CV + theme + music grouped together -->
                    <div class="topnav-actions" data-desk-actions>
                        <button type="button" class="topnav-iconbtn" data-cv-open title="View résumé" aria-label="View résumé">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h5"/></svg>
                        </button>
                        <button type="button" class="topnav-iconbtn" onclick="toggleTheme()" title="Toggle theme" aria-label="Toggle theme">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v2M12 19v2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M3 12h2M19 12h2M5.6 18.4l1.4-1.4M17 7l1.4-1.4"/><circle cx="12" cy="12" r="4"/></svg>
                        </button>
                        <!-- Music toggle — sits next to CV/theme icons. Shown by initMusic when src is set. -->
                        <button type="button" class="topnav-music" data-music-toggle title="Toggle music" aria-label="Toggle music" hidden>
                            <span class="music-bars" aria-hidden="true"><span></span><span></span><span></span></span>
                            <svg class="music-icon music-icon--play" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                        </button>
                    </div>

                    <button type="button" class="topnav-burger" data-nav-burger aria-label="Menu">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:1.125rem;height:1.125rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h10"/></svg>
                    </button>
                </div>

                <div class="topnav-sheet" id="topnav-sheet">
                    ${allItems.map(it => `<a href="${it.href}" class="${it.key === activeKey ? 'is-active' : ''}">${it.label}</a>`).join('')}
                    <button type="button" data-cv-open>Résumé</button>
                </div>
            </header>
        `;

        // Inject helper CSS for mobile-only brand visibility
        if (!document.getElementById('topnav-helper-css')) {
            const st = document.createElement('style');
            st.id = 'topnav-helper-css';
            st.textContent = `
                @media (min-width: 900px) { [data-mobile-only] { display: none !important; } }
                @media (max-width: 899px) {
                    .topnav-links { display: none !important; }
                    /* Action icons (CV + theme) stay visible on mobile next to music + burger. */
                    [data-desk-actions] { display: flex !important; gap: .15rem; }
                }
            `;
            document.head.appendChild(st);
        }

        // Burger toggle
        host.querySelector('[data-nav-burger]')?.addEventListener('click', () => {
            host.querySelector('#topnav-sheet')?.classList.toggle('is-open');
        });

        // CV opener — anywhere in the nav / sheet
        host.querySelectorAll('[data-cv-open]').forEach((btn) => {
            btn.addEventListener('click', () => window.openCV());
        });

        // Re-wire the music toggle — nav HTML was replaced, so the button
        // that was previously unhidden is a fresh DOM node. No-arg call uses
        // the URL the caller last supplied.
        window.initMusic?.();
    };

    /* ============================================================
       CV MODAL — in-page PDF viewer
       ============================================================ */
    function injectCVModal() {
        if (document.getElementById('cv-modal')) return;
        const modal = document.createElement('div');
        modal.id = 'cv-modal';
        modal.className = 'cv-modal';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-label', 'Résumé');
        modal.innerHTML = `
            <div class="cv-modal-inner">
                <div class="cv-modal-bar">
                    <div>
                        <div class="cv-modal-title">Résumé</div>
                        <div class="cv-modal-sub">Ahmed Abo Dooh · PDF</div>
                    </div>
                    <div class="cv-modal-actions">
                        <a id="cv-open-link" href="#" target="_blank" rel="noopener" title="Open in new tab" aria-label="Open in new tab" hidden>
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:.875rem;height:.875rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            Open
                        </a>
                        <a id="cv-download-link" href="#" class="primary" download title="Download PDF" hidden>
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:.875rem;height:.875rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                            Download
                        </a>
                        <button type="button" data-cv-close aria-label="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:.875rem;height:.875rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            Close
                        </button>
                    </div>
                </div>
                <div class="cv-modal-body" id="cv-modal-body"></div>
            </div>
        `;
        document.body.appendChild(modal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) window.closeCV();
            if (e.target.closest('[data-cv-close]')) window.closeCV();
        });
        document.addEventListener('keydown', (e) => {
            if (modal.classList.contains('is-open') && e.key === 'Escape') window.closeCV();
        });
    }

    window.openCV = async function () {
        injectCVModal();
        const modal = document.getElementById('cv-modal');
        const body  = document.getElementById('cv-modal-body');
        const openLink     = document.getElementById('cv-open-link');
        const downloadLink = document.getElementById('cv-download-link');

        let resumeUrl = null;
        try {
            const profile = await (window.API?.profile?.() || Promise.resolve({}));
            resumeUrl = profile?.resume_file || null;
        } catch (e) {}

        if (resumeUrl) {
            openLink.href = resumeUrl;      openLink.hidden = false;
            downloadLink.href = resumeUrl;  downloadLink.hidden = false;
            body.innerHTML = `<object data="${resumeUrl}#toolbar=0&navpanes=0" type="application/pdf">
                <iframe src="${resumeUrl}" title="Résumé"></iframe>
            </object>`;
        } else {
            openLink.hidden = true; downloadLink.hidden = true;
            body.innerHTML = `
                <div class="cv-missing">
                    <h3>Résumé not uploaded yet</h3>
                    <p>Upload the PDF in the admin panel:<br>
                    <strong>Admin → Site Settings → <code>resume_file</code></strong></p>
                </div>`;
        }

        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };
    window.closeCV = function () {
        document.getElementById('cv-modal')?.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    /* ============================================================
       Bootstrap on DOM ready
       ============================================================ */
    document.addEventListener('DOMContentLoaded', () => {
        initBootScreen();
        observeReveal();
        initCountUp();
        initMagnetic();
        initTilt();
        initScrollProgress();
        document.querySelectorAll('[data-type]').forEach((el) => setTimeout(() => window.typewriter(el), 300));

        const y = document.getElementById('current-year');
        if (y) y.textContent = new Date().getFullYear();

        // Initial nav + footer render (main.js may re-render nav with owner name from API)
        window.renderNav?.(document.body.dataset.page);
        window.renderFooter?.();
        window.hydrateIdentity?.();

        // Global CV opener — any [data-cv-open] anywhere on the page (hero, footer, nav, …)
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-cv-open]');
            if (trigger) {
                e.preventDefault();
                window.openCV?.();
            }
        });

        // safety net for reveal — only flip elements that are already in the viewport,
        // so below-the-fold cards keep their scroll-triggered animation.
        setTimeout(() => {
            const vh = window.innerHeight || document.documentElement.clientHeight;
            document.querySelectorAll('.reveal').forEach((el) => {
                const r = el.getBoundingClientRect();
                if (r.top < vh && r.bottom > 0) el.classList.add('is-visible');
            });
        }, 1400);
    });
})();
