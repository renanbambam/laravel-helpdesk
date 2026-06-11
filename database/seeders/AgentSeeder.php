<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        $agents = [
            ['name' => 'Ana Souza', 'email' => 'ana.souza@codificar.test'],
            ['name' => 'Bruno Lima', 'email' => 'bruno.lima@codificar.test'],
            ['name' => 'Carla Mendes', 'email' => 'carla.mendes@codificar.test'],
            ['name' => 'Diego Rocha', 'email' => 'diego.rocha@codificar.test'],
        ];

        foreach ($agents as $agent) {
            Agent::firstOrCreate(['email' => $agent['email']], $agent);
        }
    }
}
