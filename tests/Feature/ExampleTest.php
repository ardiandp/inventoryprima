<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Guest diarahkan ke login (auth wajib di PHASE 2).
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/home');
    }

    public function test_home_requires_auth(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
