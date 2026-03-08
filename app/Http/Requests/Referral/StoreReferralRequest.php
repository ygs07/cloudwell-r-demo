<?php

namespace App\Http\Requests\Referral;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferralRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient' => ['required', 'array'],
            'patient.patient_number' => ['required'],
            'patient.date_of_birth' => ['nullable'],
            'patient.weight' => ['nullable'],
            'patient.blood_group' => ['nullable'],
            'patient.genotype' => ['nullable'],
            'referral_reason' => ['required'],
            'priority' => ['required', 'integer'],
            'referring_party' => ['required', 'array'],
            'referring_party.name' => ['required'],
            'optional_notes' => ['nullable'],
        ];
    }
}
