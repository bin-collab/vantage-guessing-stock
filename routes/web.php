<?php

use App\Models\Stock;
use App\Services\GuessService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Models\Guess;
use Illuminate\Support\Facades\Session;

Route::get('/', function (GuessService $guessService) {
    $today = Carbon::today();
    $existingGuesses = null;

    if (Session::has('opt_in_user_id')) {
        $existingGuesses = Guess::where('opt_in_user_id', Session::get('opt_in_user_id'))
            ->where('guess_date', $today)
            ->get()
            ->keyBy('stock_id');
    }

    return Inertia::render('Landing', [
        'stocks' => Stock::all(),
        'canGuess' => $guessService->isGuessingAllowed($today),
        'existingGuesses' => $existingGuesses,
    ]);
})->name('landing');

Route::post('/login', 'App\Http\Controllers\AuthController@login');
Route::post('/logout', 'App\Http\Controllers\AuthController@logout')->name('logout');

Route::middleware(['opt-in-auth'])->group(function () {
    Route::post('/guess', 'App\Http\Controllers\GuessController@store')->name('guess.store');
});
