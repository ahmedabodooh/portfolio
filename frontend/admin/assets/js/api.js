/**
 * Admin API client — attaches Sanctum bearer token automatically.
 * Storage: localStorage['admin_token'] set on successful login.
 */
(function () {
    const API_BASE = window.ADMIN_API_BASE || '/api/v1';
    const TOKEN_KEY = 'admin_token';

    function token() { return localStorage.getItem(TOKEN_KEY); }
    function setToken(t) { localStorage.setItem(TOKEN_KEY, t); }
    function clearToken() { localStorage.removeItem(TOKEN_KEY); }

    async function request(path, { method = 'GET', body, headers, raw } = {}) {
        const url = path.startsWith('http') ? path : API_BASE + path;
        const opts = {
            method,
            headers: {
                'Accept': 'application/json',
                ...(token() ? { Authorization: 'Bearer ' + token() } : {}),
                ...(headers || {}),
            },
        };
        if (body instanceof FormData) {
            opts.body = body;
        } else if (body !== undefined) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        const res = await fetch(url, opts);

        if (res.status === 401) {
            clearToken();
            if (!location.pathname.endsWith('/login') && !location.pathname.endsWith('/login.html')) {
                location.href = '/admin/login';
            }
            const err = new Error('Unauthenticated');
            err.status = 401;
            throw err;
        }

        if (!res.ok) {
            let data = {};
            try { data = await res.json(); } catch (e) {}
            const err = new Error(data?.message || `API ${res.status}: ${res.statusText}`);
            err.status = res.status;
            err.validation = data?.errors;
            throw err;
        }

        if (res.status === 204) return null;
        if (raw) return res;
        return res.json();
    }

    const AdminAPI = {
        token, setToken, clearToken,

        auth: {
            login:  (email, password) => request('/admin/auth/login',  { method: 'POST', body: { email, password } }),
            logout: ()                => request('/admin/auth/logout', { method: 'POST' }),
            me:     ()                => request('/admin/auth/me'),
        },

        dashboard: () => request('/admin/dashboard'),

        projects: {
            list:   (params = {}) => request('/admin/projects?' + new URLSearchParams(params).toString()),
            get:    (id)          => request(`/admin/projects/${id}`),
            // Both create and update use POST — accepts plain objects OR FormData
            // (for cover image upload). Update goes to POST /projects/{id} alias.
            create: (data)        => request('/admin/projects',       { method: 'POST', body: data }),
            update: (id, data)    => request(`/admin/projects/${id}`, { method: 'POST', body: data }),
            delete: (id)          => request(`/admin/projects/${id}`, { method: 'DELETE' }),
        },
        blog: {
            list:   (params = {}) => request('/admin/blog?' + new URLSearchParams(params).toString()),
            // expand=1 tells BlogPostResource to include body_markdown/body_html.
            get:    (id)          => request(`/admin/blog/${id}?expand=1`),
            // create/update use POST (multipart accepted for cover image uploads).
            create: (data)        => request('/admin/blog',       { method: 'POST', body: data }),
            update: (id, data)    => request(`/admin/blog/${id}`, { method: 'POST', body: data }),
            delete: (id)          => request(`/admin/blog/${id}`, { method: 'DELETE' }),
        },
        skills: {
            list:   ()         => request('/admin/skills'),
            get:    (id)       => request(`/admin/skills/${id}`),
            create: (data)     => request('/admin/skills',       { method: 'POST',  body: data }),
            update: (id, data) => request(`/admin/skills/${id}`, { method: 'PUT',   body: data }),
            delete: (id)       => request(`/admin/skills/${id}`, { method: 'DELETE' }),
        },
        experiences: {
            list:   ()         => request('/admin/experiences'),
            get:    (id)       => request(`/admin/experiences/${id}`),
            create: (data)     => request('/admin/experiences',       { method: 'POST',  body: data }),
            update: (id, data) => request(`/admin/experiences/${id}`, { method: 'PUT',   body: data }),
            delete: (id)       => request(`/admin/experiences/${id}`, { method: 'DELETE' }),
        },
        certifications: {
            list:   ()         => request('/admin/certifications'),
            get:    (id)       => request(`/admin/certifications/${id}`),
            // create/update use POST (multipart accepted for image uploads).
            create: (data)     => request('/admin/certifications',       { method: 'POST', body: data }),
            update: (id, data) => request(`/admin/certifications/${id}`, { method: 'POST', body: data }),
            delete: (id)       => request(`/admin/certifications/${id}`, { method: 'DELETE' }),
        },
        clients: {
            list:   ()         => request('/admin/clients'),
            get:    (id)       => request(`/admin/clients/${id}`),
            // create/update always go as multipart POST (logo file + fields)
            create: (formData) => request('/admin/clients',       { method: 'POST', body: formData }),
            update: (id, formData) => request(`/admin/clients/${id}`, { method: 'POST', body: formData }),
            delete: (id)       => request(`/admin/clients/${id}`, { method: 'DELETE' }),
        },
        messages: {
            list:        (params = {}) => request('/admin/messages?' + new URLSearchParams(params).toString()),
            get:         (id)          => request(`/admin/messages/${id}`),
            markRead:    (id, read)    => request(`/admin/messages/${id}/read`, { method: 'PATCH', body: { read } }),
            delete:      (id)          => request(`/admin/messages/${id}`, { method: 'DELETE' }),
            unreadCount: ()            => request('/admin/messages/unread-count'),
        },
        settings: {
            get:    ()      => request('/admin/settings'),
            update: (pairs) => request('/admin/settings', { method: 'PUT', body: { settings: pairs } }),
            upload: (key, file) => {
                const fd = new FormData();
                fd.append('file', file);
                return request(`/admin/settings/${encodeURIComponent(key)}/upload`, { method: 'POST', body: fd });
            },
            clearFile: (key) => request(`/admin/settings/${encodeURIComponent(key)}/upload`, { method: 'DELETE' }),
        },
    };

    window.AdminAPI = AdminAPI;
})();
