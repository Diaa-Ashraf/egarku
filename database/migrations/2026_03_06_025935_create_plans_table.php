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
        Schema::create('plans', function (Blueprint $table) {
            
    $table->id();
    $table->string('name');
    $table->integer('ad_limit')->default(3);
    $table->integer('featured_limit')->default(0);
    $table->boolean('has_banner')->default(false);
    $table->boolean('has_analytics')->default(false);
    $table->boolean('has_support')->default(false);
    $table->decimal('price', 8, 2)->default(0);
    $table->integer('duration_days')->default(30);
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
