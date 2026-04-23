<?php

use App\Http\Controllers\SeoController;
use App\Http\Controllers\Shell\StaticShellController;
use Illuminate\Support\Facades\Route;

// SEO endpoints
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt',  [SeoController::class, 'robots'])->name('robots');

// Admin shell — /admin and anything under it -> frontend/admin/
Route::get('/admin', fn () => StaticShellController::admin()(request(), ''));
Route::get('/admin/{path}', fn (string $path) => StaticShellController::admin()(request(), $path))
    ->where('path', '.*');

// Web shell — root domain opens the home page directly (frontend/web/index.html).
Route::get('/', fn () => StaticShellController::web()(request(), ''))->name('home');

// Everything else not matched by api.php goes through the web shell.
Route::fallback(function () {
    $path = ltrim(request()->path(), '/');
    if (str_starts_with($path, 'api/')) {
        abort(404);
    }
    return StaticShellController::web()(request(), $path);
});
