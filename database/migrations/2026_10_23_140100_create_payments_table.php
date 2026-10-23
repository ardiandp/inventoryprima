<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('payment_method');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('paid_by')->constrained('users')->onDelete('set null');
            $table->dateTime('paid_at');
            $table->timestamps();
            $table->index(['invoice_id','paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
}