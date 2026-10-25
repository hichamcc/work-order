<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The exported list writes one trailer several ways: "FJ4743",
     * "Ernø FJ 4743" and "FJ 47 43 DFDS" are the same vehicle. plate_number
     * holds the plate on its own so both systems can join on it, and label
     * keeps the text as the workshop wrote it.
     */
    public function up(): void
    {
        Schema::table('trailers', function (Blueprint $table) {
            $table->string('label')->nullable()->after('plate_number');
        });
    }

    public function down(): void
    {
        Schema::table('trailers', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
