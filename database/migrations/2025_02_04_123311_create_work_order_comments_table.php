<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('work_order_comments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained();
        $table->text('comment');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_comments');
    }
};
