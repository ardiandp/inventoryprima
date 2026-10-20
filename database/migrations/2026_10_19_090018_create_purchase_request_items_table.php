<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('unit_id')->constrained('units')->onDelete('restrict');
            $table->decimal('quantity', 15, 6)->default(0);
            $table->decimal('unit_price', 15, 6)->nullable();
            $table->decimal('minimum_term', 15, 6)->nullable();
            $table->text('specification')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'revision_requested'])->default('pending');
            $table->string('rejection_reason', 500)->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('purchase_request_id');
            $table->index('item_id');
            $table->index(['purchase_request_id', 'item_id']);
            $table->index('unit_id');
            $table->index('status');
            $table->index(['purchase_request_id', 'status']);
            $table->unique(['purchase_request_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};
