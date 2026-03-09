<?php

namespace App\Services\Triage;

use App\Enums\ReferralPriority;
use App\Enums\ReferralStatus;
use App\Models\Referral;

class TriageEngine
{
    public function evaluate(Referral $referral): ReferralStatus
    {
        $reason = strtolower($referral->referral_reason);
        $notes = strtolower($referral->optional_notes ?? '');


        $this->adjustPriority($referral, $reason, $notes);


        if ($this->isEmergency($reason)) {
            return ReferralStatus::ACCEPTED;
        }

        if ($this->isHighPriority($referral)) {
            return ReferralStatus::ACCEPTED;
        }

        if ($this->shouldReject($referral)) {
            return ReferralStatus::REJECTED;
        }

        return ReferralStatus::TRIAGING;
    }

    protected function adjustPriority(Referral $referral, string $reason, string $notes): void
    {
        $priority = $referral->priority->value;


        $emergencyKeywords = ['stroke', 'cardiac arrest', 'heart attack', 'respiratory distress', 'severe bleeding', 'emergency'];
        foreach ($emergencyKeywords as $keyword) {
            if (str_contains($reason, $keyword)) {
                $priority++;
                break;
            }
        }


        if (str_contains($notes, 'urgent') || str_contains($notes, 'immediately')) {
            $priority++;
        }


        if (strlen($reason) < 10 && empty($notes)) {
            $priority--;
        }


        $priority = max(ReferralPriority::LOW->value, min(ReferralPriority::HIGH->value, $priority));

        $referral->update(['priority' => $priority]);
    }

    protected function isEmergency(string $reason): bool
    {
        $keywords = ['stroke', 'cardiac arrest', 'heart attack', 'respiratory distress', 'severe bleeding', 'emergency'];
        foreach ($keywords as $keyword) {
            if (str_contains($reason, $keyword)) return true;
        }
        return false;
    }

    protected function isHighPriority(Referral $referral): bool
    {
        return $referral->priority->value >= ReferralPriority::HIGH->value;
    }

    protected function shouldReject(Referral $referral): bool
    {
        return $referral->priority->value <= ReferralPriority::LOW->value
            && empty($referral->optional_notes)
            && strlen($referral->referral_reason) < 10;
    }
}