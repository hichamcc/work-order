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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->onDelete('cascade');
            $table->foreignId('adjusted_by')->constrained('users');
            $table->integer('previous_stock');
            $table->integer('new_stock');
            $table->integer('adjustment_quantity');
            $table->enum('adjustment_type', ['add', 'remove']);
            $table->text('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
