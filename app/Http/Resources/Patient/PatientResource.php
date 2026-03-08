<?php

namespace App\Http\Resources\Patient;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
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
            'date_of_birth' => $this->date_of_birth,
            'weight' => $this->weight,
            'blood_group' => $this->blood_group?->label(),
            'genotype' => $this->genotype?->label(),
            'patient_number' => $this->patient_number
        ];
    }
}
