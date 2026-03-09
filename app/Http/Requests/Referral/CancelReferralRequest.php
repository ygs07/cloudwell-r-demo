<?php

namespace App\Http\Requests\Referral;

use App\Enums\ReferralStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class CancelReferralRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        
        $referral = $this->route('referral');

        return $referral->canBeCancelled() && auth()->user()->tokenCan('referral:manage');
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

    /**
     * Custom message if authorization fails.
     */
    protected function failedAuthorization(): JsonResponse
    {
        abort(409, 'Referral cannot be cancelledds');
    }
}
