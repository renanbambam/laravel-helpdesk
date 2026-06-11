<?php

use App\Enums\TicketStatus;

it('considera apenas Aberto e Em andamento como em aberto', function () {
    expect(TicketStatus::Aberto->isOpen())->toBeTrue()
        ->and(TicketStatus::EmAndamento->isOpen())->toBeTrue()
        ->and(TicketStatus::Resolvido->isOpen())->toBeFalse()
        ->and(TicketStatus::Fechado->isOpen())->toBeFalse();
});

it('expõe os valores dos status em aberto para uso em consultas', function () {
    expect(TicketStatus::openValues())->toBe(['aberto', 'em_andamento']);
});
