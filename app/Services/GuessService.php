<?php

namespace App\Services;

use App\Models\ClosingPrice;
use App\Models\Guess;
use App\Models\OptInUser;
use App\Models\Setting;
use Carbon\Carbon;

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
                        'is_correct' => $guess->guessed_price === $integerPrice,
                    ]);
                }
            });
    }

    /**
     * Check if a user can still submit/modify a guess for today.
     * Allowed range and daily deadline (GMT+3) are configured in Settings database.
     */
    public function isGuessingAllowed(Carbon $date): bool
    {
        $settings = Setting::first();
        if (! $settings) {
            $start = Carbon::parse('2026-07-06', '+03:00');
            $end = Carbon::parse('2026-07-10', '+03:00');
            $deadline = '18:00';
        } else {
            $start = Carbon::parse($settings->guess_start_date->toDateString(), '+03:00');
            $end = Carbon::parse($settings->guess_end_date->toDateString(), '+03:00');
            $deadline = $settings->daily_deadline;
        }

        $now = Carbon::now('+03:00');

        // If the date is not today in GMT+3, we can't guess (only current day allowed)
        if ($date->format('Y-m-d') !== $now->format('Y-m-d')) {
            return false;
        }

        // Must be within the campaign start and end dates
        $startLimit = $start->copy()->startOfDay();
        $endLimit = $end->copy()->endOfDay();

        if (! $now->between($startLimit, $endLimit)) {
            return false;
        }

        // Must be before the daily deadline (GMT+3)
        [$hour, $minute] = explode(':', $deadline);
        $deadlineTime = $now->copy()->setTime((int) $hour, (int) $minute, 0);

        return $now->lt($deadlineTime);
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
            },
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
