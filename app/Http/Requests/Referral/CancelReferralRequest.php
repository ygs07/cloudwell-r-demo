<?php

namespace App\Http\Requests\Referral;

use Illuminate\Foundation\Http\FormRequest;

class CancelReferralRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $referral = $this->route('referral');
        abort_if(!in_array($referral->status->value, [1, 2, 3, 4]) || $referral->priority > 2, 400, 'Referral cannot be cancelled');
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
            'cancellation_reason' => ['required', 'string', 'max:255'],
        ];
    }
}
