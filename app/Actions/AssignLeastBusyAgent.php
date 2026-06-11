<?php

namespace App\Actions;

use App\Models\Agent;

class AssignLeastBusyAgent
{
    public function execute(): Agent
    {
        return Agent::query()
            ->withCount('openTickets')
            ->orderBy('open_tickets_count')
            ->orderBy('id')
            ->firstOrFail();
    }
}
