<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscriptions') && ! Schema::hasColumn('subscriptions', 'payment_id')) {
            Schema::table('subscriptions', function (Blueprint $table): void {
                $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subscriptions') && Schema::hasColumn('subscriptions', 'payment_id')) {
            Schema::table('subscriptions', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('payment_id');
            });
        }
    }
};
