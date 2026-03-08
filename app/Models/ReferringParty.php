<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferringParty extends Model
{
    /** @use HasFactory<\Database\Factories\ReferringPartyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'system_id',
        'type',
    ];

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }
}
