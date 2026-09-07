<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alerts can come from two places: the nightly check, which measures from
     * the last recorded oil service, and the Mapon reminder import, which only
     * knows the odometer the next service falls due at. last_service_km becomes
     * nullable so imported rows can carry due_at_km instead.
     */
    public function up(): void
    {
        Schema::table('truck_service_alerts', function (Blueprint $table) {
            $table->unsignedInteger('due_at_km')->nullable()->after('last_service_km');
            $table->string('source')->default('service_history')->after('km_source'); // service_history, mapon_reminder
        });

        Schema::table('truck_service_alerts', function (Blueprint $table) {
            $table->unsignedInteger('last_service_km')->nullable()->change();
            $table->unsignedInteger('km_since_service')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('truck_service_alerts', function (Blueprint $table) {
            $table->dropColumn(['due_at_km', 'source']);
        });
    }
};
