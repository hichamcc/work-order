<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
       public function up()
    {
        Schema::table('work_order_photos', function (Blueprint $table) {
            // First drop the existing foreign key constraint
            $table->dropForeign(['checklist_item_id']);
            
            // Then add the correct foreign key constraint
            $table->foreign('checklist_item_id')
                  ->references('id')
                  ->on('work_order_checklist_items')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('work_order_photos', function (Blueprint $table) {
            $table->dropForeign(['checklist_item_id']);
            
            // Restore the original foreign key if needed
            $table->foreign('checklist_item_id')
                  ->references('id')
                  ->on('checklist_items')
                  ->onDelete('cascade');
        });
    }
};
