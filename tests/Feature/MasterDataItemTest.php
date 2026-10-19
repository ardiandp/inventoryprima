<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\ItemUnitConversion;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;

class MasterDataItemTest extends MasterDataTest
{
    public function test_item_store_creates_with_active_master(): void
    {
        $this->actingAsMaster();
        $cat = $this->makeCategory();
        $unit = $this->makeUnit();

        $this->post(route('master.items.store'), [
            'code' => 'brg-001',
            'name' => 'Semen 50kg',
            'category_id' => $cat->id,
            'default_unit_id' => $unit->id,
            'min_stock' => 10,
            'max_stock' => 100,
        ])->assertRedirect(route('master.items.index'));

        $this->assertDatabaseHas('items', ['code' => 'BRG-001', 'name' => 'Semen 50kg']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'create', 'model_type' => Item::class]);
    }

    public function test_item_code_must_be_unique(): void
    {
        $this->actingAsMaster();
        $item = $this->makeItem(['code' => 'BRG999']);

        $this->post(route('master.items.store'), [
            'code' => 'BRG999',
            'name' => 'Duplikat',
            'category_id' => $item->category_id,
            'default_unit_id' => $item->default_unit_id,
            'min_stock' => 0,
        ])->assertSessionHasErrors('code');
    }

    public function test_item_rejects_inactive_category_and_unit(): void
    {
        $this->actingAsMaster();
        $catOff = $this->makeCategory(['is_active' => false]);
        $unitOff = $this->makeUnit(['is_active' => false]);
        $goodCat = $this->makeCategory();
        $goodUnit = $this->makeUnit();

        $this->post(route('master.items.store'), [
            'code' => $this->nextCode('BRG'),
            'name' => 'X',
            'category_id' => $catOff->id,
            'default_unit_id' => $goodUnit->id,
            'min_stock' => 0,
        ])->assertSessionHasErrors('category_id');

        $this->post(route('master.items.store'), [
            'code' => $this->nextCode('BRG'),
            'name' => 'Y',
            'category_id' => $goodCat->id,
            'default_unit_id' => $unitOff->id,
            'min_stock' => 0,
        ])->assertSessionHasErrors('default_unit_id');
    }

    public function test_item_search_filter_and_pagination(): void
    {
        $this->actingAsMaster();
        $cat = $this->makeCategory();
        $unit = $this->makeUnit();

        foreach (range(1, 16) as $i) {
            Item::create([
                'code' => 'BRG-PG'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'name' => 'Barang Paging '.$i,
                'category_id' => $cat->id,
                'default_unit_id' => $unit->id,
                'min_stock' => 0,
                'is_active' => $i % 2 === 0,
            ]);
        }

        $this->get('/master/items?q=Paging')->assertOk()->assertSee('Barang Paging 1');
        $this->get('/master/items?status=inactive')->assertOk()->assertSee('Barang Paging 1');
        $this->get('/master/items?page=2')->assertOk();
    }

    public function test_item_with_conversion_cannot_be_deleted(): void
    {
        $this->actingAsMaster();
        $item = $this->makeItem();
        $toUnit = $this->makeUnit();
        ItemUnitConversion::create([
            'item_id' => $item->id,
            'from_unit_id' => $item->default_unit_id,
            'to_unit_id' => $toUnit->id,
            'conversion_factor' => 12,
        ]);

        $this->delete(route('master.items.destroy', $item))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('items', ['id' => $item->id, 'deleted_at' => null]);
    }

    public function test_item_unused_soft_deleted_not_hard_deleted(): void
    {
        $this->actingAsMaster();
        $item = $this->makeItem();

        $this->delete(route('master.items.destroy', $item))
            ->assertRedirect(route('master.items.index'));

        $this->assertSoftDeleted('items', ['id' => $item->id]);
        $this->assertDatabaseHas('items', ['id' => $item->id]);
    }

    public function test_conversion_crud_and_unique_combination(): void
    {
        $this->actingAsMaster();
        $item = $this->makeItem();
        $toUnit = $this->makeUnit();

        $this->post(route('master.items.conversions.store', $item), [
            'from_unit_id' => $item->default_unit_id,
            'to_unit_id' => $toUnit->id,
            'conversion_factor' => 10,
        ])->assertRedirect(route('master.items.edit', $item));

        $this->assertDatabaseHas('item_unit_conversions', [
            'item_id' => $item->id,
            'from_unit_id' => $item->default_unit_id,
            'to_unit_id' => $toUnit->id,
        ]);

        $this->post(route('master.items.conversions.store', $item), [
            'from_unit_id' => $item->default_unit_id,
            'to_unit_id' => $toUnit->id,
            'conversion_factor' => 10,
        ])->assertSessionHasErrors('from_unit_id');

        $this->post(route('master.items.conversions.store', $item), [
            'from_unit_id' => $item->default_unit_id,
            'to_unit_id' => $item->default_unit_id,
            'conversion_factor' => 5,
        ])->assertSessionHasErrors('to_unit_id');

        $conv = ItemUnitConversion::where('item_id', $item->id)->first();
        $this->delete(route('master.items.conversions.destroy', [$item, $conv]))
            ->assertRedirect(route('master.items.edit', $item));
        $this->assertDatabaseMissing('item_unit_conversions', ['id' => $conv->id]);
    }
}
