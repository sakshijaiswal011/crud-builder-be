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
        Schema::create('crud_forms_list', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('field_id');

            $table->string('form_input_type', 100);
            $table->string('form_label', 100);
            $table->string('form_placeholder', 100);
            $table->boolean('is_required')->default(false);
            $table->json('validation_rules');

            $table->string('list_label', 150)->nullable();
            $table->boolean('search_enabled')->default(true);
            $table->boolean('sorting_enabled')->default(true);
            $table->boolean('filtering_enabled')->default(true);
            $table->integer('width')->default(25);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('module_id')
                ->references('id')
                ->on('crud_modules')
                ->cascadeOnDelete();

            $table->foreign('field_id')
                ->references('id')
                ->on('crud_fields')
                ->cascadeOnDelete();

            $table->unique(['module_id', 'field_id'], 'uq_module_form_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crud_forms_list');
    }
};
