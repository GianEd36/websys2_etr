<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\apiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\BannedController;
use Illuminate\Support\Facades\Route;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

// Protect all web routes with the CheckBanned middleware so banned users
// are logged out and redirected to the appeal/notice page on any request.
Route::middleware(\App\Http\Middleware\CheckBanned::class)->group(function () {
    Route::get('/', [apiController::class, 'showMovies'])->name('home');
    Route::get('/homepage', [apiController::class, 'showMovies']);
    Route::get('/search', [apiController::class, 'search']);
    Route::get('/genre/{id}', [apiController::class, 'byGenre']);
    Route::get('/movie/{id}', [apiController::class, 'showDetails'])->name('movie.details');
    Route::get('/movies', [apiController::class, 'showMovies'])->name('movies.show');
    // Banned notice and appeal (public notice, appeal requires auth)
    Route::get('/banned', [BannedController::class, 'notice'])->name('banned.notice');
    Route::post('/banned/appeal', [BannedController::class, 'appeal'])->name('banned.appeal')->middleware('auth');
    
    //Auto created by the breeze plugin
    Route::middleware(['auth'])->group(function () {
        //Needed this for the user account ReviewController
        Route::post('/movie/{id}/review', [ReviewController::class, 'store'])->name('reviews.store');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
        //Reply
        Route::post('/reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');
        //Upvotes
        Route::post('/reviews/{review}/vote', [ReviewController::class, 'vote'])->name('reviews.vote');
        //User reporting routes
        Route::post('/reviews/{review}/report', [ReviewController::class, 'report'])->name('reviews.report');

        //Breeze provided routing are these
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware(['auth', 'verified'])
            ->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show');
    });
});

Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function() {
//All of the routings I did
Route::get('/', [apiController::class, 'showMovies'])->name('home');
Route::get('/homepage', [apiController::class, 'showMovies']);
Route::get('/search', [apiController::class, 'search']);
Route::get('/genre/{id}', [apiController::class, 'byGenre']);
Route::get('/movie/{id}', [apiController::class, 'showDetails'])->name('movie.details');
Route::get('/movies', [apiController::class, 'showMovies'])->name('movies.show');
// Banned notice and appeal (public notice, appeal requires auth)
Route::get('/banned', [BannedController::class, 'notice'])->name('banned.notice');
Route::post('/banned/appeal', [BannedController::class, 'appeal'])->name('banned.appeal')->middleware('auth');
});
//Auto created by the breeze plugin
Route::middleware(['auth', \App\Http\Middleware\CheckBanned::class])->group(function () {
    //Needed this for the user account ReviewController
    Route::post('/movie/{id}/review', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    //Reply
    Route::post('/reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');
    //Upvotes
    //Pivot Table that connects the user and the upvotes system tightly
    Route::post('/reviews/{review}/vote', [ReviewController::class, 'vote'])->name('reviews.vote');
    //User reporting routes
    Route::post('/reviews/{review}/report', [ReviewController::class, 'report'])->name('reviews.report');

    //Breeze provided routing are these
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function() {
    Route::get('/reports', [AdminController::class, 'index'])->name('reports.index');
    Route::delete('/reports/{report}', [AdminController::class, 'dismiss'])->name('reports.dismiss');
    Route::post('/users/{user}/ban', [AdminController::class, 'banUser'])->name('users.ban');
    Route::post('/users/{user}/unban', [AdminController::class, 'unbanUser'])->name('users.unban');
    Route::get('/movies/stats', [AdminController::class, 'moviesStats'])->name('movies.stats');
    // Appeals management
    Route::get('/appeals', [AdminController::class, 'appealsIndex'])->name('appeals.index');
    Route::post('/appeals/{appeal}/accept', [AdminController::class, 'acceptAppeal'])->name('appeals.accept');
    Route::post('/appeals/{appeal}/deny', [AdminController::class, 'denyAppeal'])->name('appeals.deny');
});
require __DIR__.'/auth.php';
