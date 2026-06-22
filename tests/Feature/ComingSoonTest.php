<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ComingSoonTest extends TestCase
{
    use RefreshDatabase;

    public function test_coming_soon_page_returns_a_successful_response(): void
    {
        $response = $this->get('/coming-soon');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page): void {
            $page->component('ComingSoon');
        });
    }
}
