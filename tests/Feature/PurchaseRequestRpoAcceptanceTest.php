<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use RefreshDatabase;
use Tests\TestCase;

class PurchaseRequestRpoAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_view_rpo_index()
    {
        $this->actingAs($this->adminUser())
            ->get('/rpo')
            ->assertStatus(200)
            ->assertSee('Permintaan Pembelian (RPO)');
    }

    /** @test */
    public function a_user_can_create_rpo()
    {
        $this->actingAs($this->adminUser())
            ->get('/rpo/create')
            ->assertStatus(200)
            ->assertSee('Buat RPO Baru');
    }

    /** @test */
    public function a_user_can_view_rpo_detail()
    {
        $pr = PurchaseRequest::factory()->create();

        $this->actingAs($this->adminUser())
            ->get('/rpo/' . $pr->id)
            ->assertStatus(200)
            ->assertSee($pr->display_number);
    }

    /** @test */
    public function a_user_cannot_submit_rpo_without_items()
    {
        $pr = PurchaseRequest::factory()->create();

        $this->actingAs($this->adminUser())
            ->post('/rpo/' . $pr->id . '/submit')
            ->assertSessionHasErrors();
    }

    /** @test */
    public function a_user_cancel_rpo()
    {
        $pr = PurchaseRequest::factory()->status('draft')->create();

        $this->actingAs($this->adminUser())
            ->post('/rpo/' . $pr->id . '/cancel')
            ->assertRedirect('/rpo');

        $this->assertEquals('cancelled', $pr->refresh()->status);
    }

    /** @test */
    public function a_user_cannot_cancel_approved_rpo()
    {
        $pr = PurchaseRequest::factory()->status('approved')->create();

        $this->actingAs($this->adminUser())
            ->post('/rpo/' . $pr->id . '/cancel')
            ->assertSessionHasErrors('cannot_cancel_approved');
    }

    /** @test */
    public function status_label_works_correctly()
    {
        $this->seed();

        $statuses = [
            'draft' => 'Draft',
            'submitted' => 'Diajukan',
            'under_review' => 'Sedang Direview',
            'revision_requested' => 'Perlu Revisi',
            'sourcing' => 'Sourcing',
            'pending_approval' => 'Menunggu Approval',
            'approved' => 'Disetujui',
            'partially_ordered' => 'Sebagian Dipesan',
            'fulfilled' => 'Terpenuhi',
            'closed' => 'Ditutup',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];

        foreach ($statuses as $code => $label) {
            $pr = PurchaseRequest::factory()->status($code)->create();
            $this->assertEquals($label, $pr->statusLabel());
        }
    }

    protected function adminUser(): User
    {
        return User::factory()->superAdmin()->create();
    }
}