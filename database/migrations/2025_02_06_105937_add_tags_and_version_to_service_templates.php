<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('service_templates', function (Blueprint $table) {
        $table->integer('version')->default(1)->after('is_active');
        $table->json('tags')->nullable()->after('version');
        $table->softDeletes();
    });
}

public function down()
{
    Schema::table('service_templates', function (Blueprint $table) {
        $table->dropColumn(['version', 'tags']);
        $table->dropSoftDeletes();
    });
}

};
