<?php

namespace App\Models;

use App\Enums\BloodGroup;
use App\Enums\Genotype;
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

   protected function casts(): array
    {
        return [
            'blood_group' => BloodGroup::class,
            'genotype' => Genotype::class,
        ];
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }
}
