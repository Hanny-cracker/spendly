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
            $table->foreignId('default_expense_category_id')->nullable()->after('week_starts_on')->constrained('categories')->nullOnDelete();
            $table->foreignId('default_income_category_id')->nullable()->after('default_expense_category_id')->constrained('categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_income_category_id');
            $table->dropConstrainedForeignId('default_expense_category_id');
        });
    }
};
