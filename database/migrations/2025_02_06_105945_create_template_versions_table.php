<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_template_id')->constrained()->onDelete('cascade');
            $table->integer('version');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('checklist_items');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at');
            $table->text('change_notes')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('template_versions');
    }
    
};
