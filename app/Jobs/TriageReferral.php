<?php

namespace App\Jobs;

use App\Enums\ReferralStatus;
use App\Models\AuditLog;
use App\Models\Referral;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TriageReferral implements ShouldQueue
{
    use Queueable;

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
        $status = ReferralStatus::TRIAGING;

        $oldStatus = $this->referral->status;

        AuditLog::create([
            'action' => 'triage_started',
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
    }
}
