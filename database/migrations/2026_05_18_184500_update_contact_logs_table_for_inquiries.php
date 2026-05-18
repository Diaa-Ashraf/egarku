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
        Schema::table('contact_logs', function (Blueprint $table) {
            // Change enum to string so we can support 'inquiry' or any future type safely
            $table->string('contact_type', 50)->change();
            
            // Add inquiry fields
            $table->text('message')->nullable()->after('contact_type');
            $table->boolean('wants_whatsapp_reply')->default(false)->after('message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_logs', function (Blueprint $table) {
            $table->dropColumn(['message', 'wants_whatsapp_reply']);
            // We can keep contact_type as string since it's compatible
        });
    }
};
