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
        Schema::table('crud_modules', function (Blueprint $table) {
            $table->string('target_project_path')->nullable()->after('status')->comment('Absolute path to target project');
            $table->string('domain_folder')->nullable()->after('target_project_path')->comment('DDD Module folder name e.g. Posts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crud_modules', function (Blueprint $table) {
            $table->dropColumn(['target_project_path', 'domain_folder']);
        });
    }
};
