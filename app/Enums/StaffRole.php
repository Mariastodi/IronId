<?php

namespace App\Enums;

enum StaffRole: string
{
    case Admin = 'admin';
    case Attendant = 'attendant';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Attendant => 'Recepcionista',
        };
    }
}
