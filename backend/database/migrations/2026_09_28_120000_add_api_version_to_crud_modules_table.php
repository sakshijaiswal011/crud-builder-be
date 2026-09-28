<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crud_modules', function (Blueprint $table) {
            $table->string('api_version', 20)->default('v1')->after('api_prefix');
        });
    }

    public function down(): void
    {
        Schema::table('crud_modules', function (Blueprint $table) {
            $table->dropColumn('api_version');
        });
    }
};
