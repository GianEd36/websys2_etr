<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\apiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

//All of the routings I did
Route::get('/', [apiController::class, 'showMovies'])->name('home');
Route::get('/homepage', [apiController::class, 'showMovies']);
Route::get('/search', [apiController::class, 'search']);
Route::get('/genre/{id}', [apiController::class, 'byGenre']);
Route::get('/movie/{id}', [apiController::class, 'showDetails'])->name('movie.details');

//Auto created by the breeze plugin
Route::middleware('auth')->group(function () {
    //Needed this for the user account ReviewController
    Route::post('/movie/{id}/review', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');


    //Breeze provided routing are these
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
