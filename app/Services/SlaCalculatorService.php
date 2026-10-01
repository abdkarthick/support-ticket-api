<?php

namespace App\Services;

use App\Models\Ticket;
use Carbon\Carbon;

class SlaCalculatorService
{
    // SLA Hours mapping
    const SLA_HOURS = [
        'high' => 4,
        'medium' => 24,
        'low' => 72,
    ];

    public function calculateDeadline(string $priority, ?Carbon $from = null): Carbon
    {
        $from = $from ?? now();
        $hours = self::SLA_HOURS[$priority] ?? 24;

        return $from->copy()->addHours($hours);
    }

    public function setDeadline(Ticket $ticket): void
    {
        $ticket->sla_deadline = $this->calculateDeadline($ticket->priority);
        $ticket->save();
    }

    public function isBreached(Ticket $ticket): bool
    {
        if (! $ticket->sla_deadline) {
            return false;
        }
        if (in_array($ticket->status, ['closed', 'resolved'])) {
            return false;
        }

        return now()->greaterThan($ticket->sla_deadline);
    }

    public function checkAndMarkBreach(Ticket $ticket): bool
    {
        if ($this->isBreached($ticket) && ! $ticket->breached_at) {
            $ticket->breached_at = now();
            $ticket->save();

            // Log activity
            $ticket->activityLogs()->create([
                'user_id' => null,
                'action' => 'sla_breached',
                'old_value' => $ticket->sla_deadline,
                'new_value' => $ticket->breached_at,
            ]);

            return true;
        }

        return false;
    }
}
