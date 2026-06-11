<?php

return [
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser um texto.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'max' => [
        'string' => 'O campo :attribute não pode ter mais que :max caracteres.',
    ],
    'in' => 'O :attribute selecionado é inválido.',
    'enum' => 'O :attribute selecionado é inválido.',
    'exists' => 'O :attribute selecionado é inválido.',

    'attributes' => [
        'title' => 'título',
        'description' => 'descrição',
        'priority' => 'prioridade',
        'status' => 'status',
        'requester_name' => 'solicitante',
        'agent_id' => 'responsável',
        'assignment_mode' => 'modo de atribuição',
    ],
];
