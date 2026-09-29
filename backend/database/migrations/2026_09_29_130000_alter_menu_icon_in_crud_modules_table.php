<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crud_modules', function (Blueprint $table) {
            $table->text('menu_icon')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('crud_modules', function (Blueprint $table) {
            $table->string('menu_icon', 100)->nullable()->change();
        });
    }
};
