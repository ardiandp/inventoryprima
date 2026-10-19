<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: buat user dengan role + permission tertentu.
     */
    private function createUserWithPermission(string $roleName, array $permissionNames): User
    {
        $role = Role::factory()->create(['name' => $roleName]);

        foreach ($permissionNames as $permName) {
            $perm = Permission::factory()->create(['name' => $permName]);
            $role->permissions()->attach($perm->id);
        }

        $user = User::factory()->create();
        $user->roles()->attach($role->id);

        return $user;
    }

    // --- USER MANAGEMENT (permission: user.manage) ---

    public function test_super_admin_can_access_user_management(): void
    {
        $user = $this->createUserWithPermission('super_admin', ['user.manage']);

        $this->actingAs($user)->get('/users')->assertOk();
        $this->actingAs($user)->get('/users/create')->assertOk();
    }

    public function test_user_without_user_manage_permission_is_denied(): void
    {
        // User dengan role tapi TANPA permission user.manage
        $role = Role::factory()->create(['name' => 'warehouse']);
        $user = User::factory()->create();
        $user->roles()->attach($role->id);

        $this->actingAs($user)->get('/users')->assertForbidden();
    }

    public function test_finance_role_cannot_access_user_management(): void
    {
        // Finance role: punya finance.* tapi TIDAK user.manage
        $user = $this->createUserWithPermission('finance', ['finance.view', 'finance.create']);

        $this->actingAs($user)->get('/users')->assertForbidden();
    }

    // --- ROLE MANAGEMENT (permission: role.manage) ---

    public function test_super_admin_can_access_role_management(): void
    {
        $user = $this->createUserWithPermission('super_admin', ['role.manage']);

        $this->actingAs($user)->get('/roles')->assertOk();
        $this->actingAs($user)->get('/permissions')->assertOk();
    }

    public function test_user_without_role_manage_permission_is_denied(): void
    {
        $role = Role::factory()->create(['name' => 'purchasing']);
        $user = User::factory()->create();
        $user->roles()->attach($role->id);

        $this->actingAs($user)->get('/roles')->assertForbidden();
    }

    // --- AUDIT LOG (permission: audit.view) ---

    public function test_super_admin_can_access_audit_logs(): void
    {
        $user = $this->createUserWithPermission('super_admin', ['audit.view']);

        $this->actingAs($user)->get('/audit-logs')->assertOk();
    }

    public function test_user_without_audit_view_permission_is_denied(): void
    {
        $role = Role::factory()->create(['name' => 'production']);
        $user = User::factory()->create();
        $user->roles()->attach($role->id);

        $this->actingAs($user)->get('/audit-logs')->assertForbidden();
    }

    // --- FINANCE TIDAK DAPAT MENGUBAH STOK (acceptance criteria) ---

    public function test_finance_role_does_not_have_inventory_adjust_permission(): void
    {
        // Finance harus punya inventory.view tapi TIDAK inventory.adjust
        $financeRole = Role::factory()->create(['name' => 'finance']);
        $viewPerm = Permission::factory()->create(['name' => 'inventory.view']);
        $adjustPerm = Permission::factory()->create(['name' => 'inventory.adjust']);
        $financeRole->permissions()->attach($viewPerm->id); // hanya view

        $user = User::factory()->create();
        $user->roles()->attach($financeRole->id);

        $this->assertTrue($user->hasPermission('inventory.view'));
        $this->assertFalse($user->hasPermission('inventory.adjust'));
    }

    // --- HASPERMISSION UNIT TESTS ---

    public function test_hasPermission_returns_true_when_role_has_permission(): void
    {
        $user = $this->createUserWithPermission('warehouse', ['inventory.adjust']);

        $this->assertTrue($user->hasPermission('inventory.adjust'));
    }

    public function test_hasPermission_returns_false_when_role_lacks_permission(): void
    {
        $user = $this->createUserWithPermission('qc', ['qc.inspect']);

        $this->assertFalse($user->hasPermission('inventory.adjust'));
    }

    public function test_hasPermission_ignores_inactive_role(): void
    {
        $role = Role::factory()->create(['name' => 'super_admin', 'is_active' => false]);
        $perm = Permission::factory()->create(['name' => 'user.manage']);
        $role->permissions()->attach($perm->id);

        $user = User::factory()->create();
        $user->roles()->attach($role->id);

        $this->assertFalse($user->hasPermission('user.manage'));
    }

    public function test_user_with_no_role_has_no_permission(): void
    {
        $user = User::factory()->create(); // tanpa role

        $this->assertFalse($user->hasPermission('user.manage'));
        $this->assertFalse($user->hasPermission('inventory.adjust'));
    }

    // --- SEEDER ROLE-PERMISSION MAPPING (acceptance: Finance tidak ubah stok) ---

    public function test_seeder_finance_role_cannot_adjust_stock(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $financeRole = Role::where('name', 'finance')->first();
        $this->assertNotNull($financeRole);
        $this->assertTrue($financeRole->permissions()->where('name', 'inventory.view')->exists());
        $this->assertFalse($financeRole->permissions()->where('name', 'inventory.adjust')->exists());
    }

    public function test_seeder_warehouse_role_can_adjust_stock(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $warehouseRole = Role::where('name', 'warehouse')->first();
        $this->assertNotNull($warehouseRole);
        $this->assertTrue($warehouseRole->permissions()->where('name', 'inventory.adjust')->exists());
    }

    public function test_seeder_super_admin_has_all_permissions(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $saRole = Role::where('name', 'super_admin')->first();
        $this->assertNotNull($saRole);
        $this->assertEquals(
            Permission::count(),
            $saRole->permissions()->count(),
            'super_admin harus memiliki semua permission'
        );
    }
}