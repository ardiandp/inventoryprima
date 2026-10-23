<?php

use Illuminate\Database\Seeder;
use Faker\Factory as FakerFactory;

class PurchaseRequestSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create('id_ID');
        $faker->locale = 'id_ID';

        $itemCodes = ['B001','B002','B003','B004','B005','C001','C002','C003','C004','C005','P001','P002','P003','P004','P005'];
        $descriptions = ['Besi','Cat','Semen','Bata','Pasir','Split','Air','Krakatoa','Besi Siku','Pipa'];
        $units = ['pcs','kg','roll','kotak','ton','meter','liter'];
        $statuses = ['draft','submitted','under_review','sourcing','pending_approval','approved','revision_requested','rejected','cancelled'];

        for ($i = 1; $i <= 12; $i++) {
            $title = 'Permintaan Barang ' . $i;
            $description = 'Permintaan barang untuk kebutuhan proyek - ' . $i;
            $requestedBy = rand(1, 5);
            $pr = PurchaseRequest::create([
                'title' => $title,
                'description' => $description,
                'requested_by' => $requestedBy,
                'status' => $statuses[array_rand($statuses)],
                'source' => 'rpo',
            ]);
            $itemCount = rand(3, 5);
            for ($j = 0; $j < $itemCount; $j++) {
                $pr->items()->create([
                    'item_code' => $itemCodes[array_rand($itemCodes)],
                    'description' => $descriptions[array_rand($descriptions)],
                    'quantity' => rand(10, 500),
                    'unit' => $units[array_rand($units)],
                    'price' => rand(5000, 500000),
                ]);
            }
        }

        // Additional specific status records
        PurchaseRequest::create(['title' => 'Dibatalkan','description' => 'Revisi QC','requested_by' => 2,'status' => 'cancelled','source' => 'rpo']);
        PurchaseRequest::create(['title' => 'Submitted','description' => 'Proyek baru','requested_by' => 3,'status' => 'submitted','source' => 'rpo']);
        PurchaseRequest::create(['title' => 'Revision','description' => 'Survey lokasi','requested_by' => 4,'status' => 'revision_requested','source' => 'rpo']);
        PurchaseRequest::create(['title' => 'Draft','description' => 'Baru dibuat','requested_by' => 5,'status' => 'draft','source' => 'rpo']);
        PurchaseRequest::create(['title' => 'Sourcing','description' => 'Menunggu supplier','requested_by' => 1,'status' => 'sourcing','source' => 'rpo']);
        PurchaseRequest::create(['title' => 'Pending Approval','description' => 'Menunggu director','requested_by' => 2,'status' => 'pending_approval','source' => 'rpo']);
        PurchaseRequest::create(['title' => 'Approved','description' => 'Siap PO','requested_by' => 3,'status' => 'approved','source' => 'rpo']);
        PurchaseRequest::create(['title' => 'Approved 2','description' => 'Persiapan PO','requested_by' => 4,'status' => 'approved','source' => 'rpo']);
        PurchaseRequest::create(['title' => 'Approved 3','description' => 'Persiapan PO 2','requested_by' => 5,'status' => 'approved','source' => 'rpo']);

        $this->command->info('16 PR records created');
    }
}

use Illuminate\Database\Seeder;
use App\Models\PurchaseRequest;

class PurchaseRequestSeeder extends Seeder
{
    public function run(): void
    {
        //
    }
}