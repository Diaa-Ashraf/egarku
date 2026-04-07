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
        Schema::create('transactions', function (Blueprint $table) {
              $table->id();
              $table->foreignId('vendor_profile_id')->nullable()->constrained()->onDelete('set null');
              $table->foreignId('plan_id')->nullable()->constrained()->onDelete('set null');
              $table->foreignId('ad_id')->nullable()->constrained()->onDelete('set null');
              $table->foreignId('banner_id')->nullable()->constrained('banners')->onDelete('set null');
              $table->foreignId('featured_partner_id')->nullable()->constrained('featured_partners')->onDelete('set null');
              $table->decimal('amount', 10, 2);
              $table->enum('type', [
                     'subscription',       // شراء باقة
                     'featured',           // تمييز إعلان
                     'banner',             // شراء بانر
                     'featured_partner',   // ظهور في شركاؤنا المميزون
                 ]);
              $table->enum('method', ['vodafone_cash', 'instapay', 'fawry', 'cash', 'paymob'])->nullable();
              $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
              $table->string('reference')->nullable();
              $table->text('notes')->nullable();
              $table->timestamps();

              $table->index(['vendor_profile_id', 'status']);
              $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
