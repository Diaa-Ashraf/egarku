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
        Schema::create('vendor_profiles', function (Blueprint $table) {

      $table->id();
      $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
     $table->foreignId('marketplace_id')->constrained()->onDelete('cascade');

    // نوع المعلن: فرد أو شركة - بس
    $table->enum('vendor_type', ['individual', 'company'])->default('individual');
    $table->string('display_name');
    $table->string('company_name')->nullable();
    $table->string('work_phone', 20)->nullable();
    $table->string('whatsapp', 20)->nullable();
    $table->text('bio')->nullable();
    $table->string('website')->nullable();
    $table->boolean('is_verified')->default(false);
    $table->string('verification_doc')->nullable();
    $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->decimal('avg_rating', 3, 2)->default(0.00);
    $table->unsignedInteger('reviews_count')->default(0);   
    $table->timestamps();
    // logo → Spatie Media Library
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_profiles');
    }
};
