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
        foreach (['transactions', 'budgets', 'recurring_transactions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropForeign(['category_id']);
                $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });
        Schema::table('recurring_transactions', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });
        Schema::table('budgets', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });
    }
};
