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
        Schema::create('crud_module_permissions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('module_id');
            $table->string('permission_name', 150);
            $table->string('action', 50);
            $table->boolean('enabled')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('module_id')
                ->references('id')
                ->on('crud_modules')
                ->cascadeOnDelete();

            $table->unique(['module_id', 'permission_name'], 'uq_module_permission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crud_module_permissions');
    }
};
