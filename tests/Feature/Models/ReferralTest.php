<?php

namespace Tests\Feature;


use App\Jobs\TriageReferral;
use App\Models\Referral;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class ReferralTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_async_triage_path_updates_referral_status(): void
    {
        $referral = Referral::factory()->create([
            'priority' => 3,
            'referral_reason' => 'Emergency situation',
            'status' => 1,
        ]);

        $job = new TriageReferral($referral);
        $job->handle();

        $this->assertDatabaseHas('referrals', [
            'id' => $referral->id,
            'status' => 3,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $referral->id,
            'auditable_type' => Referral::class,
            'action' => 'triage_completed',
        ]);
    }
}
