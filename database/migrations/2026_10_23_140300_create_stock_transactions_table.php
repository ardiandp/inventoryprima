<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_receipt_id')->constrained('purchase_receipts')->nullOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('production_order_id')->constrained('production_orders')->nullOnDelete();
            $table->string('display_number')->unique();
            $table->decimal('accepted_quantity', 15, 2)->default(0);
            $table->decimal('returned_quantity', 15, 2)->default(0);
            $table->decimal('issue_quantity', 15, 2)->default(0);
            $table->decimal('remaining_quantity', 15, 2)->default(0);
            $table->enum('status', ['draft', 'posted', 'cancelled']);
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->constrained('users')->nullOnDelete();
            $table->timestamp('performed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['display_number']);
            $table->index('status');
            $table->index('purchase_receipt_id');
            $table->index('purchase_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};