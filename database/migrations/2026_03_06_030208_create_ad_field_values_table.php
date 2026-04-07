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
        Schema::create('ad_field_values', function (Blueprint $table) {

             $table->id();
             $table->foreignId('ad_id')->constrained()->onDelete('cascade');
             $table->foreignId('field_id')->constrained('marketplace_fields')->onDelete('cascade');
             $table->string('value');

             $table->unique(['ad_id', 'field_id']);
             $table->index(['field_id', 'value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_field_values');
    }
};
