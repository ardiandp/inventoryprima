<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call([
        //     PurchaseRequestSeeder::class,
        //     PurchaseOrderSeeder::class,
        //     // Add other seeders here
        // ]);

        // For now, run just PurchaseRequest seeder
        $this->call(PurchaseRequestSeeder::class);
    }
}

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedDepartments();
        $this->seedRoles();
        $this->seedPermissions();
        $this->seedRolePermissions();
        $this->seedUnits();
        $this->seedItemCategories();
        $this->seedWarehouses();
        $this->seedSuppliers();
        $this->seedProjects();
        $this->seedAdminUser();
        $this->seedSampleItems();
        $this->seedPurchaseRequests();
    }
    protected function seedDepartments(): void
    {
        $names = [
            'Gudang' => 'Departemen gudang dan inventori',
            'Purchasing' => 'Departemen pembelian dan sourcing',
            'Produksi' => 'Departemen produksi dan proyek',
            'Finance' => 'Departemen keuangan dan pembayaran',
            'QC' => 'Departemen inspeksi kualitas',
            'Direktorat' => 'Departeman direksi dan approval',
            'IT / Admin' => 'Departemen sistem dan administrasi',
        ];

        foreach ($names as $name => $description) {
            Department::factory()->create([
                'name' => $name,
                'description' => $description,
            ]);
        }
    }

    protected function seedRoles(): void
    {
        $roles = [
            'super_admin' => 'Konfigurasi dan akun sistem',
            'warehouse' => 'RPO stok, penerimaan fisik, stok, transfer, pengeluaran, stock opname',
            'production' => 'RPO material proyek, lihat status, konfirmasi pemakaian/pengembalian',
            'purchasing' => 'Verifikasi RPO, sourcing, quotation, PO, koordinasi supplier dan retur',
            'director' => 'Approve/reject semua pengadaan, laporan manajemen',
            'finance' => 'Tagihan, jatuh tempo, pembayaran; tidak dapat mengubah saldo stok',
            'qc' => 'Inspeksi kualitas, bukti pemeriksaan, status accepted/rejected',
        ];

        foreach ($roles as $name => $description) {
            Role::factory()->create([
                'name' => $name,
                'description' => $description,
            ]);
        }
    }

    protected function seedPermissions(): void
    {
        $permissions = [
            'master.view' => 'master',
            'master.create' => 'master',
            'master.edit' => 'master',
            'master.delete' => 'master',
            'rpo.view' => 'rpo',
            'rpo.create' => 'rpo',
            'rpo.edit' => 'rpo',
            'rpo.delete' => 'rpo',
            'rpo.verify' => 'rpo',
            'po.view' => 'po',
            'po.create' => 'po',
            'po.edit' => 'po',
            'po.delete' => 'po',
            'po.approve' => 'po',
            'receiving.view' => 'receiving',
            'receiving.create' => 'receiving',
            'receiving.post' => 'receiving',
            'qc.view' => 'qc',
            'qc.inspect' => 'qc',
            'inventory.view' => 'inventory',
            'inventory.adjust' => 'inventory',
            'finance.view' => 'finance',
            'finance.create' => 'finance',
            'finance.edit' => 'finance',
            'report.view' => 'report',
            'user.manage' => 'system',
            'role.manage' => 'system',
            'audit.view' => 'system',
        ];

        foreach ($permissions as $name => $module) {
            Permission::factory()->create([
                'name' => $name,
                'module' => $module,
            ]);
        }
    }

    protected function seedRolePermissions(): void
    {
        // Mapping permission berdasarkan role (PRD bagian 3)
        $mapping = [
            'super_admin' => ['*'], // semua permission
            'warehouse' => [
                'master.view', 'rpo.view', 'rpo.create', 'rpo.edit',
                'receiving.view', 'receiving.create', 'receiving.post',
                'inventory.view', 'inventory.adjust',
                'report.view',
            ],
            'production' => [
                'master.view', 'rpo.view', 'rpo.create', 'rpo.edit',
                'inventory.view', 'report.view',
            ],
            'purchasing' => [
                'master.view', 'master.create', 'master.edit',
                'rpo.view', 'rpo.verify',
                'po.view', 'po.create', 'po.edit',
                'receiving.view',
                'inventory.view',
                'report.view',
            ],
            'director' => [
                'master.view', 'rpo.view', 'po.view', 'po.approve',
                'receiving.view', 'qc.view',
                'inventory.view', 'finance.view',
                'report.view',
            ],
            'finance' => [
                'master.view', 'po.view',
                'finance.view', 'finance.create', 'finance.edit',
                'inventory.view', // hanya lihat, TIDAK dapat inventory.adjust
                'report.view',
            ],
            'qc' => [
                'master.view', 'rpo.view', 'po.view',
                'receiving.view',
                'qc.view', 'qc.inspect',
                'inventory.view',
                'report.view',
            ],
        ];

        foreach ($mapping as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->first();
            if (! $role) {
                continue;
            }

            if ($permissionNames === ['*']) {
                $allPermissions = Permission::pluck('id');
                $role->permissions()->sync($allPermissions);
            } else {
                $permIds = Permission::whereIn('name', $permissionNames)->pluck('id');
                $role->permissions()->sync($permIds);
            }
        }
    }




    protected function seedUnits(): void
    {
        $units = [
            ['PCS', 'Pcs', 'pcs'],
            ['BOX', 'Box', 'box'],
            ['KG', 'Kg', 'kg'],
            ['LTR', 'Liter', 'L'],
            ['MTR', 'Meter', 'm'],
            ['SET', 'Set', 'set'],
        ];

        foreach ($units as [$code, $name, $symbol]) {
            Unit::factory()->create([
                'code' => $code,
                'name' => $name,
                'symbol' => $symbol,
                'is_active' => true,
            ]);
        }
    }

    protected function seedItemCategories(): void
    {
        $categories = [
            'Bahan Baku',
            'Barang Jadi',
            'Peralatan',
            'Consumable',
            'Sparepart',
            'Packaging',
            'MRO',
        ];

        foreach ($categories as $name) {
            ItemCategory::factory()->create([
                'name' => $name,
                'is_active' => true,
            ]);
        }
    }

    protected function seedWarehouses(): void
    {
        $warehouses = [
            'Gudang Pusat',
            'Gudang Cabang A',
            'Gudang Produksi',
            'Gudang Karantina',
        ];

        foreach ($warehouses as $name) {
            Warehouse::factory()->create([
                'name' => $name,
                'is_active' => true,
            ]);
        }
    }

    protected function seedSuppliers(): void
    {
        Supplier::factory()->create([
            'name' => 'PT Supplier Jaya',
            'contact_person' => 'Budi Santoso',
            'email' => 'budi@supplierjaya.co.id',
            'phone' => '021-12345678',
            'address' => 'Jakarta, Indonesia',
            'tax_id' => '01.234.567.8-901.234',
            'is_active' => true,
        ]);
        Supplier::factory()->create([
            'name' => 'CV Toko Abadi',
            'contact_person' => 'Siti Rahayu',
            'email' => 'siti@tokoabadi.co.id',
            'phone' => '021-87654321',
            'address' => 'Bogor, Indonesia',
            'tax_id' => '02.345.678.9-012.345',
            'is_active' => true,
        ]);
    }

    protected function seedProjects(): void
    {
        Project::factory()->create([
            'name' => 'Proyek Pabrik Baru',
            'description' => 'Proyek pembangunan pabrik produksi baru',
            'is_active' => true,
        ]);
        Project::factory()->create([
            'name' => 'Proyek Expansi Gudang',
            'description' => 'Proyek ekspansi kapasitas gudang',
            'is_active' => true,
        ]);
    }

    protected function seedAdminUser(): void
    {
        $adminDept = Department::where('name', 'Direktorat')->first();
        $adminUser = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@primainventory.co.id',
            'password' => bcrypt('password'),
            'department_id' => $adminDept ? $adminDept->id : null,
        ]);
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $adminUser->roles()->attach($superAdminRole->id);
        }
    }

    protected function seedSampleItems(): void
    {
        // Ambil ID kategori berdasarkan nama (urutan seed)
        $sparepartCat = ItemCategory::where('name', 'Sparepart')->first();
        $consumableCat = ItemCategory::where('name', 'Consumable')->first();

        $pcsUnit = Unit::where('code', 'PCS')->first();
        $kgUnit = Unit::where('code', 'KG')->first();

        // Pastikan kategori dan unit tersedia
        if ($sparepartCat && $pcsUnit) {
            Item::factory()->create([
                'code' => 'BRG00001',
                'name' => 'Bearing 6205',
                'category_id' => $sparepartCat->id,
                'default_unit_id' => $pcsUnit->id,
                'min_stock' => 10,
                'max_stock' => 100,
                'requires_qc' => true,
                'is_active' => true,
            ]);
            Item::factory()->create([
                'code' => 'BRG00002',
                'name' => 'Bolt M10x50',
                'category_id' => $sparepartCat->id,
                'default_unit_id' => $pcsUnit->id,
                'min_stock' => 50,
                'max_stock' => 500,
                'requires_qc' => false,
                'is_active' => true,
            ]);
        }
        if ($consumableCat && $kgUnit) {
            Item::factory()->create([
                'code' => 'BRG00003',
                'name' => 'Lem Inggris',
                'category_id' => $consumableCat->id,
                'default_unit_id' => $kgUnit->id,
                'min_stock' => 5,
                'max_stock' => 50,
                'requires_qc' => false,
                'is_active' => true,
            ]);
        }
    }
    protected function seedPurchaseRequests(): void
    {
        $warehouseRole = Role::where('name', 'warehouse')->first();
        $productionRole = Role::where('name', 'production')->first();

        $warehouseUser = User::factory()->create([
            'name' => 'Warehouse Staff',
            'email' => 'warehouse@example.com',
            'password' => bcrypt('password'),
        ]);
        if ($warehouseRole) {
            $warehouseUser->roles()->attach($warehouseRole->id);
        }

        $productionUser = User::factory()->create([
            'name' => 'Production Staff',
            'email' => 'production@example.com',
            'password' => bcrypt('password'),
        ]);
        if ($productionRole) {
            $productionUser->roles()->attach($productionRole->id);
        }

        $purchasingUser = User::factory()->create([
            'name' => 'Purchasing Staff',
            'email' => 'purchasing@example.com',
            'password' => bcrypt('password'),
        ]);
        if ($purchasingRole) {
            $purchasingUser->roles()->attach($purchasingRole->id);
        }

        $directorUser = User::factory()->create([
            'name' => 'Director',
            'email' => 'director@example.com',
            'password' => bcrypt('password'),
        ]);
        if ($directorRole) {
            $directorUser->roles()->attach($directorRole->id);
        }

        $financeUser = User::factory()->create([
            'name' => 'Finance Staff',
            'email' => 'finance@example.com',
            'password' => bcrypt('password'),
        ]);
        if ($financeRole) {
            $financeUser->roles()->attach($financeRole->id);
        }

        $qcUser = User::factory()->create([
            'name' => 'QC Staff',
            'email' => 'qc@example.com',
            'password' => bcrypt('password'),
        ]);
        if ($qcRole) {
            $qcUser->roles()->attach($qcRole->id);
        }

        $itemIds = Item::pluck('id')->toArray();
        $unitIds = Unit::pluck('id')->toArray();

        $warehouseRequest = PurchaseRequest::factory()->create([
            'display_number' => 'PR-' . strtoupper(uniqid()),
            'requested_by' => $warehouseUser->id,
            'request_type' => 'regular',
            'reason' => 'Permintaan stok barang perlengkapan gudang',
            'priority' => 'normal',
            'projected_date' => now()->addDays(7),
            'department_id' => Department::where('name', 'Gudang')->first()?->id,
            'status' => 'submitted',
        ]);

        if (! empty($itemIds) && ! empty($unitIds)) {
            PurchaseRequestItem::factory()->create([
                'purchase_request_id' => $warehouseRequest->id,
                'item_id' => $itemIds[0],
                'unit_id' => $unitIds[0],
                'quantity' => 200,
                'unit_price' => 1250,
                'specification' => 'Standar dkk.',
            ]);
            PurchaseRequestItem::factory()->create([
                'purchase_request_id' => $warehouseRequest->id,
                'item_id' => $itemIds[1],
                'unit_id' => $unitIds[0],
                'quantity' => 50,
                'unit_price' => 900,
                'specification' => 'Tebal ringan',
            ]);
        }

        $project = Project::where('name', 'Proyek Pabrik Baru')->first();
        $productionRequest = PurchaseRequest::factory()->create([
            'display_number' => 'PR-' . strtoupper(uniqid()),
            'requested_by' => $productionUser->id,
            'request_type' => 'project',
            'reason' => 'Material proyek pabrik baru',
            'priority' => 'high',
            'projected_date' => now()->addDays(21),
            'department_id' => Department::where('name', 'Produksi')->first()?->id,
            'project_id' => $project ? $project->id : null,
            'status' => 'under_review',
        ]);

        if (! empty($itemIds) && ! empty($unitIds)) {
            PurchaseRequestItem::factory()->create([
                'purchase_request_id' => $productionRequest->id,
                'item_id' => $itemIds[0],
                'unit_id' => $unitIds[0],
                'quantity' => 300,
                'unit_price' => 1250,
                'specification' => 'Untuk penggunaan proyek',
            ]);
            PurchaseRequestItem::factory()->create([
                'purchase_request_id' => $productionRequest->id,
                'item_id' => $itemIds[1],
                'unit_id' => $unitIds[0],
                'quantity' => 100,
                'unit_price' => 900,
                'specification' => 'Tambahan proyek',
            ]);
        }
    }
}
