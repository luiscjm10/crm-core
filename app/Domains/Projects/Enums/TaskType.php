<?php

namespace App\Domains\Projects\Enums;

enum TaskType: string
{
    case General = 'general';
    case Visit = 'visit';
    case Maintenance = 'maintenance';
    case Development = 'development';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Visit => 'Visita',
            self::Maintenance => 'Mantenimiento',
            self::Development => 'Desarrollo',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::General => 'Flag',
            self::Visit => 'MapPin',
            self::Maintenance => 'Wrench',
            self::Development => 'Code',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
