<?php

namespace App\Http\Requests;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', new Enum(TicketPriority::class)],
            'status' => ['required', new Enum(TicketStatus::class)],
            'requester_name' => ['nullable', 'string', 'max:255'],
            'assignment_mode' => ['required', Rule::in(['manual', 'auto'])],
            'agent_id' => [
                Rule::requiredIf(fn () => $this->input('assignment_mode') === 'manual'),
                'nullable',
                'integer',
                'exists:agents,id',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descrição',
            'priority' => 'prioridade',
            'status' => 'status',
            'requester_name' => 'solicitante',
            'agent_id' => 'responsável',
        ];
    }
}
