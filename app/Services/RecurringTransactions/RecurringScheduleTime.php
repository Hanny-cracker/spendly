<?php

namespace App\Services\RecurringTransactions;

use App\Enums\RecurringFrequency;
use App\Models\UserPreference;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class RecurringScheduleTime
{
    public function timezoneForUser(int $userId): string
    {
        return (string) (UserPreference::query()
            ->where('user_id', $userId)
            ->value('timezone') ?? config('app.timezone', 'UTC'));
    }

    public function localToUtc(CarbonInterface $date, string $time, string $timezone): Carbon
    {
        return Carbon::parse($date->format('Y-m-d').' '.$time, $timezone)->utc();
    }

    public function toLocal(CarbonInterface $dateTime, string $timezone): Carbon
    {
        return Carbon::parse($dateTime)->setTimezone($timezone);
    }

    public function nextRunUtc(CarbonInterface $currentRunUtc, RecurringFrequency $frequency, string $timezone): Carbon
    {
        return $frequency->nextRun($this->toLocal($currentRunUtc, $timezone))->utc();
    }
}
