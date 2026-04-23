<?php

use App\Http\Controllers\Api\V1\Public\BlogController;
use App\Http\Controllers\Api\V1\Public\ContactController;
use App\Http\Controllers\Api\V1\Public\ProjectController;
use App\Http\Controllers\Api\V1\Public\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/',                   [SiteController::class, 'index']);

Route::prefix('site')->group(function () {
    Route::get('profile',         [SiteController::class, 'profile']);
    Route::get('branding',        [SiteController::class, 'branding']);
    Route::get('experiences',     [SiteController::class, 'experiences']);
    Route::get('skills',          [SiteController::class, 'skills']);
    Route::get('certifications',  [SiteController::class, 'certifications']);
    Route::get('clients',         [SiteController::class, 'clients']);
});

Route::get('projects',            [ProjectController::class, 'index']);
Route::get('projects/{slug}',     [ProjectController::class, 'show']);

Route::get('blog',                [BlogController::class, 'index']);
Route::get('blog/{slug}',         [BlogController::class, 'show']);

Route::post('contact',            [ContactController::class, 'store'])
    ->middleware('throttle:contact');
