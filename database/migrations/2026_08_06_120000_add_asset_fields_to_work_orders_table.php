<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('asset_type')->nullable()->after('customer_id'); // truck, trailer, other
            $table->string('truck_number')->nullable()->after('asset_type');
            $table->unsignedInteger('truck_km')->nullable()->after('truck_number');
            $table->string('truck_km_source')->nullable()->after('truck_km'); // can, gps, manual
            $table->boolean('oil_service')->default(false)->after('truck_km_source');

            $table->index('asset_type');
            $table->index('truck_number');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex(['asset_type']);
            $table->dropIndex(['truck_number']);
            $table->dropColumn([
                'asset_type',
                'truck_number',
                'truck_km',
                'truck_km_source',
                'oil_service',
            ]);
        });
    }
};
