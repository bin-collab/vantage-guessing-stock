<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing');
})->name('landing');

Route::get('/login', 'App\Http\Controllers\AuthController@showLogin')->name('login');
Route::post('/login', 'App\Http\Controllers\AuthController@login');
Route::post('/logout', 'App\Http\Controllers\AuthController@logout')->name('logout');

Route::middleware(['opt-in-auth'])->group(function () {
    Route::get('/guess', 'App\Http\Controllers\GuessController@index')->name('guess.index');
    Route::post('/guess', 'App\Http\Controllers\GuessController@store')->name('guess.store');
});
