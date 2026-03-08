<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\ReferralStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Referral\StoreReferralRequest;
use App\Http\Resources\Referral\ReferralResource;
use App\Http\Resources\Referral\ReferralResourceCollection;
use App\Models\Patient;
use App\Models\Referral;
use App\Models\ReferringParty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{

    public function index(Request $request)
    {
        $referrals = Referral::with('patient', 'referringParty')
            ->searchAndFilter($request->all())
            ->paginate(10);
        return response()->json(
            new ReferralResourceCollection($referrals),
        );
    }
    public function store(StoreReferralRequest $request): JsonResponse
    {
        try {
            $referral = DB::transaction(function () use ($request) {
                $patient = Patient::updateOrCreate(
                    ['patient_number' => $request->patient['patient_number']],
                    [
                        'date_of_birth' => $request->patient['date_of_birth'] ?? null,
                        'weight' => $request->patient['weight'] ?? null,
                        'blood_group' => $request->patient['blood_group'] ?? null,
                        'genotype' => $request->patient['genotype'] ?? null,
                    ]
                );

                $referringParty = ReferringParty::updateOrCreate(
                    ['system_id' => $request->referring_party['system_id']],
                    [
                        'name' => $request->referring_party['name'],
                        'type' => $request->referring_party['type'],
                    ]
                );

                return Referral::create([
                    'patient_id' => $patient->id,
                    'referral_reason' => $request->referral_reason,
                    'priority' => $request->priority,
                    'referring_party_id' => $referringParty->id,
                    'optional_notes' => $request->optional_notes,
                    'status' => ReferralStatus::RECEIVED,
                ]);
            });

            return response()->json([
                'message' => 'Referral created successfully',
                'data' => new ReferralResource($referral),
            ], 201);
        } catch (\Throwable $e) {
            \Log::error('Error creating referral: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create referral',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
