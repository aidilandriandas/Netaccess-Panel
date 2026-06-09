<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Invoice;
use Illuminate\Console\Command;

class CheckOverdueInvoice extends Command
{
    protected $signature = 'invoice:check-overdue';
    protected $description = 'Check and mark overdue invoices';

    public function handle(): int
    {
        $overdueInvoices = Invoice::where('status', 'unpaid')
            ->where('due_date', '<', today())
            ->get();

        $count = 0;
        foreach ($overdueInvoices as $invoice) {
            $invoice->update(['status' => 'overdue']);

            ActivityLog::create([
                'user_id' => null,
                'action' => 'auto_overdue_invoice',
                'description' => "Invoice marked overdue: {$invoice->invoice_number}",
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Scheduler',
            ]);

            $count++;
        }

        $this->info("Processed {$count} overdue invoices.");
        return self::SUCCESS;
    }
}
