<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->text('instructions')->nullable()->after('description');
            $table->json('additional_fields')->nullable()->after('is_required');
        });
    }
    
    public function down()
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->dropColumn(['instructions', 'additional_fields']);
        });
    }
};
