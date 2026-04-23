/**
 * Public API client — talks to Laravel backend at /api/v1/*.
 * Same-origin, so relative URLs work. Override API_BASE to point at
 * a different host if the frontend is deployed separately.
 */
(function () {
    const API_BASE = window.API_BASE || '/api/v1';

    async function request(path, { method = 'GET', body, headers } = {}) {
        const url = path.startsWith('http') ? path : API_BASE + path;
        const res = await fetch(url, {
            method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                ...(headers || {}),
            },
            body: body ? JSON.stringify(body) : undefined,
        });
        if (!res.ok) {
            let data = {};
            try { data = await res.json(); } catch (e) {}
            const err = new Error(data?.message || `API ${res.status}: ${res.statusText}`);
            err.status = res.status;
            err.validation = data?.errors;
            throw err;
        }
        if (res.status === 204) return null;
        return res.json();
    }

    const API = {
        // --- Site identity ---
        profile:        () => request('/site/profile'),
        branding:       () => request('/site/branding'),
        experiences:    () => request('/site/experiences'),
        skills:         () => request('/site/skills'),
        certifications: () => request('/site/certifications'),
        clients:        () => request('/site/clients'),

        // --- Projects ---
        projects: ({ featured, category, per_page = 50 } = {}) => {
            const q = new URLSearchParams();
            if (featured !== undefined) q.set('featured', String(featured));
            if (category) q.set('category', category);
            q.set('per_page', String(per_page));
            return request('/projects?' + q.toString());
        },
        project: (slug) => request(`/projects/${encodeURIComponent(slug)}?expand=1`),

        // --- Blog ---
        posts: ({ tag, per_page = 20 } = {}) => {
            const q = new URLSearchParams();
            if (tag) q.set('tag', tag);
            q.set('per_page', String(per_page));
            return request('/blog?' + q.toString());
        },
        post: (slug) => request(`/blog/${encodeURIComponent(slug)}?expand=1`),

        // --- Contact ---
        contact: (payload) => request('/contact', { method: 'POST', body: payload }),
    };

    window.API = API;
})();
