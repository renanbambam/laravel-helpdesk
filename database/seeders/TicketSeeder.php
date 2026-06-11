<?php

namespace Database\Seeders;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Agent;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $agents = Agent::orderBy('id')->get()->keyBy('email');

        $tickets = [
            [
                'title' => 'Computador travando ao abrir o sistema',
                'description' => 'Meu computador trava toda vez que abro o sistema de notas. Preciso reiniciar várias vezes por dia.',
                'priority' => TicketPriority::Alta,
                'status' => TicketStatus::EmAndamento,
                'agent' => 'ana.souza@codificar.test',
                'requester_name' => 'Patrícia Gomes',
                'days_ago' => 1,
            ],
            [
                'title' => 'Impressora do segundo andar não funciona',
                'description' => 'A impressora não está imprimindo. Aparece a luz vermelha piscando e nada sai.',
                'priority' => TicketPriority::Media,
                'status' => TicketStatus::Aberto,
                'agent' => 'ana.souza@codificar.test',
                'requester_name' => 'Marcelo Dias',
                'days_ago' => 2,
            ],
            [
                'title' => 'Solicitação de cadeira nova',
                'description' => 'Minha cadeira está com o encosto quebrado e está difícil trabalhar. Gostaria de uma nova.',
                'priority' => TicketPriority::Baixa,
                'status' => TicketStatus::Aberto,
                'agent' => 'ana.souza@codificar.test',
                'requester_name' => 'Juliana Castro',
                'days_ago' => 3,
            ],
            [
                'title' => 'Acesso ao e-mail corporativo bloqueado',
                'description' => 'Não consigo acessar meu e-mail desde ontem. A senha não é aceita.',
                'priority' => TicketPriority::Alta,
                'status' => TicketStatus::EmAndamento,
                'agent' => 'bruno.lima@codificar.test',
                'requester_name' => 'Rafael Antunes',
                'days_ago' => 1,
            ],
            [
                'title' => 'Monitor com listras na tela',
                'description' => 'Apareceram listras coloridas no monitor. Atrapalha bastante a leitura.',
                'priority' => TicketPriority::Media,
                'status' => TicketStatus::Aberto,
                'agent' => 'bruno.lima@codificar.test',
                'requester_name' => 'Fernanda Reis',
                'days_ago' => 4,
            ],
            [
                'title' => 'Instalação do pacote Office',
                'description' => 'Preciso do Excel e do Word instalados na máquina nova que recebi.',
                'priority' => TicketPriority::Baixa,
                'status' => TicketStatus::Resolvido,
                'agent' => 'carla.mendes@codificar.test',
                'requester_name' => 'Tiago Barros',
                'days_ago' => 6,
            ],
            [
                'title' => 'Teclado com teclas sem resposta',
                'description' => 'Algumas teclas pararam de funcionar. Já testei em outra entrada USB.',
                'priority' => TicketPriority::Media,
                'status' => TicketStatus::Fechado,
                'agent' => 'carla.mendes@codificar.test',
                'requester_name' => 'Letícia Nunes',
                'days_ago' => 8,
            ],
            [
                'title' => 'Wi-Fi instável na sala de reuniões',
                'description' => 'A conexão cai durante as reuniões. Acontece principalmente à tarde.',
                'priority' => TicketPriority::Alta,
                'status' => TicketStatus::Resolvido,
                'agent' => 'carla.mendes@codificar.test',
                'requester_name' => 'Gustavo Pereira',
                'days_ago' => 10,
            ],
        ];

        foreach ($tickets as $data) {
            $agent = $agents->get($data['agent']);

            Ticket::factory()
                ->forAgent($agent)
                ->create([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'priority' => $data['priority'],
                    'status' => $data['status'],
                    'requester_name' => $data['requester_name'],
                    'created_at' => now()->subDays($data['days_ago']),
                    'updated_at' => now()->subDays($data['days_ago']),
                ]);
        }
    }
}
