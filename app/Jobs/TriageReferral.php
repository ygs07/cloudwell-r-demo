<?php

namespace App\Jobs;

use App\Enums\ReferralStatus;
use App\Models\AuditLog;
use App\Models\Referral;
use App\Services\Triage\TriageEngine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class TriageReferral implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $tries = 3;

    public function __construct(public Referral $referral)
    {
    }

    public function handle()
    {
        DB::transaction(function () {

            $oldStatus = $this->referral->status;
            $oldPriority = $this->referral->priority;

            $triageEngine = new TriageEngine();
            $status = $triageEngine->evaluate($this->referral);

            $this->referral->update(['status' => ReferralStatus::TRIAGING]);

            if ($this->referral->priority !== $oldPriority) {
                AuditLog::create([
                    'action' => 'priority_adjusted',
                    'auditable_type' => Referral::class,
                    'auditable_id' => $this->referral->id,
                    'old_values' => ['priority' => $oldPriority],
                    'new_values' => ['priority' => $this->referral->priority],
                ]);
            }


            AuditLog::create([
                'action' => 'triaging',
                'auditable_type' => Referral::class,
                'auditable_id' => $this->referral->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => ReferralStatus::TRIAGING],
            ]);


            if ($status !== ReferralStatus::TRIAGING) {
                $this->referral->update(['status' => $status]);

                AuditLog::create([
                    'action' => 'triaging_completed',
                    'auditable_type' => Referral::class,
                    'auditable_id' => $this->referral->id,
                    'old_values' => ['status' => ReferralStatus::TRIAGING],
                    'new_values' => ['status' => $status],
                ]);


                AuditLog::create([
                    'action' => 'status_changed',
                    'auditable_type' => Referral::class,
                    'auditable_id' => $this->referral->id,
                    'old_values' => ['status' => ReferralStatus::TRIAGING],
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