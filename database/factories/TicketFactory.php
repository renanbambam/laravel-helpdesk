<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Agent;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->sentence(4), '.'),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(TicketPriority::cases()),
            'status' => fake()->randomElement(TicketStatus::cases()),
            'agent_id' => Agent::factory(),
            'requester_name' => fake()->name(),
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => [
            'status' => fake()->randomElement(TicketStatus::openCases()),
        ]);
    }

    public function concluded(): static
    {
        return $this->state(fn () => [
            'status' => fake()->randomElement([TicketStatus::Resolvido, TicketStatus::Fechado]),
        ]);
    }

    public function forAgent(Agent $agent): static
    {
        return $this->state(fn () => ['agent_id' => $agent->id]);
    }
}
