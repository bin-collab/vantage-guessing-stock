<?php

namespace App\Http\Controllers;

use App\Models\Guess;
use App\Models\OptInUser;
use App\Models\Setting;
use App\Models\Stock;
use App\Services\GuessService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class GuessController extends Controller
{
    public function index(GuessService $guessService)
    {
        $userId = Session::get('opt_in_user_id');
        $user = OptInUser::findOrFail($userId);
        $today = Carbon::today('+03:00');

        $stocks = Stock::all();
        $existingGuesses = Guess::where('opt_in_user_id', $userId)
            ->where('guess_date', $today)
            ->get()
            ->keyBy('stock_id');

        return Inertia::render('GuessInput', [
            'stocks' => $stocks,
            'existingGuesses' => $existingGuesses,
            'canGuess' => $guessService->isGuessingAllowed($today),
            'user' => $user,
        ]);
    }

    public function store(Request $request, GuessService $guessService)
    {
        $userId = Session::get('opt_in_user_id');
        $today = Carbon::today('+03:00');

        if (! $guessService->isGuessingAllowed($today)) {
            $settings = Setting::first();
            $deadline = $settings ? $settings->daily_deadline : '18:00';

            return back()->withErrors(['message' => "Guessing is closed for today (Deadline: {$deadline} GMT+3)."]);
        }

        $request->validate([
            'guesses' => 'required|array',
            'guesses.*.stock_id' => 'required|exists:stocks,id',
            'guesses.*.guessed_price' => 'required|integer|min:0',
        ]);

        foreach ($request->guesses as $guessData) {
            Guess::updateOrCreate(
                [
                    'opt_in_user_id' => $userId,
                    'stock_id' => $guessData['stock_id'],
                    'guess_date' => $today,
                ],
                [
                    'guessed_price' => $guessData['guessed_price'],
                ]
            );
        }

        return back()->with('success', 'Your guesses have been saved successfully.');
    }
}
