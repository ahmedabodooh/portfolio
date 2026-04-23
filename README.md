# Ahmed Abo Dooh — Portfolio

A warm, editorial-style portfolio built with Laravel 12, Tailwind CSS v4, and a Filament v3 admin panel.

## Palette

| Role      | Color       | Hex       |
|-----------|-------------|-----------|
| Surface   | Cream       | `#F5EFE6` |
| Ink       | Espresso    | `#1A1410` |
| Accent    | Terracotta  | `#C2410C` |
| Support   | Sage        | `#7A8B6C` |

## Stack

- **Laravel 12** + PHP 8.2
- **Tailwind CSS v4** (via `@tailwindcss/vite`)
- **Filament v3** admin panel (`/admin`)
- **MySQL 8** (database `ahmedabodooh`) — also supports SQLite via `.env`
- **Bricolage Grotesque** + **Fraunces** serif for the editorial feel

## Features

- Editorial hero, selected work grid, experience timeline, stack matrix, notes preview, contact form
- **Dark mode** toggle (persisted in `localStorage`, respects system preference)
- **Project galleries** with arrow-keyboard + click lightbox
- **Blog / Notes** with Markdown rendering, auto reading-time, tags
- **Resume PDF** download (admin uploads the file in Site Settings → `resume_file`)
- **Contact form** with IP-based rate limiting (3/min, 20/day) and a hidden honeypot field
- **S3 / DigitalOcean Spaces** disk pre-configured — just fill `DO_SPACES_*` in `.env` and run `composer require league/flysystem-aws-s3-v3`

## Run it

```bash
# Install deps
composer install
npm install

# Configure .env (MySQL)
cp .env.example .env
php artisan key:generate
# Set: DB_CONNECTION=mysql, DB_DATABASE=ahmedabodooh, DB_USERNAME=root, DB_PASSWORD=

# Create DB (XAMPP)
mysql -u root -e "CREATE DATABASE ahmedabodooh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# Schema + seed demo data
php artisan migrate --seed

# Symlink storage (for image uploads)
php artisan storage:link

# Dev
npm run dev        # Vite, terminal 1
php artisan serve  # App at http://127.0.0.1:8000, terminal 2
```

## Admin

- URL: `http://127.0.0.1:8000/admin`
- Email: `zalfyhima@gmail.com`
- Password: `password`

**Change this immediately** after first login.

### Resources

| Group     | Resource          | What it controls |
|-----------|-------------------|------------------|
| Inbox     | Contact Messages  | Submissions from the public contact form (badge = unread count, read-only). |
| Portfolio | Projects          | Drag to reorder, toggle Featured/Live, upload covers + multi-image gallery. |
| Portfolio | Experiences       | Work history timeline. |
| Portfolio | Skills            | Grouped by Languages/Backend/Frontend/Tools. |
| Portfolio | Certifications    | Training and certificates. |
| Portfolio | Blog posts        | Markdown body, tags, cover image, schedule via `published_at`. |
| Settings  | Site Settings     | Key-value pairs rendered across the site (name, tagline, social, **resume PDF**). |

### Upload your résumé

1. Admin → Settings → Site Settings
2. Find/create key `resume_file`, set **type: Image / File**
3. Upload your PDF
4. Save — the "Download résumé" link appears in the hero + navbar

### S3 / DigitalOcean Spaces

To move uploads off local disk:

```bash
composer require league/flysystem-aws-s3-v3
```

Fill these env vars:

```env
FILESYSTEM_DISK=spaces
DO_SPACES_KEY=...
DO_SPACES_SECRET=...
DO_SPACES_REGION=fra1
DO_SPACES_BUCKET=ahmedabodooh
DO_SPACES_ENDPOINT=https://fra1.digitaloceanspaces.com
DO_SPACES_URL=https://ahmedabodooh.fra1.cdn.digitaloceanspaces.com
```

Then update each `FileUpload::make(...)->disk('public')` in `app/Filament/Resources/*` to `->disk('spaces')`.

## Public routes

- `/`                    — home (hero, work, experience, stack, notes, contact)
- `/projects`            — full archive with category filter
- `/projects/{slug}`     — case study + gallery with lightbox + next project
- `/blog`                — notes index with pagination
- `/blog/{slug}`         — rendered markdown post
- `/resume`              — PDF download (404 if not uploaded)
- `POST /contact`        — rate-limited + honeypot
