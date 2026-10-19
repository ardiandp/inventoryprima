<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_logger_writes_record(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        AuditLogger::log('update', 'App\\Models\\Item', 42, [
            'old' => ['name' => 'Lama'],
            'new' => ['name' => 'Baru'],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'update',
            'model_type' => 'App\\Models\\Item',
            'model_id' => '42',
        ]);
    }

    public function test_audit_logger_stores_old_and_new_values(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        AuditLogger::log('update', 'App\\Models\\User', $user->id, [
            'old' => ['name' => 'Old'],
            'new' => ['name' => 'New'],
        ]);

        $log = AuditLog::latest('id')->first();
        $this->assertEquals('Old', $log->old_values['name']);
        $this->assertEquals('New', $log->new_values['name']);
    }

    public function test_audit_logger_works_without_authenticated_user(): void
    {
        Auth::logout();

        AuditLogger::log('system_action', null, null, ['event' => 'seed']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null,
            'action' => 'system_action',
        ]);
    }

    public function test_audit_logs_index_requires_permission(): void
    {
        // User tanpa audit.view
        $role = Role::factory()->create(['name' => 'warehouse']);
        $user = User::factory()->create();
        $user->roles()->attach($role->id);

        $this->actingAs($user)->get('/audit-logs')->assertForbidden();
    }

    public function test_audit_logs_index_accessible_with_permission(): void
    {
        $role = Role::factory()->create(['name' => 'super_admin']);
        $perm = Permission::factory()->create(['name' => 'audit.view']);
        $role->permissions()->attach($perm->id);
        $user = User::factory()->create();
        $user->roles()->attach($role->id);

        AuditLogger::log('test_action');

        $this->actingAs($user)->get('/audit-logs')
            ->assertOk()
            ->assertSee('test_action');
    }

    public function test_audit_logs_index_filters_by_action(): void
    {
        $role = Role::factory()->create(['name' => 'super_admin']);
        $perm = Permission::factory()->create(['name' => 'audit.view']);
        $role->permissions()->attach($perm->id);
        $user = User::factory()->create();
        $user->roles()->attach($role->id);

        // Data khas agar baris dapat dibedakan (dropdown selalu memuat semua nama action,
        // jadi assertDontSee terhadap nama action saja tidak valid).
        AuditLogger::log('login', null, null, ['email' => 'login-marker@test.com']);
        AuditLogger::log('delete', null, null, ['victim' => 'delete-marker-name']);

        $this->actingAs($user)->get('/audit-logs?action=login')
            ->assertOk()
            ->assertSee('login-marker@test.com')
            ->assertDontSee('delete-marker-name');
    }

    public function test_login_and_logout_are_logged(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret123']);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $user->id, 'action' => 'login']);

        $this->post('/logout');
        $this->assertDatabaseHas('audit_logs', ['user_id' => $user->id, 'action' => 'logout']);
    }
}