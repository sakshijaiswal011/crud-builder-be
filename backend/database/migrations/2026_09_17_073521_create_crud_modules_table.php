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
        Schema::create('crud_modules', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->string('slug', 100)->unique('uq_crud_modules_slug');
            $table->string('table_name', 150)->unique('uq_crud_modules_table_name');
            $table->string('api_prefix', 150)->nullable();

            $table->string('menu_name', 100)->nullable();
            $table->string('menu_group', 100)->nullable();
            $table->string('menu_icon', 100)->nullable();

            $table->boolean('soft_delete')->default(false);
            $table->boolean('audit_log')->default(false);

            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');

            $table->boolean('generate_api_controller_routes')->default(true);
            $table->boolean('generate_api_resource')->default(true);
            $table->boolean('generate_policy')->default(true);
            $table->boolean('generate_frontend_views')->default(true);

            $table->boolean('list_pagination')->default(true);
            $table->unsignedSmallInteger('list_default_per_page')->default(25);
            $table->unsignedSmallInteger('list_max_per_page')->default(100);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crud_modules');
    }
};
