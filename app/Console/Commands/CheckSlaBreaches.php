<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\SlaCalculatorService;
use Illuminate\Console\Command;

class CheckSlaBreaches extends Command
{
    protected $signature = 'tickets:check-sla-breaches';

    protected $description = 'Check and mark SLA breached tickets';

    public function handle(SlaCalculatorService $slaService): int
    {
        $this->info('Checking SLA breaches...');

        $tickets = Ticket::whereNull('breached_at')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->whereNotNull('sla_deadline')
            ->where('sla_deadline', '<', now())
            ->get();

        $count = 0;
        foreach ($tickets as $ticket) {
            $slaService->checkAndMarkBreach($ticket);
            $count++;
            $this->line("Ticket #{$ticket->id} breached - Priority: {$ticket->priority}, Deadline: {$ticket->sla_deadline}");
        }

        $this->info("Done! {$count} tickets marked as breached.");

        return Command::SUCCESS;
    }
}
