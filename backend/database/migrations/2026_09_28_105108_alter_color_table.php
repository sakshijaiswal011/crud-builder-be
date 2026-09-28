<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('color', function (Blueprint $table) {
            if (! Schema::hasColumn('color', 'short_name')) {
                $table->string('short_name', 255);
            }
            if (! Schema::hasColumn('color', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('color', function (Blueprint $table) {
            if (Schema::hasColumn('color', 'short_name')) {
                $table->dropColumn('short_name');
            }
            if (Schema::hasColumn('color', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
