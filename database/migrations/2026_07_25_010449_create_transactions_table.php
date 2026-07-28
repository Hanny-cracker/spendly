<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {

            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            // the parent transation is used for recurring transactios.
            $table->foreignId('parent_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount',12,2);
            $table->enum('type',['income','expense']);
            $table->date('date');
            $table->enum('status',['completed','pending'])->default('completed');
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['user_id','date']);            
            $table->index(['user_id', 'type']);

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
