<?php

namespace App\Domains\Projects\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Baja',
            self::Medium => 'Media',
            self::High => 'Alta',
            self::Urgent => 'Urgente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => 'text-zinc-500',
            self::Medium => 'text-blue-500',
            self::High => 'text-amber-500',
            self::Urgent => 'text-red-500',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
