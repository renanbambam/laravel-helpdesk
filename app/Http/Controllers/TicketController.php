<?php

namespace App\Http\Controllers;

use App\Actions\AssignLeastBusyAgent;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Agent;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    private const SORTABLE = ['created_at', 'title', 'priority', 'status'];

    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->trim()->value() ?: null,
            'status' => $request->string('status')->value() ?: null,
            'priority' => $request->string('priority')->value() ?: null,
            'agent_id' => $request->integer('agent_id') ?: null,
            'sort' => in_array($request->input('sort'), self::SORTABLE, true) ? $request->input('sort') : 'created_at',
            'direction' => $request->input('direction') === 'asc' ? 'asc' : 'desc',
        ];

        $tickets = Ticket::query()
            ->with('agent')
            ->search($filters['search'])
            ->status($filters['status'])
            ->priority($filters['priority'])
            ->forAgent($filters['agent_id'])
            ->orderBy($filters['sort'], $filters['direction'])
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Ticket $ticket) => (new TicketResource($ticket))->resolve());

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $filters,
            'agents' => $this->agentOptions(),
            'priorityOptions' => TicketPriority::options(),
            'statusOptions' => TicketStatus::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tickets/Create', $this->formOptions());
    }

    public function store(StoreTicketRequest $request, AssignLeastBusyAgent $assigner): RedirectResponse
    {
        $data = $request->validated();
        $data['agent_id'] = $this->resolveAgentId($request, $assigner);

        $ticket = Ticket::create($data);

        return redirect()
            ->route('chamados.show', $ticket)
            ->with('success', 'Chamado aberto com sucesso.');
    }

    public function show(Ticket $ticket): Response
    {
        return Inertia::render('Tickets/Show', [
            'ticket' => (new TicketResource($ticket->load('agent')))->resolve(),
        ]);
    }

    public function edit(Ticket $ticket): Response
    {
        return Inertia::render('Tickets/Edit', [
            'ticket' => (new TicketResource($ticket->load('agent')))->resolve(),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket, AssignLeastBusyAgent $assigner): RedirectResponse
    {
        $data = $request->validated();
        $data['agent_id'] = $this->resolveAgentId($request, $assigner);

        $ticket->update($data);

        return redirect()
            ->route('chamados.show', $ticket)
            ->with('success', 'Chamado atualizado com sucesso.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->delete();

        return redirect()
            ->route('chamados.index')
            ->with('success', 'Chamado removido.');
    }

    private function resolveAgentId(Request $request, AssignLeastBusyAgent $assigner): int
    {
        if ($request->validated('assignment_mode') === 'auto') {
            return $assigner->execute()->id;
        }

        return (int) $request->validated('agent_id');
    }

    private function formOptions(): array
    {
        return [
            'agents' => $this->agentOptions(),
            'priorityOptions' => TicketPriority::options(),
            'statusOptions' => TicketStatus::options(),
        ];
    }

    private function agentOptions(): array
    {
        return Agent::query()
            ->withCount('openTickets')
            ->orderBy('name')
            ->get()
            ->map(fn (Agent $agent) => [
                'id' => $agent->id,
                'name' => $agent->name,
                'open_tickets_count' => $agent->open_tickets_count,
            ])
            ->all();
    }
}
