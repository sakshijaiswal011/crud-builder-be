<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permission_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();

        DB::table('permission_actions')->insert([
            ['id' => 1, 'name' => 'View', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Update', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Delete', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Create', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_actions');
    }
};
