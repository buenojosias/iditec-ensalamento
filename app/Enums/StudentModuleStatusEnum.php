<?php

namespace App\Enums;

enum StudentModuleStatusEnum: string
{
    case CURRENT = 'C';
    case APPROVED = 'A';
    case REPROVED = 'R';
    case NEXT = 'N';

    public function getLabel(): string
    {
        return match ($this) {
            self::CURRENT => 'Atual',
            self::APPROVED => 'Aprovado',
            self::REPROVED => 'Reprovado',
            self::NEXT => 'Próximo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CURRENT => 'blue',
            self::APPROVED => 'green',
            self::REPROVED => 'red',
            self::NEXT => 'amber',
        };
    }
}
