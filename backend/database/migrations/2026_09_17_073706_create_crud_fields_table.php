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
        Schema::create('crud_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id');
            $table->foreign('module_id', 'fk_crud_fields_module')
                ->references('id')
                ->on('crud_modules')
                ->cascadeOnDelete();

            $table->string('field_name', 100);
            $table->string('type', 50);
            $table->integer('length')->nullable();
            $table->boolean('nullable')->default(false);
            $table->text('default_value')->nullable();
            // auto_increment , is_primary
            $table->boolean('is_unique')->default(false);
            $table->boolean('is_indexed')->default(false);
            $table->text('comment')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['module_id', 'field_name'], 'uq_module_field');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crud_fields');
    }
};
