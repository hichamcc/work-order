<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->boolean('track_serials')->default(false)->after('is_active');
        });

        Schema::create('part_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->onDelete('cascade');
            $table->string('serial_number');
            $table->enum('status', ['in_stock', 'assigned', 'used'])->default('in_stock');
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['part_id', 'serial_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('part_instances');
        
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn('track_serials');
        });
    }
};