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
        Schema::create('banners', function (Blueprint $table) {
             $table->id();
             $table->foreignId('marketplace_id')->nullable()->constrained()->onDelete('cascade');
             $table->foreignId('vendor_profile_id')->nullable()->constrained()->onDelete('cascade');
             $table->foreignId('city_id')->nullable()->constrained()->onDelete('set null');
             $table->string('image');
             $table->string('link')->nullable();
             $table->enum('position', ['homepage_top', 'homepage_mid', 'search_page', 'sidebar']);
             $table->decimal('price', 8, 2)->default(0);
             $table->timestamp('starts_at')->useCurrent();
$table->timestamp('expires_at')->nullable();
             $table->boolean('is_active')->default(true);
             $table->unsignedInteger('impressions')->default(0);
             $table->unsignedInteger('clicks')->default(0);
             $table->timestamps();

             $table->index(['is_active', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
