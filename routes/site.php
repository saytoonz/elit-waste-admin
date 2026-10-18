<?php

use App\Http\Controllers\Site\SiteController;
use App\Http\Controllers\Site\SiteFormController;
use Illuminate\Support\Facades\Route;

/*
| Public website. Loaded last from web.php so the catch-all page route never
| shadows an app route.
*/

Route::get('/', [SiteController::class, 'home'])->name('site.home');

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/forms/submit', [SiteFormController::class, 'submit'])->name('site.forms.submit');
    Route::post('/forms/subscribe', [SiteFormController::class, 'subscribe'])->name('site.forms.subscribe');
});

Route::get('/news/{slug}', [SiteController::class, 'post'])
    ->where('slug', '[a-z0-9-]+')
    ->name('site.posts.show');

Route::get('/{slug}', [SiteController::class, 'page'])
    ->where('slug', '[a-z0-9-]+')
    ->name('site.page');
