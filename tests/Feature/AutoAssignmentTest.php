<?php

use App\Actions\AssignLeastBusyAgent;
use App\Models\Agent;
use App\Models\Ticket;

it('escolhe o responsável com menos chamados em aberto', function () {
    $busy = Agent::factory()->create();
    $free = Agent::factory()->create();

    Ticket::factory()->count(3)->open()->forAgent($busy)->create();
    Ticket::factory()->count(1)->open()->forAgent($free)->create();

    $chosen = app(AssignLeastBusyAgent::class)->execute();

    expect($chosen->id)->toBe($free->id);
});

it('ignora chamados concluídos ao medir a carga de trabalho', function () {
    $withConcluded = Agent::factory()->create();
    $withOpen = Agent::factory()->create();

    Ticket::factory()->count(5)->concluded()->forAgent($withConcluded)->create();
    Ticket::factory()->count(1)->open()->forAgent($withOpen)->create();

    expect(app(AssignLeastBusyAgent::class)->execute()->id)->toBe($withConcluded->id);
});

it('desempata de forma determinística pelo menor id', function () {
    $first = Agent::factory()->create();
    Agent::factory()->create();

    expect(app(AssignLeastBusyAgent::class)->execute()->id)->toBe($first->id);
});

it('atribui automaticamente ao menos sobrecarregado ao abrir um chamado', function () {
    $busy = Agent::factory()->create();
    $free = Agent::factory()->create();
    Ticket::factory()->count(2)->open()->forAgent($busy)->create();

    $this->post('/chamados', [
        'title' => 'Notebook não liga',
        'description' => 'A tela fica preta ao ligar.',
        'priority' => 'alta',
        'status' => 'aberto',
        'assignment_mode' => 'auto',
    ])->assertRedirect();

    $this->assertDatabaseHas('tickets', [
        'title' => 'Notebook não liga',
        'agent_id' => $free->id,
    ]);
});
