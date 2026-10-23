<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rpo_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('po_number', 50)->unique();
            $table->enum('status', [
                'draft',
                'sent',
                'supplier_confirmed',
                'partially_received',
                'fully_received',
                'closed',
                'cancelled',
            ])->default('draft');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('delivery_address')->nullable();
            $table->date('delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('delivered_by')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('rpo_id');
            $table->index('supplier_id');
            $table->index('status');
            $table->index('po_number');

            // Foreign keys
            $table->foreign('rpo_id')->references('id')->on('purchase_requests')->on('cascade')->nullOnDelete();
            $table->foreign('supplier_id')->references('id')->on('users')->on('cascade')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->on('cascade')->nullOnDelete();
            $table->foreign('delivered_by')->references('id')->on('users')->on('cascade')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};