<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_insights', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('title')->nullable();
            $table->text('summary')->nullable();
            $table->unsignedTinyInteger('health_score')->nullable();
            $table->json('data');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'type', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_insights');
    }
};
