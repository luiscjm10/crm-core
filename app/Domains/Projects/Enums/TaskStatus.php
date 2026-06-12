<?php

namespace App\Domains\Projects\Enums;

enum TaskStatus: string
{
    case Planned = 'planned';
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planeada',
            self::Todo => 'Por hacer',
            self::InProgress => 'En progreso',
            self::Done => 'Completada',
            self::Cancelled => 'Cancelada',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
