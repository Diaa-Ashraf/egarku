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
        Schema::create('vendor_usages', function (Blueprint $table) {
            
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('ads_count')->default(0);
            $table->unsignedInteger('featured_count')->default(0);
            $table->timestamps();

          $table->unique(['vendor_profile_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_usages');
    }
};
