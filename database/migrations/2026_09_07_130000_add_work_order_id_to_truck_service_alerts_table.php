<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An alert can be assigned to a work order so the workshop can see, from the
     * oil service list, which trucks are already booked in.
     */
    public function up(): void
    {
        Schema::table('truck_service_alerts', function (Blueprint $table) {
            $table->foreignId('work_order_id')->nullable()->after('truck_number')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('truck_service_alerts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('work_order_id');
        });
    }
};
