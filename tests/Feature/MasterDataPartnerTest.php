<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;

class MasterDataPartnerTest extends MasterDataTest
{
    public function test_supplier_crud_unique_search_and_soft_delete(): void
    {
        $this->actingAsMaster();

        $this->post(route('master.suppliers.store'), [
            'code' => 'sup-01', 'name' => 'PT Maju Jaya',
        ])->assertRedirect(route('master.suppliers.index'));

        $this->assertDatabaseHas('suppliers', ['code' => 'SUP-01']);

        $this->post(route('master.suppliers.store'), [
            'code' => 'SUP-01', 'name' => 'Duplikat',
        ])->assertSessionHasErrors('code');

        $sup = Supplier::where('code', 'SUP-01')->first();
        $this->put(route('master.suppliers.update', $sup), [
            'code' => 'SUP-01', 'name' => 'PT Maju Jaya Update',
        ])->assertRedirect(route('master.suppliers.index'));

        $this->get('/master/suppliers?q=Maju')->assertOk()->assertSee('PT Maju Jaya Update');

        $this->delete(route('master.suppliers.destroy', $sup))
            ->assertRedirect(route('master.suppliers.index'));
        $this->assertSoftDeleted('suppliers', ['id' => $sup->id]);
        $this->assertDatabaseHas('suppliers', ['id' => $sup->id]);
    }

    public function test_warehouse_delete_blocked_when_has_locations(): void
    {
        $this->actingAsMaster();

        $this->post(route('master.warehouses.store'), [
            'code' => 'wh-01', 'name' => 'Gudang Pusat Test',
        ])->assertRedirect(route('master.warehouses.index'));

        $wh = Warehouse::where('code', 'WH-01')->first();
        $this->assertNotNull($wh);

        WarehouseLocation::create([
            'warehouse_id' => $wh->id,
            'code' => 'LOK-A',
            'name' => 'Rak A',
            'is_active' => true,
        ]);

        $this->delete(route('master.warehouses.destroy', $wh))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('warehouses', ['id' => $wh->id, 'deleted_at' => null]);
    }

    public function test_location_unique_per_warehouse_and_rejects_inactive_warehouse(): void
    {
        $this->actingAsMaster();
        $wh1 = Warehouse::create(['code' => $this->nextCode('WH'), 'name' => 'WH 1', 'is_active' => true]);
        $wh2 = Warehouse::create(['code' => $this->nextCode('WH'), 'name' => 'WH 2', 'is_active' => true]);
        $whOff = Warehouse::create(['code' => $this->nextCode('WH'), 'name' => 'WH Off', 'is_active' => false]);

        $this->post(route('master.locations.store'), [
            'warehouse_id' => $wh1->id, 'code' => 'rak-01', 'name' => 'Rak 01',
        ])->assertRedirect(route('master.locations.index'));

        $this->post(route('master.locations.store'), [
            'warehouse_id' => $wh1->id, 'code' => 'RAK-01', 'name' => 'Rak Duplikat',
        ])->assertSessionHasErrors('code');

        $this->post(route('master.locations.store'), [
            'warehouse_id' => $wh2->id, 'code' => 'RAK-01', 'name' => 'Rak 01 WH2',
        ])->assertRedirect(route('master.locations.index'));

        $this->post(route('master.locations.store'), [
            'warehouse_id' => $whOff->id, 'code' => 'RAK-99', 'name' => 'Rak X',
        ])->assertSessionHasErrors('warehouse_id');

        $this->get('/master/locations?warehouse_id='.$wh1->id)->assertOk()->assertSee('Rak 01');
    }

    public function test_project_crud_unique_and_soft_delete(): void
    {
        $this->actingAsMaster();

        $this->post(route('master.projects.store'), [
            'code' => 'prj-01', 'name' => 'Proyek A',
            'start_date' => '2026-01-01', 'end_date' => '2026-12-31',
        ])->assertRedirect(route('master.projects.index'));

        $prj = Project::where('code', 'PRJ-01')->first();
        $this->assertNotNull($prj);

        $this->post(route('master.projects.store'), [
            'code' => 'PRJ-01', 'name' => 'Duplikat',
        ])->assertSessionHasErrors('code');

        $this->delete(route('master.projects.destroy', $prj))
            ->assertRedirect(route('master.projects.index'));
        $this->assertSoftDeleted('projects', ['id' => $prj->id]);
        $this->assertDatabaseHas('projects', ['id' => $prj->id]);
    }
}
