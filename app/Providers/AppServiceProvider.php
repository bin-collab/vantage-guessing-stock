<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('guess-submission', function (Request $request) {
            $key = $request->session()->get('opt_in_user_id') ?: $request->ip();

            return Limit::perMinute(30)->by($key)->response(function (Request $request, array $headers) {
                if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                    return response()->json([
                        'message' => '操作過於頻繁，請稍後再試。',
                        'errors' => ['message' => '操作過於頻繁，請稍後再試。'],
                    ], 422);
                }

                return back()->withErrors(['message' => '操作過於頻繁，請稍後再試。']);
            });
        });
    }
}
