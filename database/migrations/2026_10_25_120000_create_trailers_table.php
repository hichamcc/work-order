<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trailers mirrored from WTTsystem.
     *
     * Mapon does not track trailers, so there is no mileage to measure against;
     * a trailer is flagged for service by hand instead.
     */
    public function up(): void
    {
        Schema::create('trailers', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique();
            $table->boolean('needs_service')->default(false);
            $table->text('service_note')->nullable();
            $table->timestamp('flagged_at')->nullable();
            $table->foreignId('flagged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('last_serviced_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['needs_service', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trailers');
    }
};
