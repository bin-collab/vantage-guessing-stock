<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GuessActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_email_and_uid(): void
    {
        $user = \App\Models\OptInUser::create([
            'email' => 'test@example.com',
            'uid' => '12345',
            'deposit_amount' => 1000,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'uid' => '12345',
        ]);

        $response->assertRedirect('/guess');
        $this->assertEquals($user->id, session('opt_in_user_id'));
    }

    public function test_user_can_submit_guess_before_6pm(): void
    {
        $user = \App\Models\OptInUser::create([
            'email' => 'test@example.com',
            'uid' => '12345',
        ]);
        $stock = \App\Models\Stock::create(['symbol' => 'TSLA.24H', 'name' => 'Tesla']);

        \Carbon\Carbon::setTestNow(\Carbon\Carbon::today()->setHour(10)); // 10 AM

        $this->withSession(['opt_in_user_id' => $user->id])
            ->post('/guess', [
                'guesses' => [
                    ['stock_id' => $stock->id, 'guessed_price' => 200]
                ]
            ]);

        $this->assertDatabaseHas('guesses', [
            'opt_in_user_id' => $user->id,
            'stock_id' => $stock->id,
            'guessed_price' => 200,
        ]);
        $this->assertEquals(\Carbon\Carbon::today()->toDateString(), \App\Models\Guess::first()->guess_date->toDateString());
    }

    public function test_user_cannot_submit_guess_after_6pm(): void
    {
        $user = \App\Models\OptInUser::create([
            'email' => 'test@example.com',
            'uid' => '12345',
        ]);
        $stock = \App\Models\Stock::create(['symbol' => 'TSLA.24H', 'name' => 'Tesla']);

        \Carbon\Carbon::setTestNow(\Carbon\Carbon::today()->setHour(19)); // 7 PM

        $response = $this->withSession(['opt_in_user_id' => $user->id])
            ->post('/guess', [
                'guesses' => [
                    ['stock_id' => $stock->id, 'guessed_price' => 200]
                ]
            ]);

        $response->assertSessionHasErrors(['message']);
        $this->assertDatabaseMissing('guesses', ['guessed_price' => 200]);
    }

    public function test_guess_validation_when_admin_enters_price(): void
    {
        $user = \App\Models\OptInUser::create(['email' => 't1@e.com', 'uid' => 'u1']);
        $stock = \App\Models\Stock::create(['symbol' => 'TSLA.24H', 'name' => 'T']);

        $guess = \App\Models\Guess::create([
            'opt_in_user_id' => $user->id,
            'stock_id' => $stock->id,
            'guess_date' => \Carbon\Carbon::today(),
            'guessed_price' => 396,
        ]);

        // Admin enters price 396.72
        \App\Models\ClosingPrice::create([
            'stock_id' => $stock->id,
            'date' => \Carbon\Carbon::today(),
            'price' => 396.72,
        ]);

        $this->assertTrue($guess->fresh()->is_correct);

        // Update to incorrect price
        $closingPrice = \App\Models\ClosingPrice::where('stock_id', $stock->id)->first();
        $closingPrice->update(['price' => 400.00]);

        $this->assertFalse($guess->fresh()->is_correct);
    }
}

