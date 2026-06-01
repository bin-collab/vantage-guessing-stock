<?php

namespace App\Services;

use App\Models\Guess;
use App\Models\ClosingPrice;
use App\Models\OptInUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GuessService
{
    /**
     * Validate all guesses for a specific closing price record.
     */
    public function validateGuesses(ClosingPrice $closingPrice): void
    {
        $integerPrice = (int) $closingPrice->price;

        Guess::where('stock_id', $closingPrice->stock_id)
            ->where('guess_date', $closingPrice->date)
            ->chunkById(100, function ($guesses) use ($integerPrice) {
                foreach ($guesses as $guess) {
                    $guess->update([
                        'is_correct' => $guess->guessed_price === $integerPrice
                    ]);
                }
            });
    }

    /**
     * Check if a user can still submit/modify a guess for today.
     * Deadline is 6 PM daily.
     */
    public function isGuessingAllowed(Carbon $date): bool
    {
        // For testing/development, we might want to override this.
        // In production, check if current time is before 6 PM of the given date.
        $now = Carbon::now();

        // If the date is not today, we can't guess (only current day allowed)
        if (!$date->isToday()) {
            return false;
        }

        return $now->hour < 18;
    }

    /**
     * Get the overall grand prize winner ($500).
     * Rule: Most correct guesses. Tie-breaker: Earlier registration.
     */
    public function getGrandPrizeWinner()
    {
        return OptInUser::withCount([
            'guesses' => function ($query) {
                $query->where('is_correct', true);
            }
        ])
            ->orderByDesc('guesses_count')
            ->orderBy('created_at')
            ->first();
    }

    /**
     * Get statistics for winners.
     */
    public function getStats(): array
    {
        return [
            'total_guesses' => Guess::count(),
            'correct_guesses' => Guess::where('is_correct', true)->count(),
            'total_participants' => OptInUser::count(),
            'participants_with_correct_guess' => OptInUser::whereHas('guesses', function ($q) {
                $q->where('is_correct', true);
            })->count(),
        ];
    }
}
