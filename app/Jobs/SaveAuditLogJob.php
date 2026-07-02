<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SaveAuditLogJob implements ShouldQueue
{
    use Queueable;

    protected array $logData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $logData)
    {
        $this->logData = $logData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Illuminate\Support\Facades\DB::table('audit_logs')->insert(array_merge([
            'created_at' => now(),
        ], $this->logData));
    }
}
