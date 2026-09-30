<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crud_fields', function (Blueprint $table) {
            $table->text('enum_values')->nullable()->after('length');
        });
    }

    public function down(): void
    {
        Schema::table('crud_fields', function (Blueprint $table) {
            $table->dropColumn('enum_values');
        });
    }
};
