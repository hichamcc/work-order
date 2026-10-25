<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trailer_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trailer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('serviced_at');
            $table->timestamps();

            $table->index(['trailer_id', 'serviced_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trailer_services');
    }
};
