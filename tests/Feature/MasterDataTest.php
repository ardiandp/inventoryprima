<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected int $seq = 0;

    protected function uniq(string $prefix): string
    {
        $this->seq++;
        return strtoupper($prefix . substr(md5($prefix . $this->seq . microtime()), 0, 6));
    }

    protected function nextCode(string $prefix): string
    {
        return $this->uniq($prefix);
    }

    protected function actingAsMaster(): User
    {
        return $this->actingAsMasterAdmin();
    }

    protected function actingAsMasterAdmin(): User
    {
        $role = Role::factory()->create(['name' => 'master_admin']);
        foreach (['master.view', 'master.create', 'master.edit', 'master.delete'] as $permName) {
            $perm = Permission::factory()->create(['name' => $permName, 'module' => 'master']);
            $role->permissions()->attach($perm->id);
        }
        $user = User::factory()->create();
        $user->roles()->attach($role->id);
        $this->actingAs($user);
        return $user;
    }

    protected function makeCategory(array $over = []): ItemCategory
    {
        return ItemCategory::factory()->create(array_merge([
            'code' => $this->uniq('CAT'),
            'name' => 'Kategori ' . Str::random(8),
            'is_active' => true,
        ], $over));
    }

    protected function makeUnit(array $over = []): Unit
    {
        return Unit::factory()->create(array_merge([
            'code' => $this->uniq('U'),
            'name' => 'Satuan ' . Str::random(8),
            'symbol' => 's' . Str::random(3),
            'is_active' => true,
        ], $over));
    }

    protected function makeItem(array $over = []): Item
    {
        $cat = $over['category_id'] ?? $this->makeCategory()->id;
        $unit = $over['default_unit_id'] ?? $this->makeUnit()->id;
        unset($over['category_id'], $over['default_unit_id']);
        return Item::factory()->create(array_merge([
            'code' => $this->uniq('BRG'),
            'name' => 'Barang ' . Str::random(8),
            'category_id' => $cat,
            'default_unit_id' => $unit,
            'min_stock' => 0,
            'max_stock' => 100,
            'is_active' => true,
        ], $over));
    }

    public function test_guest_diblokir_dari_master(): void
    {
        $this->get('/master/items')->assertRedirect('/login');
        $this->get('/master/categories')->assertRedirect('/login');
        $this->get('/master/units')->assertRedirect('/login');
    }

    public function test_user_tanpa_permission_ditolak(): void
    {
        $role = Role::factory()->create(['name' => 'warehouse']);
        $user = User::factory()->create();
        $user->roles()->attach($role->id);
        $this->actingAs($user)->get('/master/items')->assertForbidden();
    }

    public function test_item_store_create_dan_audit(): void
    {
        $this->actingAsMasterAdmin();
        $cat = $this->makeCategory();
        $unit = $this->makeUnit();
        $this->post('/master/items', [
            'code' => 'BRGTEST01',
            'name' => 'Bearing Test',
            'category_id' => $cat->id,
            'default_unit_id' => $unit->id,
            'min_stock' => 10,
            'max_stock' => 100,
        ])->assertRedirect('/master/items');
        $this->assertDatabaseHas('items', ['code' => 'BRGTEST01']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'create', 'model_type' => Item::class]);
    }

    public function test_item_kode_unik(): void
    {
        $this->actingAsMasterAdmin();
        $this->makeItem(['code' => 'BRGDUP01']);
        $this->post('/master/items', [
            'code' => 'BRGDUP01', 'name' => 'Duplikat',
            'category_id' => $this->makeCategory()->id,
            'default_unit_id' => $this->makeUnit()->id,
            'min_stock' => 0,
        ])->assertSessionHasErrors('code');
    }
}
