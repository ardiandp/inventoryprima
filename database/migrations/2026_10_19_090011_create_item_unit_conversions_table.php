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
        Schema::create('item_unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('from_unit_id')->constrained('units')->onDelete('restrict');
            $table->foreignId('to_unit_id')->constrained('units')->onDelete('restrict');
            $table->decimal('conversion_factor', 15, 6);
            $table->timestamps();

            $table->unique(['item_id', 'from_unit_id', 'to_unit_id']);
            $table->index('item_id');
            $table->index('from_unit_id');
            $table->index('to_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_unit_conversions');
    }
};