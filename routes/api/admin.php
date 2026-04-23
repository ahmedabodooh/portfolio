<?php

use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\BlogController;
use App\Http\Controllers\Api\V1\Admin\CertificationController;
use App\Http\Controllers\Api\V1\Admin\ClientController;
use App\Http\Controllers\Api\V1\Admin\ExperienceController;
use App\Http\Controllers\Api\V1\Admin\MessageController;
use App\Http\Controllers\Api\V1\Admin\ProjectController;
use App\Http\Controllers\Api\V1\Admin\SettingsController;
use App\Http\Controllers\Api\V1\Admin\SkillController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {

    // Public auth endpoints (login issues a token; others need it)
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

    // Protected: require a Sanctum token with the 'admin' ability
    Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',      [AuthController::class, 'me']);

        Route::get('dashboard',    [SettingsController::class, 'dashboard']);

        Route::apiResource('projects',       ProjectController::class);
        // Multipart update alias — PHP cannot parse multipart/form-data on PUT/PATCH,
        // so we accept POST for project updates (mirrors the clients pattern).
        Route::post('projects/{id}', [ProjectController::class, 'update']);
        Route::apiResource('blog',           BlogController::class);
        // Multipart update alias (cover image upload) — same reason as projects/certifications.
        Route::post('blog/{id}', [BlogController::class, 'update']);
        Route::apiResource('skills',         SkillController::class);
        Route::apiResource('experiences',    ExperienceController::class);
        Route::apiResource('certifications', CertificationController::class);
        // Multipart update alias (image upload) — same reason as projects/clients.
        Route::post('certifications/{id}', [CertificationController::class, 'update']);

        // Clients — update uses POST (not PUT) so multipart file uploads work under PHP.
        Route::get   ('clients',        [ClientController::class, 'index']);
        Route::get   ('clients/{id}',   [ClientController::class, 'show']);
        Route::post  ('clients',        [ClientController::class, 'store']);
        Route::post  ('clients/{id}',   [ClientController::class, 'update']);
        Route::delete('clients/{id}',   [ClientController::class, 'destroy']);

        Route::get('messages/unread-count', [MessageController::class, 'unreadCount']);
        Route::patch('messages/{id}/read',  [MessageController::class, 'markRead']);
        Route::apiResource('messages', MessageController::class)
            ->only(['index', 'show', 'destroy']);

        Route::get('settings',                 [SettingsController::class, 'index']);
        Route::put('settings',                 [SettingsController::class, 'update']);
        Route::post('settings/{key}/upload',   [SettingsController::class, 'upload']);
        Route::delete('settings/{key}/upload', [SettingsController::class, 'clearFile']);
    });
});
