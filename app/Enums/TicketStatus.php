<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Aberto = 'aberto';
    case EmAndamento = 'em_andamento';
    case Resolvido = 'resolvido';
    case Fechado = 'fechado';

    public function label(): string
    {
        return match ($this) {
            self::Aberto => 'Aberto',
            self::EmAndamento => 'Em andamento',
            self::Resolvido => 'Resolvido',
            self::Fechado => 'Fechado',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, self::openCases(), strict: true);
    }

    public static function openCases(): array
    {
        return [self::Aberto, self::EmAndamento];
    }

    public static function openValues(): array
    {
        return array_map(fn (self $status) => $status->value, self::openCases());
    }

    public static function options(): array
    {
        return array_map(
            fn (self $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            self::cases(),
        );
    }
}
