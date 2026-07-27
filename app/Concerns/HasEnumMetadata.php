<?php

declare(strict_types=1);

namespace App\Concerns;

trait HasEnumMetadata
{
    abstract protected function metadata(): array;


    /**
     * Display label.
     */
    public function label(): string
    {
        return $this->metadata()['label'];
    }


    /**
     * Display color.
     */
    public function color(): string
    {
        return $this->metadata()['color'] ?? 'gray';
    }


    /**
     * Icon name.
     */
    public function icon(): ?string
    {
        return $this->metadata()['icon'] ?? null;
    }


    /**
     * Description text.
     */
    public function description(): ?string
    {
        return $this->metadata()['description'] ?? null;
    }


    /**
     * Extra badge text.
     */
    public function badge(): ?string
    {
        return $this->metadata()['badge'] ?? null;
    }
}