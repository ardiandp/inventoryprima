<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    /** Semua tabel dasar PHASE 1 harus tersedia setelah migrate. */
    public function test_phase1_tables_exist(): void
    {
        $tables = [
            'users', 'departments', 'roles', 'permissions',
            'role_user', 'permission_role', 'audit_logs',
            'notifications', 'attachments',
            'item_categories', 'units', 'items',
            'item_unit_conversions', 'warehouses',
            'warehouse_locations', 'suppliers', 'projects',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Tabel {$table} harus tersedia"
            );
        }
    }

    /** Kolom unik harus tersedia pada setiap tabel master. */
    public function test_unique_columns_exist(): void
    {
        $uniqueChecks = [
            ['departments', 'code'],
            ['roles', 'name'],
            ['permissions', 'name'],
            ['item_categories', 'code'],
            ['units', 'code'],
            ['items', 'code'],
            ['warehouses', 'code'],
            ['suppliers', 'code'],
            ['projects', 'code'],
        ];

        foreach ($uniqueChecks as [$table, $column]) {
            $columns = Schema::getColumnListing($table);
            $this->assertContains(
                $column,
                $columns,
                "Kolom {$table}.{$column} harus tersedia"
            );
        }
    }

    /** Kolom foreign key harus tersedia pada relasi utama. */
    public function test_foreign_key_columns_exist(): void
    {
        $fkChecks = [
            ['items', 'category_id'],
            ['items', 'default_unit_id'],
            ['item_unit_conversions', 'item_id'],
            ['item_unit_conversions', 'from_unit_id'],
            ['item_unit_conversions', 'to_unit_id'],
            ['warehouse_locations', 'warehouse_id'],
            ['users', 'department_id'],
        ];

        foreach ($fkChecks as [$table, $column]) {
            $columns = Schema::getColumnListing($table);
            $this->assertContains(
                $column,
                $columns,
                "Kolom {$table}.{$column} harus tersedia sebagai foreign key"
            );
        }
    }

    /** Kolom indeks harus tersedia pada tabel master. */
    public function test_index_columns_exist(): void
    {
        $indexChecks = [
            ['items', 'is_active'],
            ['warehouses', 'is_active'],
            ['suppliers', 'is_active'],
        ];

        foreach ($indexChecks as [$table, $column]) {
            $columns = Schema::getColumnListing($table);
            $this->assertContains(
                $column,
                $columns,
                "Kolom {$table}.{$column} harus tersedia"
            );
        }
    }

    /** Dokumen ERD dan data dictionary harus tersedia. */
    public function test_erd_and_data_dictionary_exist(): void
    {
        $this->assertFileExists(base_path('docs/database/ERD.md'));
        $this->assertFileExists(base_path('docs/database/DATA_DICTIONARY.md'));
    }

    /** Semua tabel PHASE 1 harus tersedia setelah migrate. */
    public function test_migration_creates_all_phase1_tables(): void
    {
        $this->assertTrue(Schema::hasTable('departments'));
        $this->assertTrue(Schema::hasTable('roles'));
        $this->assertTrue(Schema::hasTable('permissions'));
        $this->assertTrue(Schema::hasTable('items'));
        $this->assertTrue(Schema::hasTable('warehouses'));
        $this->assertTrue(Schema::hasTable('suppliers'));
        $this->assertTrue(Schema::hasTable('projects'));
        $this->assertTrue(Schema::hasTable('audit_logs'));
    }

    /** Kolom department_id harus ada di tabel users. */
    public function test_users_has_department_id_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('users', 'department_id'),
            "Kolom 'department_id' harus ada di tabel users."
        );
    }

    /** Seeder harus mengisi data referensi dasar. */
    public function test_seeder_populates_reference_data(): void
    {
        $this->seed();

        $this->assertGreaterThan(0, \App\Models\Department::count(), 'Departments harus di-seed.');
        $this->assertGreaterThan(0, \App\Models\Role::count(), 'Roles harus di-seed.');
        $this->assertGreaterThan(0, \App\Models\Permission::count(), 'Permissions harus di-seed.');
        $this->assertGreaterThan(0, \App\Models\Unit::count(), 'Units harus di-seed.');
        $this->assertGreaterThan(0, \App\Models\ItemCategory::count(), 'Item Categories harus di-seed.');
        $this->assertGreaterThan(0, \App\Models\Warehouse::count(), 'Warehouses harus di-seed.');
        $this->assertGreaterThan(0, \App\Models\Supplier::count(), 'Suppliers harus di-seed.');
        $this->assertGreaterThan(0, \App\Models\Project::count(), 'Projects harus di-seed.');
        $this->assertGreaterThan(0, \App\Models\User::count(), 'Users harus di-seed.');
    }

    /** Admin user harus memiliki role super_admin. */
    public function test_admin_user_has_super_admin_role(): void
    {
        $this->seed();

        $admin = \App\Models\User::where('email', 'admin@primainventory.co.id')->first();
        $this->assertNotNull($admin, 'Admin user harus ada.');
        $this->assertTrue(
            $admin->hasRole('super_admin'),
            'Admin user harus memiliki role super_admin.'
        );
    }

    /** Items harus di-seed dengan relasi category dan unit yang valid. */
    public function test_items_seeded_with_valid_relations(): void
    {
        $this->seed();

        $items = \App\Models\Item::all();
        $this->assertGreaterThan(0, $items->count(), 'Minimal 1 item harus di-seed.');

        foreach ($items as $item) {
            $this->assertNotNull($item->category_id, "Item {$item->code} harus punya category_id.");
            $this->assertNotNull($item->default_unit_id, "Item {$item->code} harus punya default_unit_id.");
            $this->assertNotNull($item->category, "Item {$item->code} harus punya relasi category.");
            $this->assertNotNull($item->defaultUnit, "Item {$item->code} harus punya relasi defaultUnit.");
        }
    }

    /** Unique constraint pada departments.code harus aktif. */
    public function test_unique_constraint_on_department_code(): void
    {
        DB::table('departments')->insert([
            'code' => 'UNIQ001',
            'name' => 'Test Unique',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('departments')->insert([
            'code' => 'UNIQ001',
            'name' => 'Test Unique 2',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Foreign key constraint pada items.category_id harus aktif. */
    public function test_foreign_key_constraint_on_items(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('items')->insert([
            'code' => 'TEST001',
            'name' => 'Test Item',
            'category_id' => 99999,
            'default_unit_id' => 99999,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

