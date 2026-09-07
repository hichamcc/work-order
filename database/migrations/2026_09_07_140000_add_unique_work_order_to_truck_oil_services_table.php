<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * One oil service per work order.
     *
     * The application already checks before writing, but a double-submitted form
     * can pass that check twice before either row lands. The constraint makes the
     * database the final word.
     */
    public function up(): void
    {
        // Clear any duplicates a double submit has already created, keeping the
        // first row recorded for each work order.
        $duplicates = DB::table('truck_oil_services')
            ->select('work_order_id', DB::raw('MIN(id) as keep_id'))
            ->whereNotNull('work_order_id')
            ->groupBy('work_order_id')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('truck_oil_services')
                ->where('work_order_id', $duplicate->work_order_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('truck_oil_services', function (Blueprint $table) {
            $table->unique('work_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('truck_oil_services', function (Blueprint $table) {
            $table->dropUnique(['work_order_id']);
        });
    }
};
