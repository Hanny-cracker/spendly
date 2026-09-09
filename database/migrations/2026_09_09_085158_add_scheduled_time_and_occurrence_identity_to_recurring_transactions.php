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
        Schema::table('recurring_transactions', function (Blueprint $table): void {
            $table->time('scheduled_time')->default('00:00:00')->after('start_date');
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->timestamp('scheduled_for')->nullable()->after('recurring_transaction_id');
            $table->unique(['recurring_transaction_id', 'scheduled_for'], 'transactions_recurring_occurrence_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropUnique('transactions_recurring_occurrence_unique');
            $table->dropColumn('scheduled_for');
        });

        Schema::table('recurring_transactions', function (Blueprint $table): void {
            $table->dropColumn('scheduled_time');
        });
    }
};
