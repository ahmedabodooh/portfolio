/**
 * Admin shell — injects sidebar + topbar on every page,
 * gates access behind a valid Sanctum token, and wires logout.
 *
 * Usage in page HTML: <body data-admin-page="projects" data-title="Projects">
 *                     <main id="page" class="admin-main"> ... </main>
 *
 * shell.js wraps the body with .admin-shell and decides whether to render
 * the sidebar/topbar based on data-admin-page (login → no shell).
 */
(function () {
    // Favicon — injected here so every admin page gets it without editing each HTML file.
    (function injectFavicon() {
        if (document.querySelector('link[rel="icon"]')) return;
        const link = document.createElement('link');
        link.rel  = 'icon';
        link.type = 'image/svg+xml';
        link.href = '/assets/images/favicon.svg';
        document.head.appendChild(link);
    })();

    const STANDALONE_PAGES = new Set(['login']);

    const NAV = [
        { key: 'dashboard',      label: 'Dashboard',      href: '/admin',                 icon: iconDashboard() },
        { key: 'projects',       label: 'Projects',       href: '/admin/projects',        icon: iconFolder() },
        { key: 'blog',           label: 'Blog',           href: '/admin/blog',            icon: iconPen() },
        { key: 'skills',         label: 'Skills',         href: '/admin/skills',          icon: iconSpark() },
        { key: 'experiences',    label: 'Experiences',    href: '/admin/experiences',     icon: iconBriefcase() },
        { key: 'certifications', label: 'Certifications', href: '/admin/certifications',  icon: iconAward() },
        { key: 'clients',        label: 'Clients',        href: '/admin/clients',         icon: iconUsers() },
        { key: 'messages',       label: 'Messages',       href: '/admin/messages',        icon: iconMail() },
        { key: 'settings',       label: 'Settings',       href: '/admin/settings',        icon: iconSettings() },
    ];

    document.addEventListener('DOMContentLoaded', () => init());

    async function init() {
        const body = document.body;
        const page = body.dataset.adminPage || 'dashboard';
        const title = body.dataset.title || 'Admin';

        if (STANDALONE_PAGES.has(page)) {
            document.title = title + ' · Admin';
            return;
        }

        // Gate: if no token, bounce to login.
        if (!AdminAPI.token()) {
            location.href = '/admin/login';
            return;
        }

        // Try to load /auth/me — if it fails (expired), bounce.
        let user;
        try {
            user = await AdminAPI.auth.me();
        } catch (e) {
            location.href = '/admin/login';
            return;
        }

        buildShell(page, title, user);
        document.title = title + ' · Admin';
    }

    function buildShell(activePage, pageTitle, user) {
        const body = document.body;

        // Move existing main content into a container
        const existingMain = document.getElementById('page');
        if (!existingMain) {
            console.warn('shell.js: expected <main id="page"> inside body');
            return;
        }
        existingMain.classList.add('admin-main');

        const sidebar = document.createElement('aside');
        sidebar.className = 'admin-sidebar';
        sidebar.innerHTML = `
            <div class="brand">
                <div class="logo">A</div>
                <div>
                    <div class="brand-name">Ahmed Admin</div>
                    <div class="brand-sub">Portfolio</div>
                </div>
            </div>
            <nav>
                ${NAV.map(n => `
                    <a href="${n.href}" class="${n.key === activePage ? 'active' : ''}" data-nav="${n.key}">
                        <span class="icon">${n.icon}</span>
                        <span>${n.label}</span>
                    </a>
                `).join('')}
            </nav>
            <div class="sidebar-footer">
                <div>© ${new Date().getFullYear()} Ahmed Abo Dooh</div>
                <div style="margin-top:.25rem;">API v1</div>
            </div>
        `;

        const topbar = document.createElement('header');
        topbar.className = 'admin-topbar';
        topbar.innerHTML = `
            <div style="display:flex; align-items:center; gap:.75rem;">
                <button type="button" class="hamburger" aria-label="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6"  x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="page-title">${pageTitle}</div>
            </div>
            <div class="user-menu">
                <div class="avatar">${(user.name || '?').slice(0,1).toUpperCase()}</div>
                <div style="line-height:1.1;">
                    <div style="font-weight:600;">${escapeHtml(user.name)}</div>
                    <div style="font-size:.72rem; color: var(--text-mute);">${escapeHtml(user.email)}</div>
                </div>
                <button class="btn btn-ghost btn-sm" id="admin-logout">Logout</button>
            </div>
        `;

        const shell = document.createElement('div');
        shell.className = 'admin-shell';
        body.insertBefore(shell, body.firstChild);

        shell.appendChild(sidebar);
        shell.appendChild(topbar);
        shell.appendChild(existingMain);

        // Hamburger toggle
        topbar.querySelector('.hamburger').addEventListener('click', () => {
            sidebar.classList.toggle('is-open');
        });

        // Logout
        topbar.querySelector('#admin-logout').addEventListener('click', async () => {
            try { await AdminAPI.auth.logout(); } catch (e) {}
            AdminAPI.clearToken();
            location.href = '/admin/login';
        });

        // Toast host
        if (!document.querySelector('.toast-host')) {
            const th = document.createElement('div');
            th.className = 'toast-host';
            document.body.appendChild(th);
        }
    }

    /* ---- Icons (inline SVG) ---- */
    function iconDashboard() { return svg('<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>'); }
    function iconFolder()    { return svg('<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>'); }
    function iconPen()       { return svg('<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>'); }
    function iconSpark()     { return svg('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'); }
    function iconBriefcase() { return svg('<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>'); }
    function iconAward()     { return svg('<circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>'); }
    function iconMail()      { return svg('<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>'); }
    function iconUsers()     { return svg('<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'); }
    function iconSettings()  { return svg('<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>'); }

    function svg(body) {
        return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${body}</svg>`;
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' })[c]);
    }
    window.escapeHtml = escapeHtml;
})();
