<?php

use App\Models\Agent;
use App\Models\Ticket;
use Inertia\Testing\AssertableInertia as Assert;

it('lista os chamados na tela de acompanhamento', function () {
    $agent = Agent::factory()->create();
    Ticket::factory()->forAgent($agent)->create(['title' => 'Computador travando']);

    $this->get('/chamados')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tickets/Index')
            ->has('tickets.data', 1)
            ->where('tickets.data.0.title', 'Computador travando')
        );
});

it('exibe o detalhe de um chamado', function () {
    $ticket = Ticket::factory()->forAgent(Agent::factory()->create())->create();

    $this->get("/chamados/{$ticket->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tickets/Show')
            ->where('ticket.id', $ticket->id)
        );
});

it('cria um chamado com responsável escolhido manualmente', function () {
    $agent = Agent::factory()->create();

    $this->post('/chamados', [
        'title' => 'Impressora com defeito',
        'description' => 'A luz vermelha fica piscando e nada imprime.',
        'priority' => 'alta',
        'status' => 'aberto',
        'requester_name' => 'Marcelo Dias',
        'assignment_mode' => 'manual',
        'agent_id' => $agent->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('tickets', [
        'title' => 'Impressora com defeito',
        'agent_id' => $agent->id,
        'priority' => 'alta',
        'status' => 'aberto',
    ]);
});

it('valida os campos obrigatórios ao criar um chamado', function () {
    $this->post('/chamados', [])
        ->assertSessionHasErrors(['title', 'description', 'priority', 'status', 'assignment_mode']);
});

it('exige um responsável quando a atribuição é manual', function () {
    $this->post('/chamados', [
        'title' => 'Teclado quebrado',
        'description' => 'Algumas teclas não funcionam.',
        'priority' => 'media',
        'status' => 'aberto',
        'assignment_mode' => 'manual',
    ])->assertSessionHasErrors('agent_id');
});

it('atualiza um chamado existente', function () {
    $agent = Agent::factory()->create();
    $ticket = Ticket::factory()->forAgent($agent)->create(['status' => 'aberto', 'priority' => 'alta']);

    $this->put("/chamados/{$ticket->id}", [
        'title' => $ticket->title,
        'description' => $ticket->description,
        'priority' => 'baixa',
        'status' => 'resolvido',
        'assignment_mode' => 'manual',
        'agent_id' => $agent->id,
    ])->assertRedirect();

    $ticket->refresh();
    expect($ticket->status->value)->toBe('resolvido')
        ->and($ticket->priority->value)->toBe('baixa');
});

it('remove um chamado', function () {
    $ticket = Ticket::factory()->forAgent(Agent::factory()->create())->create();

    $this->delete("/chamados/{$ticket->id}")->assertRedirect();

    $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
});

it('filtra os chamados por status', function () {
    $agent = Agent::factory()->create();
    Ticket::factory()->forAgent($agent)->create(['status' => 'aberto', 'title' => 'Chamado aberto']);
    Ticket::factory()->forAgent($agent)->create(['status' => 'fechado', 'title' => 'Chamado fechado']);

    $this->get('/chamados?status=aberto')
        ->assertInertia(fn (Assert $page) => $page
            ->has('tickets.data', 1)
            ->where('tickets.data.0.title', 'Chamado aberto')
        );
});

it('busca os chamados por título', function () {
    $agent = Agent::factory()->create();
    Ticket::factory()->forAgent($agent)->create(['title' => 'Wi-Fi instável']);
    Ticket::factory()->forAgent($agent)->create(['title' => 'Cadeira nova']);

    $this->get('/chamados?search=Wi-Fi')
        ->assertInertia(fn (Assert $page) => $page
            ->has('tickets.data', 1)
            ->where('tickets.data.0.title', 'Wi-Fi instável')
        );
});
