<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_transactions', function (Blueprint $table) {

            $table->id();
            $table->string('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('type', [
                'income', 
                'expense'
            ]);
            $table->enum('frequency', [
                'daily',
                'weekly',
                'monthly',
                'quarterly',
                'yearly'
            ]);
            $table->integer('interval')->default(1);
            $table->timestamp('start_date');
            $table->timestamp('next_run');
            $table->timestamp('end_date')->nullable();
            $table->enum('status', [
                'active',
                'paused',
                'completed'
            ])->default('active');
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
            $table->index('next_run');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
