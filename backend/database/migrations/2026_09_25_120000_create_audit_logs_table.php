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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('module_id')->nullable();
            $table->string('event', 50);
            $table->string('auditable_type', 255)->nullable();
            $table->string('auditable_id', 255)->nullable();
            $table->string('table_name', 100)->nullable();
            $table->string('record_identifier', 255)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('changed_fields')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('request_method', 10)->nullable();
            $table->text('request_url')->nullable();
            $table->timestamps();

            $table->foreign('module_id')
                ->references('id')
                ->on('crud_modules')
                ->nullOnDelete();

            $table->index(['module_id', 'event']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('table_name');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
