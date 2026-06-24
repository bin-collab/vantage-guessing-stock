<?php

use App\Models\Guess;
use App\Models\Setting;
use App\Models\Stock;
use App\Services\GuessService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

Route::get('/', function (GuessService $guessService) {
    $today = Carbon::today('+03:00');
    $existingGuesses = null;
    $guessHistory = [];

    if (Session::has('opt_in_user_id')) {
        $userId = Session::get('opt_in_user_id');

        $existingGuesses = Guess::where('opt_in_user_id', Session::get('opt_in_user_id'))
            ->where('guess_date', $today)
            ->get()
            ->keyBy('stock_id');

        $guessHistory = Guess::with('stock')
            ->where('opt_in_user_id', $userId)
            ->orderByDesc('guess_date')
            ->orderByDesc('id')
            ->get();
    }

    return Inertia::render('Landing', [
        'stocks' => Stock::all(),
        'canGuess' => $guessService->isGuessingAllowed($today),
        'existingGuesses' => $existingGuesses,
        'guessHistory' => $guessHistory,
        'settings' => Setting::first(),
    ]);
})->name('landing');

// Route::get('/', function () {
//     return Inertia::render('ComingSoon');
// })->name('landing');

Route::post('/login', 'App\Http\Controllers\AuthController@login');
Route::post('/logout', 'App\Http\Controllers\AuthController@logout')->name('logout');

Route::middleware(['opt-in-auth', 'throttle:guess-submission'])->group(function () {
    Route::post('/guess', 'App\Http\Controllers\GuessController@store')->name('guess.store');
});
