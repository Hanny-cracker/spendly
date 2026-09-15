<?php

namespace App\Data\AI;

readonly class AIInsightData
{
    /** @param array<int, string> $highlights @param array<int, string> $warnings @param array<int, array<string, mixed>> $recommendations */
    public function __construct(
        public string $summary,
        public int $healthScore,
        public array $highlights = [],
        public array $warnings = [],
        public array $recommendations = [],
    ) {}

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $recommendations = [];
        foreach (is_array($payload['recommendations'] ?? null) ? $payload['recommendations'] : [] as $recommendation) {
            if (! is_array($recommendation) || ! is_string($recommendation['title'] ?? null) || ! is_string($recommendation['description'] ?? null)) {
                continue;
            }
            $type = in_array($recommendation['type'] ?? null, ['saving', 'spending', 'budget', 'cashflow'], true) ? $recommendation['type'] : 'spending';
            $priority = in_array($recommendation['priority'] ?? null, ['low', 'medium', 'high'], true) ? $recommendation['priority'] : 'medium';
            $recommendations[] = ['title' => $recommendation['title'], 'description' => $recommendation['description'], 'type' => $type, 'priority' => $priority, 'estimated_impact' => is_string($recommendation['estimated_impact'] ?? null) ? $recommendation['estimated_impact'] : null];
        }

        return new self(
            is_string($payload['summary'] ?? null) ? $payload['summary'] : 'Your financial activity has been reviewed.',
            max(0, min(100, (int) ($payload['health_score'] ?? 0))),
            array_values(array_filter(is_array($payload['highlights'] ?? null) ? $payload['highlights'] : [], 'is_string')),
            array_values(array_filter(is_array($payload['warnings'] ?? null) ? $payload['warnings'] : [], 'is_string')),
            $recommendations,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['summary' => $this->summary, 'health_score' => $this->healthScore, 'highlights' => $this->highlights, 'warnings' => $this->warnings, 'recommendations' => $this->recommendations];
    }
}
