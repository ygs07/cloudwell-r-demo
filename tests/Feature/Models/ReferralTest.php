<?php

namespace Tests\Feature;

use App\Enums\ReferralPriority;
use App\Jobs\TriageReferral;
use App\Models\Patient;
use App\Models\Referral;
use App\Models\ReferringParty;
use App\Models\User;
use App\Enums\ReferralStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class ReferralTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_authenticated_user_can_create_referral(): void
    {
        Queue::fake();

        $staff = \App\Models\Staff::factory()->create();
        $user = $staff->user;

        $token = $user->createToken('test-token', ['referral:manage'])->plainTextToken;
        $payload = [
            'patient' => [
                'patient_number' => 'PT-12345',
                'date_of_birth' => '1990-01-01',
                'weight' => '70kg',
                'blood_group' => 1,
                'genotype' => 1,
            ],
            'referral_reason' => 'Routine checkup needed',
            'priority' => 1,
            'referring_party' => [
                'system_id' => 'SYS-001',
                'name' => 'General Hospital',
                'type' => 'hospital',
            ],
            'optional_notes' => 'Patient prefers afternoon appointments',
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
            'Idempotency-Key' => '44',
        ])->postJson('/api/v1/referrals', $payload);


        $response->assertStatus(201)
            ->assertJsonPath('message', 'Referral created successfully')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'patient_id',
                    'referring_party_id',
                    'referral_reason',
                    'priority',
                    'status',
                    'optional_notes',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('patients', [
            'patient_number' => 'PT-12345',
        ]);

        $this->assertDatabaseHas('referring_parties', [
            'system_id' => 'SYS-001',
        ]);

        $this->assertDatabaseHas('referrals', [
            'referral_reason' => 'Routine checkup needed',
            'priority' => 1,
        ]);

        Queue::assertPushed(TriageReferral::class);
        $user->tokens()->delete();
    }

    public function test_referral_creation_validation_fails_with_invalid_data(): void
    {
        $staff = \App\Models\Staff::factory()->create();
        $user = $staff->user;

        $payload = [
            'patient' => [
                // 'patient_number' missing
            ],
            // 'referral_reason' missing
            'priority' => 'high', // Should be integer
            'referring_party' => [
                // 'name' missing
                'system_id' => 'SYS-001',
            ],
        ];

        $token = $user->createToken('test-token', ['referral:manage'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
            'Idempotency-Key' => Uuid::uuid4(),
        ])->postJson('/api/v1/referrals', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'patient.patient_number',
                'referral_reason',
                'priority',
                'referring_party.name',
            ]);

        $user->tokens()->delete();
    }

    public function test_referral_can_be_cancelled_only_if_allowed(): void
    {
        Queue::fake();
        $staff = \App\Models\Staff::factory()->create();
        $user = $staff->user;
        $token = $user->createToken('test-token', ['referral:manage'])->plainTextToken;

        // 1️⃣ Referral that cannot be cancelled (e.g., REJECTED)
        $nonCancellable = Referral::factory()->create([
            'status' => ReferralStatus::REJECTED->value,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
            'Idempotency-Key' => Uuid::uuid4(),
        ])->patchJson("/api/v1/referrals/{$nonCancellable->id}/cancel", [
                    'cancellation_reason' => 'Test cancellation reason'
                ]);

        $response->assertStatus(409);

        $cancellable = Referral::factory()->create([
            'status' => ReferralStatus::TRIAGING->value,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
            'Idempotency-Key' => '46',
        ])->patchJson("/api/v1/referrals/{$cancellable->id}/cancel", [
                    'cancellation_reason' => 'Test cancellation reason'
                ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Referral cancelled successfully');

        $this->assertDatabaseHas('referrals', [
            'id' => $cancellable->id,
            'status' => ReferralStatus::CANCELLED->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $cancellable->id,
            'auditable_type' => Referral::class,
            'action' => 'cancelled',
        ]);
        $user->tokens()->delete();
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

    public function test_unauthenticated_user_cannot_create_referral(): void
    {
        $payload = [
            'patient' => [
                'patient_number' => 'PT-12345',
            ],
            'referral_reason' => 'Checkup',
            'priority' => 1,
            'referring_party' => [
                'name' => 'General Hospital',
            ],
        ];

        $response = $this->postJson('/api/v1/referrals', $payload);

        $response->assertStatus(401);
    }
}
