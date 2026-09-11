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
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->boolean('notify_recurring_24h')->default(true);
            $table->boolean('notify_recurring_6h')->default(true);
            $table->boolean('notify_recurring_success')->default(true);
            $table->boolean('notify_recurring_failure')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropColumn(['notify_recurring_24h', 'notify_recurring_6h', 'notify_recurring_success', 'notify_recurring_failure']);
        });
    }
};
