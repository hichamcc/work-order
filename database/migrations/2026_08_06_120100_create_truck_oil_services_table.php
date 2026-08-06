<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * History of oil services per truck: truck number + the KM it was done at.
     * One row per oil service so a full report can be pulled per truck.
     */
    public function up(): void
    {
        Schema::create('truck_oil_services', function (Blueprint $table) {
            $table->id();
            $table->string('truck_number')->index();
            $table->unsignedInteger('km');
            $table->string('km_source')->nullable(); // can, gps, manual
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('serviced_at');
            $table->timestamps();

            $table->index(['truck_number', 'km']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('truck_oil_services');
    }
};
