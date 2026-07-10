<?php

namespace App\Jobs;

use App\Models\BillingUsageLog;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessClientBillingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $log;

    /**
     * Create a new job instance.
     */
    public function __construct(BillingUsageLog $log)
    {
        $this->log = $log;
    }

    /**
     * Execute the job.
     */
    public function handle(InvoiceService $invoiceService): void
    {
        $this->log->update(['status' => 'processing']);

        try {
            $license = $this->log->license;
            if (!$license) {
                throw new \Exception("License not found for ID: " . $this->log->license_id);
            }

            // Generate invoice (this also writes PDF and queues the InvoiceGenerated email)
            $invoiceService->generateInvoice(
                $license,
                $this->log->active_applicant_count,
                $this->log->sync_month,
                $this->log->sync_year,
                $this->log->school_breakdown
            );

            $this->log->update([
                'status' => 'completed',
                'error_message' => null,
            ]);
        } catch (\Throwable $e) {
            $this->log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage() . "\n" . $e->getTraceAsString(),
            ]);

            Log::error("Failed ProcessClientBillingJob for Log ID {$this->log->id}: " . $e->getMessage());
            
            // Re-throw so standard queue retry mechanisms can operate
            throw $e;
        }
    }
}
