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
        Schema::create('marketplace_fields', function (Blueprint $table) {
             $table->id();
            $table->foreignId('marketplace_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('key');
            $table->enum('type', ['text', 'number', 'select', 'boolean']);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['marketplace_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_fields');
    }
};
