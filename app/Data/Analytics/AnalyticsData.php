<?php

namespace App\Data\Analytics;

use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

readonly class AnalyticsData
{
    /**
     * @param  array<string, mixed>  $reports
     * @param  array<string, mixed>  $insights
     */
    public function __construct(
        public CarbonInterface $startDate,
        public CarbonInterface $endDate,
        public array $reports,
        public array $insights,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'start_date' => $this->startDate->toDateString(),
            'end_date' => $this->endDate->toDateString(),
            'reports' => $this->normalize($this->reports),
            'insights' => $this->normalize($this->insights),
        ];
    }

    private function normalize(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof CarbonInterface) {
            return $value->toDateString();
        }

        if ($value instanceof Model) {
            return $this->normalize($value->toArray());
        }

        if ($value instanceof Collection) {
            return $this->normalize($value->all());
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $this->normalize($value->toArray());
        }

        if (is_array($value)) {
            return array_map(
                fn (mixed $item) => $this->normalize($item),
                $value
            );
        }

        return $value;
    }
}
