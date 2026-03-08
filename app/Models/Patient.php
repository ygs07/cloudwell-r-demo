<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory;

    protected $fillable = [
        'date_of_birth',
        'weight',
        'blood_group',
        'genotype',
        'patient_number',
    ];

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }
}
