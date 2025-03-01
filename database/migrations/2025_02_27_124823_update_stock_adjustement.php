<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            // Add foreign key for part instances
            $table->foreignId('part_instance_id')->nullable()->after('part_id')
                  ->constrained()->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropForeign(['part_instance_id']);
            $table->dropColumn('part_instance_id');
        });
    }
};