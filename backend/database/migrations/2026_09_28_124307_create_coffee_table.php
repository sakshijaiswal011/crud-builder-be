<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('coffee')) {
            return;
        }

        Schema::create('coffee', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->bigInteger('color_id');
            $table->string('desc', 255);
            $table->string('name', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coffee');
    }
};
