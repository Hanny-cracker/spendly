<?php

declare(strict_types=1);

namespace App\Concerns;

use BackedEnum;
use Illuminate\Support\Collection;

/**
 * @method static array cases()
 * @method static ?static tryFrom(string $value)
 */
trait HasEnumHelpers
{
    /**
     * Get all enum values.
     */
    public static function values(): array
    {
        return collect(self::cases())
            ->map(fn (BackedEnum $case) => $case->value)
            ->toArray();
    }


    /**
     * Get all enum names.
     */
    public static function names(): array
    {
        return collect(self::cases())
            ->map(fn ($case) => $case->name)
            ->toArray();
    }


    /**
     * Get enum options for forms.
     *
     * Example:
     *
     * [
     *   'cash' => 'Cash',
     *   'bank' => 'Bank',
     * ]
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }


    /**
     * Get enum labels.
     */
    public static function labels(): array
    {
        return collect(self::cases())
            ->map(fn ($case) => $case->label())
            ->toArray();
    }


    /**
     * Get enum color map.
     */
    public static function colors(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->color(),
            ])
            ->toArray();
    }


    /**
     * Get enum icon map.
     */
    public static function icons(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->icon(),
            ])
            ->toArray();
    }


    /**
     * Check if enum contains value.
     */
    public static function has(string $value): bool
    {
        return self::tryFrom($value) !== null;
    }


    /**
     * Return random enum case.
     */
    public static function random(): static
    {
        return collect(self::cases())->random();
    }


    /**
     * Return cases as collection.
     */
    public static function collection(): Collection
    {
        return collect(self::cases());
    }
}