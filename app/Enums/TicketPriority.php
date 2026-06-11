<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Baixa = 'baixa';
    case Media = 'media';
    case Alta = 'alta';

    public function label(): string
    {
        return match ($this) {
            self::Baixa => 'Baixa',
            self::Media => 'Média',
            self::Alta => 'Alta',
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::Baixa => 1,
            self::Media => 2,
            self::Alta => 3,
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $priority) => [
                'value' => $priority->value,
                'label' => $priority->label(),
            ],
            self::cases(),
        );
    }
}
