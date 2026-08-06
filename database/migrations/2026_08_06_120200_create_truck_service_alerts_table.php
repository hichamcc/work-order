<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Notes written by the nightly check for trucks that have driven past the
     * oil service interval. One open row per truck; it is resolved once a new
     * oil service is recorded.
     */
    public function up(): void
    {
        Schema::create('truck_service_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('truck_number')->index();
            $table->unsignedInteger('current_km');
            $table->unsignedInteger('last_service_km');
            $table->unsignedInteger('km_since_service');
            $table->string('km_source')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('flagged_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['truck_number', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('truck_service_alerts');
    }
};
