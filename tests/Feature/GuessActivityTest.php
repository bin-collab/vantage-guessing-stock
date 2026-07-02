<?php

namespace Tests\Feature;

use App\Models\ClosingPrice;
use App\Models\Guess;
use App\Models\OptInUser;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuessActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_email_and_uid(): void
    {
        $user = OptInUser::create([
            'email' => 'test@example.com',
            'uid' => '12345',
            'deposit_amount' => 1000,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'uid' => '12345',
        ]);

        $response->assertRedirect('/');
        $this->assertEquals($user->id, session('opt_in_user_id'));
    }

    public function test_user_can_submit_guess_before_6pm(): void
    {
        $user = OptInUser::create([
            'email' => 'test@example.com',
            'uid' => '12345',
        ]);
        $stock = Stock::create(['symbol' => 'TSLA.24H', 'name' => 'Tesla']);

        Carbon::setTestNow(Carbon::parse('2026-07-06 10:00:00', '+03:00')); // 10 AM GMT+3 on Jul 6

        $this->withSession(['opt_in_user_id' => $user->id])
            ->post('/guess', [
                'guesses' => [
                    ['stock_id' => $stock->id, 'guessed_price' => 200],
                ],
            ]);

        $this->assertDatabaseHas('guesses', [
            'opt_in_user_id' => $user->id,
            'stock_id' => $stock->id,
            'guessed_price' => 200,
        ]);
        $this->assertEquals('2026-07-06', Guess::first()->guess_date->toDateString());
    }

    public function test_user_cannot_submit_guess_after_6pm(): void
    {
        $user = OptInUser::create([
            'email' => 'test@example.com',
            'uid' => '12345',
        ]);
        $stock = Stock::create(['symbol' => 'TSLA.24H', 'name' => 'Tesla']);

        Carbon::setTestNow(Carbon::parse('2026-07-06 19:00:00', '+03:00')); // 7 PM GMT+3 on Jul 6

        $response = $this->withSession(['opt_in_user_id' => $user->id])
            ->post('/guess', [
                'guesses' => [
                    ['stock_id' => $stock->id, 'guessed_price' => 200],
                ],
            ]);

        $response->assertSessionHasErrors(['message']);
        $this->assertDatabaseMissing('guesses', ['guessed_price' => 200]);
    }

    public function test_user_cannot_submit_guess_before_campaign_start(): void
    {
        $user = OptInUser::create([
            'email' => 'test@example.com',
            'uid' => '12345',
        ]);
        $stock = Stock::create(['symbol' => 'TSLA.24H', 'name' => 'Tesla']);

        Carbon::setTestNow(Carbon::parse('2026-07-05 10:00:00', '+03:00')); // July 5th (Campaign starts July 6th)

        $response = $this->withSession(['opt_in_user_id' => $user->id])
            ->post('/guess', [
                'guesses' => [
                    ['stock_id' => $stock->id, 'guessed_price' => 200],
                ],
            ]);

        $response->assertSessionHasErrors(['message']);
        $this->assertDatabaseMissing('guesses', ['guessed_price' => 200]);
    }

    public function test_user_cannot_submit_guess_after_campaign_end(): void
    {
        $user = OptInUser::create([
            'email' => 'test@example.com',
            'uid' => '12345',
        ]);
        $stock = Stock::create(['symbol' => 'TSLA.24H', 'name' => 'Tesla']);

        Carbon::setTestNow(Carbon::parse('2026-07-11 10:00:00', '+03:00')); // July 11th (Campaign ended July 10th)

        $response = $this->withSession(['opt_in_user_id' => $user->id])
            ->post('/guess', [
                'guesses' => [
                    ['stock_id' => $stock->id, 'guessed_price' => 200],
                ],
            ]);

        $response->assertSessionHasErrors(['message']);
        $this->assertDatabaseMissing('guesses', ['guessed_price' => 200]);
    }

    public function test_guess_validation_when_admin_enters_price(): void
    {
        $user = OptInUser::create(['email' => 't1@e.com', 'uid' => 'u1']);
        $stock = Stock::create(['symbol' => 'TSLA.24H', 'name' => 'T']);

        $guess = Guess::create([
            'opt_in_user_id' => $user->id,
            'stock_id' => $stock->id,
            'guess_date' => Carbon::today(),
            'guessed_price' => 396,
        ]);

        // Admin enters price 396.72
        ClosingPrice::create([
            'stock_id' => $stock->id,
            'date' => Carbon::today(),
            'price' => 396.72,
        ]);

        $this->assertTrue($guess->fresh()->is_correct);

        // Update to incorrect price
        $closingPrice = ClosingPrice::where('stock_id', $stock->id)->first();
        $closingPrice->update(['price' => 400.00]);

        $this->assertFalse($guess->fresh()->is_correct);
    }

    public function test_guess_submission_is_rate_limited(): void
    {
        $user = OptInUser::create([
            'email' => 'test@example.com',
            'uid' => '12345',
        ]);
        $stock = Stock::create(['symbol' => 'TSLA.24H', 'name' => 'Tesla']);

        Carbon::setTestNow(Carbon::parse('2026-07-06 10:00:00', '+03:00')); // Campaign active

        // Simulate 30 successful requests
        for ($i = 0; $i < 30; $i++) {
            $response = $this->withSession(['opt_in_user_id' => $user->id])
                ->post('/guess', [
                    'guesses' => [
                        ['stock_id' => $stock->id, 'guessed_price' => 200 + $i],
                    ],
                ]);
            $response->assertStatus(302); // Redirect back on success
        }

        // The 31st request should be rate limited
        $response = $this->withSession(['opt_in_user_id' => $user->id])
            ->post('/guess', [
                'guesses' => [
                    ['stock_id' => $stock->id, 'guessed_price' => 300],
                ],
            ]);

        $response->assertSessionHasErrors(['message']);

        // Let's also check if JSON request gets 422 with validation error
        $responseJson = $this->withSession(['opt_in_user_id' => $user->id])
            ->postJson('/guess', [
                'guesses' => [
                    ['stock_id' => $stock->id, 'guessed_price' => 301],
                ],
            ]);

        $responseJson->assertStatus(422);
        $responseJson->assertJsonPath('errors.message', '操作過於頻繁，請稍後再試。');
    }

    public function test_guest_cannot_submit_guess_and_redirects_to_landing(): void
    {
        $stock = Stock::create(['symbol' => 'TSLA.24H', 'name' => 'Tesla']);

        Carbon::setTestNow(Carbon::parse('2026-07-06 10:00:00', '+03:00'));

        $response = $this->post('/guess', [
            'guesses' => [
                ['stock_id' => $stock->id, 'guessed_price' => 200],
            ],
        ]);

        $response->assertRedirect('/');
    }
}
