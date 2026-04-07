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
        Schema::create('vendor_subscriptions', function (Blueprint $table) {

             $table->id();
             $table->foreignId('vendor_profile_id')->constrained()->onDelete('cascade');
             $table->foreignId('plan_id')->constrained()->onDelete('cascade');
             $table->timestamp('starts_at')->useCurrent();
             $table->timestamp('expires_at')->nullable();
             $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
             $table->timestamps();

             $table->index(['vendor_profile_id', 'status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_subscriptions');
    }
};
