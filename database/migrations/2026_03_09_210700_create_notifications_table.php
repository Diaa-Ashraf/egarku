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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->enum('type', [
                'new_contact',           // طلب تواصل جديد
                'ad_approved',           // الموافقة على إعلان
                'ad_rejected',           // رفض إعلان
                'subscription_expiring', // اقتراب الاشتراك من الانتهاء
                'new_review',            // تقييم جديد
            ]);

            $table->string('title');
            $table->text('body')->nullable();

            // ربط الإشعار بالعنصر المرتبط (إعلان، اشتراك، تقييم...)
            $table->nullableMorphs('related'); // related_id + related_type

            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
