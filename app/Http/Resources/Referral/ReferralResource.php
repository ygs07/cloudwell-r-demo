<?php

namespace App\Http\Resources\Referral;

use App\Http\Resources\AuditLog\AuditLogResourceCollection;
use App\Http\Resources\Patient\PatientResource;
use App\Http\Resources\ReferringParty\ReferringPartyResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralResource extends JsonResource
{
    public static $wrap = null;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'referral_reason' => $this->referral_reason,
            'priority' => $this->priority->label(),
            'referring_party_id' => $this->referring_party_id,
            'optional_notes' => $this->optional_notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->status->label(),
            'patient' => PatientResource::make($this->whenLoaded('patient')),
            'audit_logs' => AuditLogResourceCollection::make($this->whenLoaded('auditLogs')),
            'referring_party' => ReferringPartyResource::make($this->whenLoaded('referringParty')),
        ];
    }
}

