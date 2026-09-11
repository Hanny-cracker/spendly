<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('recurring_transactions')->update([
            'scheduled_time' => DB::raw('TIME(next_run)'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
