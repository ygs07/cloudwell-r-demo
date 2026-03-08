<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    /** @use HasFactory<\Database\Factories\ReferralFactory> */
    use HasFactory;


    protected $fillable = [
        'patient_id',
        'referral_reason',
        'priority',
        'referring_party_id',
        'optional_notes',
        'created_at',
        'updated_at',
        'status',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function referringParty(): BelongsTo
    {
        return $this->belongsTo(ReferringParty::class);
    }
}
