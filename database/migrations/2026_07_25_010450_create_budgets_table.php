<?php

use App\Enums\BudgetPeriod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {

            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->string('period')->default(BudgetPeriod::Monthly->value);
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedTinyInteger('alert_percentage')->default(80);
            $table->boolean('is_active')->default(true);
            $table->unique(['user_id','category_id','start_date','end_date']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
