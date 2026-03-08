<?php

namespace App\Models;

use App\Auditable;
use App\Enums\ReferralPriority;
use App\Enums\ReferralStatus;
use App\Jobs\TriageReferral;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Referral extends Model
{
    /** @use HasFactory<\Database\Factories\ReferralFactory> */
    use HasFactory, Auditable;


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


    protected function casts(): array
    {
        return [
            'status' => ReferralStatus::class,
            'priority' => ReferralPriority::class,
        ];
    }

    protected static function booted()
    {
        static::created(function ($referral) {
            TriageReferral::dispatch($referral);
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function referringParty(): BelongsTo
    {
        return $this->belongsTo(ReferringParty::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }


    public function scopeSearchAndFilter($query, array $filters)
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (isset($filters['referring_party_id'])) {
            $query->where('referring_party_id', $filters['referring_party_id']);
        }
        if (isset($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }
        return $query;
    }
}
