<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('original_path');
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('merchant')->nullable();
            $table->date('receipt_date')->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->decimal('tax', 15, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('detected_category')->nullable();
            $table->json('raw_extraction')->nullable();
            $table->string('status')->index();
            $table->text('failure_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
