<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): User
    {
        $role = Role::factory()->create(['name' => 'super_admin']);
        $perm = Permission::factory()->create(['name' => 'user.manage']);
        $role->permissions()->attach($perm->id);
        $user = User::factory()->create();
        $user->roles()->attach($role->id);
        $this->actingAs($user);
        return $user;
    }

    public function test_index_shows_users_list(): void
    {
        $this->actingAsSuperAdmin();
        User::factory()->create(['name' => 'Test User']);
        $this->get('/users')->assertOk()->assertSee('Test User');
    }

    public function test_create_form_displays(): void
    {
        $this->actingAsSuperAdmin();
        $this->get('/users/create')->assertOk()->assertSee('Tambah Pengguna');
    }

    public function test_store_creates_user_with_role(): void
    {
        $this->actingAsSuperAdmin();
        Role::factory()->create(['name' => 'warehouse']);
        $dept = Department::factory()->create();
        $this->post('/users', [
            'name' => 'User Baru', 'email' => 'baru@test.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'department_id' => $dept->id, 'role' => 'warehouse',
        ])->assertRedirect('/users');
        $newUser = User::where('email', 'baru@test.com')->first();
        $this->assertTrue($newUser->hasRole('warehouse'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'create', 'model_type' => User::class]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAsSuperAdmin();
        $this->post('/users', [
            'name' => '', 'email' => 'bad', 'password' => 'short',
            'password_confirmation' => 'x', 'role' => 'none',
        ])->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    public function test_store_rejects_duplicate_email(): void
    {
        $this->actingAsSuperAdmin();
        User::factory()->create(['email' => 'dup@test.com']);
        Role::factory()->create(['name' => 'warehouse']);
        $this->post('/users', [
            'name' => 'Dup', 'email' => 'dup@test.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'role' => 'warehouse',
        ])->assertSessionHasErrors('email');
    }

    public function test_edit_form_displays_with_data(): void
    {
        $this->actingAsSuperAdmin();
        $role = Role::factory()->create(['name' => 'purchasing']);
        $user = User::factory()->create(['name' => 'Edit Me']);
        $user->roles()->attach($role->id);
        $this->get("/users/{$user->id}/edit")
            ->assertOk()->assertSee('Edit Me')->assertSee('purchasing');
    }

    public function test_update_changes_user_data_and_role(): void
    {
        $this->actingAsSuperAdmin();
        $oldRole = Role::factory()->create(['name' => 'purchasing']);
        Role::factory()->create(['name' => 'finance']);
        $user = User::factory()->create(['name' => 'Old', 'email' => 'old@test.com']);
        $user->roles()->attach($oldRole->id);
        $this->put("/users/{$user->id}", [
            'name' => 'New', 'email' => 'new@test.com',
            'password' => '', 'password_confirmation' => '', 'role' => 'finance',
        ])->assertRedirect('/users');
        $user->refresh();
        $this->assertTrue($user->hasRole('finance'));
        $this->assertFalse($user->hasRole('purchasing'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'update', 'model_type' => User::class]);
    }

    public function test_update_changes_password_when_provided(): void
    {
        $this->actingAsSuperAdmin();
        $role = Role::factory()->create(['name' => 'warehouse']);
        $user = User::factory()->create(['password' => bcrypt('old-pass')]);
        $user->roles()->attach($role->id);
        $this->put("/users/{$user->id}", [
            'name' => $user->name, 'email' => $user->email,
            'password' => 'new-password-123', 'password_confirmation' => 'new-password-123',
            'role' => 'warehouse',
        ])->assertRedirect('/users');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_destroy_deletes_user_and_logs_audit(): void
    {
        $this->actingAsSuperAdmin();
        $role = Role::factory()->create(['name' => 'warehouse']);
        $victim = User::factory()->create();
        $victim->roles()->attach($role->id);
        $this->delete("/users/{$victim->id}")->assertRedirect('/users');
        $this->assertDatabaseMissing('users', ['id' => $victim->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'delete', 'model_type' => User::class]);
    }

    public function test_cannot_delete_self(): void
    {
        $this->actingAsSuperAdmin();
        $me = auth()->user();
        $this->delete("/users/{$me->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $me->id]);
    }

    public function test_search_filters_users(): void
    {
        $this->actingAsSuperAdmin();
        User::factory()->create(['name' => 'Alice Wonderland']);
        User::factory()->create(['name' => 'Bob Builder']);
        $this->get('/users?q=Alice')
            ->assertOk()->assertSee('Alice Wonderland')->assertDontSee('Bob Builder');
    }
}