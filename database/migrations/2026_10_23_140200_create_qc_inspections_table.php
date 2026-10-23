<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQcInspectionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('qc_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained('purchase_receipts')->onDelete('cascade');
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->string('display_number')->unique();
            $table->text('observations')->nullable();
            $table->decimal('accepted_quantity', 15, 2)->default(0);
            $table->decimal('rejected_quantity', 15, 2)->default(0);
            $table->enum('status', ['draft','received','pending_inspection','partially_accepted','accepted','rejected','posted'])->default('draft');
            $table->foreignId('inspected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('inspected_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['status','inspected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_inspections');
    }
}