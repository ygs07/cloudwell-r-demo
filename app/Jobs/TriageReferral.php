<?php

namespace App\Jobs;

use App\Enums\ReferralStatus;
use App\Models\AuditLog;
use App\Models\Referral;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
class TriageReferral implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    /**
     * Create a new job instance.
     */
    public function __construct(public Referral $referral)
    {
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        DB::transaction(function () {

            $status = ReferralStatus::TRIAGING;

            $oldStatus = $this->referral->status;
            $this->referral->update([
                'status' => ReferralStatus::TRIAGING
            ]);

            AuditLog::create([
                'action' => 'triaging',
                'auditable_type' => Referral::class,
                'auditable_id' => $this->referral->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => $status],
            ]);

            if ($this->referral->priority >= 3 || str_contains(strtolower($this->referral->referral_reason), 'emergency')) {
                $status = ReferralStatus::ACCEPTED;
                $this->referral->update([
                    'status' => $status
                ]);
                $this->referral->auditLogs()->create([
                    'action' => 'triage_completed',
                    'old_values' => ['status' => $this->referral->status],
                    'new_values' => ['status' => $status],
                ]);
                $this->referral->auditLogs()->create([
                    'action' => 'status_changed',
                    'old_values' => ['status' => $this->referral->status],
                    'new_values' => ['status' => $status],
                ]);
            }
        });
    }

    public function failed(\Throwable $exception): void
    {
        AuditLog::create([
            'action' => 'triage_failed',
            'auditable_type' => Referral::class,
            'auditable_id' => $this->referral->id,
            'old_values' => ['status' => $this->referral->status],
            'new_values' => ['status' => $this->referral->status],
            'metadata' => [
                'error' => $exception->getMessage(),
            ]
        ]);
    }
}
