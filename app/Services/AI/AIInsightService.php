<?php

namespace App\Services\AI;

use App\Contracts\AIProvider;
use App\Data\Report\DateRangeData;
use App\Enums\Feature;
use App\Models\AIInsight;
use App\Models\User;
use App\Services\Entitlements\EntitlementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AIInsightService
{
    public function __construct(
        private FinancialAnalysisService $analysisService,
        private AIProvider $provider,
        private EntitlementService $entitlements,
    ) {}

    public function latest(User $user): ?AIInsight
    {
        return AIInsight::query()->where('user_id', $user->id)->where('type', 'monthly_review')->latest('generated_at')->first();
    }

    public function generate(User $user, bool $force = false): AIInsight
    {
        $entitlement = $this->entitlements->check($user, Feature::AIInsights);
        if (! $entitlement->allowed) {
            throw new RuntimeException("You've used all {$entitlement->limit} free AI analyses this month.");
        }
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        $latest = $this->latest($user);
        $cooldown = (int) config('ai.insights.regeneration_cooldown_minutes', 60);

        if ($force && $latest?->generated_at?->greaterThan(now()->subMinutes($cooldown))) {
            throw new RuntimeException('Please wait before generating another review.');
        }

        $analysis = $this->analysisService->analyze($user, new DateRangeData($user->id, $start, $end));
        if ($analysis->transactionCount === 0) {
            throw new RuntimeException('Add some income and expenses before generating your financial review.');
        }
        $insightData = $this->provider->generateFinancialInsight($analysis->toArray());

        return DB::transaction(fn (): AIInsight => AIInsight::create([
            'user_id' => $user->id,
            'type' => 'monthly_review',
            'title' => 'Monthly financial review',
            'summary' => $insightData->summary,
            'health_score' => $insightData->healthScore,
            'data' => $insightData->toArray(),
            'period_start' => $start,
            'period_end' => $end,
            'generated_at' => now(),
        ]));
    }
}
