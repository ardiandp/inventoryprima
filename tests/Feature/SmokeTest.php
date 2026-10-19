<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    /** Beranda/AdminLTE layout dapat diakses (setelah login) dan mengembalikan 200. */
    public function test_home_page_returns_successful_response(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Inventori');
    }

    /** Guest diarahkan ke halaman login. */
    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/home');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** Halaman utama melakukan redirect ke beranda. */
    public function test_root_redirects_to_home(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/home');
    }

    /** Koneksi database dapat digunakan (migration sudah dijalankan). */
    public function test_database_connection_works(): void
    {
        $result = $this->app->make('db')->connection()->select('SELECT 1 AS connected');

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]->connected);
    }
}
