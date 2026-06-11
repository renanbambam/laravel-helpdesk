<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'agent_id',
        'requester_name',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
            'status' => TicketStatus::class,
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', TicketStatus::openValues());
    }

    public function scopeSearch(Builder $query, ?string $term): void
    {
        $query->when($term, function (Builder $query, string $term): void {
            $query->where(function (Builder $query) use ($term): void {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhere('requester_name', 'like', "%{$term}%");
            });
        });
    }

    public function scopeStatus(Builder $query, ?string $status): void
    {
        $query->when($status, fn (Builder $query, string $status) => $query->where('status', $status));
    }

    public function scopePriority(Builder $query, ?string $priority): void
    {
        $query->when($priority, fn (Builder $query, string $priority) => $query->where('priority', $priority));
    }

    public function scopeForAgent(Builder $query, ?int $agentId): void
    {
        $query->when($agentId, fn (Builder $query, int $agentId) => $query->where('agent_id', $agentId));
    }
}
