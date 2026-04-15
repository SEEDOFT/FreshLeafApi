<?php

declare(strict_types=1);

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_root_route_is_not_available_in_api_only_mode(): void
    {
        $response = $this->get('/');

        $response->assertStatus(404);
    }
}
