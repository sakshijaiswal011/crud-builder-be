<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coffee', function (Blueprint $table) {
            if (! Schema::hasColumn('coffee', 'description')) {
                $table->string('description', 255);
            }
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('coffee', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropSoftDeletes();
        });
    }
};
