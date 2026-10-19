<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // --- GUEST DIBLOKIR (acceptance criteria PHASE 2) ---

    public function test_guest_is_redirected_to_login_from_home(): void
    {
        $this->get('/home')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_users_page(): void
    {
        $this->get('/users')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_roles_page(): void
    {
        $this->get('/roles')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_audit_logs_page(): void
    {
        $this->get('/audit-logs')->assertRedirect('/login');
    }

    // --- LOGIN ---

    public function test_login_page_displays_for_guest(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $role = Role::factory()->create(['name' => 'super_admin']);
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $user->roles()->attach($role->id);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_logged_to_audit_log(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'login',
        ]);
    }

    // --- LOGOUT ---

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'logout',
        ]);
    }

    // --- AUTHENTICATED USER AKSES HOME ---

    public function test_authenticated_user_can_access_home(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/home')->assertOk();
    }

    public function test_authenticated_user_sees_name_in_navbar(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);

        $this->actingAs($user)->get('/home')->assertSee('Budi Santoso');
    }
}