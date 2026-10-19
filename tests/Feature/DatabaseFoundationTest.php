<?php

namespace Tests\Feature;

use Tests\TestCase;
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
}

