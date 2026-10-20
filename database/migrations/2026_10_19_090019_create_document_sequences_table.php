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
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50);
            $table->string('prefix', 20);
            // Periode (mis. '202610'). String kosong = tanpa periode.
            $table->string('period', 20)->default('');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();

            // Unik per kombinasi key + period (AGENTS.md: nomor dokumen bulanan).
            $table->unique(['key', 'period'], 'document_sequences_key_period_unique');
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
