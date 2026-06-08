<?php

namespace Tests\Feature;

use App\Models\Guess;
use App\Models\OptInUser;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LandingPageGuessHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_returns_only_the_authenticated_users_guess_history(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00', '+03:00'));

        try {
            $user = OptInUser::create([
                'email' => 'tester@example.com',
                'uid' => '1001',
            ]);
            $otherUser = OptInUser::create([
                'email' => 'other@example.com',
                'uid' => '1002',
            ]);
            $stock = Stock::create([
                'symbol' => 'TSLA.24H',
                'name' => 'Tesla',
            ]);
            $otherStock = Stock::create([
                'symbol' => 'AAPL.24H',
                'name' => 'Apple',
            ]);

            Guess::create([
                'opt_in_user_id' => $user->id,
                'stock_id' => $stock->id,
                'guess_date' => Carbon::today('+03:00'),
                'guessed_price' => 250,
            ]);
            Guess::create([
                'opt_in_user_id' => $user->id,
                'stock_id' => $otherStock->id,
                'guess_date' => Carbon::today('+03:00')->subDay(),
                'guessed_price' => 240,
            ]);
            Guess::create([
                'opt_in_user_id' => $otherUser->id,
                'stock_id' => $otherStock->id,
                'guess_date' => Carbon::today('+03:00'),
                'guessed_price' => 999,
            ]);

            $this->withSession(['opt_in_user_id' => $user->id])
                ->get('/')
                ->assertInertia(function (Assert $page): void {
                    $page->component('Landing')
                        ->has('guessHistory', 2)
                        ->where('guessHistory.0.guessed_price', 250)
                        ->where('guessHistory.0.stock.symbol', 'TSLA.24H')
                        ->where('guessHistory.1.guessed_price', 240)
                        ->where('guessHistory.1.stock.symbol', 'AAPL.24H');
                });
        } finally {
            Carbon::setTestNow();
        }
    }
}
