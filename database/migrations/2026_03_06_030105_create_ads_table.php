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
        Schema::create('ads', function (Blueprint $table) {

            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_profile_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('marketplace_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('area_id')->constrained()->onDelete('cascade');

            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->enum('price_unit', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();

            $table->enum('status', ['pending', 'active', 'rejected', 'expired'])->default('pending');
            $table->string('rejection_reason')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->boolean('is_for_expats')->default(false);

            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('contacts_count')->default(0);

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('address')->nullable();

            $table->timestamp('expires_at')->nullable();
            $table->date('available_from')->nullable(); // متاح من
            $table->date('available_to')->nullable();   // متاح لحد
            $table->timestamps();
            $table->softDeletes();

            $table->index(['marketplace_id', 'status']);
            $table->index(['area_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['is_featured', 'featured_until']);
            $table->index(['user_id', 'status']);
            $table->index(['vendor_profile_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
