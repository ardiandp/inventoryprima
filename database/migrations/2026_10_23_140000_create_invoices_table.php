<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->string('display_number')->unique();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->date('due_date');
            $table->enum('status', ['draft','sent','partial','paid','cancelled','void'])->default('draft');
            $table->string('payment_method')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
            $table->index(['status','due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
}