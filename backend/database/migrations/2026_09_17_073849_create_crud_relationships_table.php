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
        Schema::create('crud_relationships', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('module_id');
            $table->enum('relation_type', [
                'hasOne',
                'hasMany',
                'belongsTo',
                'belongsToMany',
            ]);
            $table->unsignedBigInteger('related_module_id')->nullable();
            $table->string('foreign_key', 100)->nullable();
            $table->string('local_key', 100)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('module_id')
                ->references('id')
                ->on('crud_modules')
                ->cascadeOnDelete();

            $table->foreign('related_module_id')
                ->references('id')
                ->on('crud_modules')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crud_relationships');
    }
};
