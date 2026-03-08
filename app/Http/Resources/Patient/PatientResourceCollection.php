<?php

namespace App\Http\Resources\Patient;

use App\Http\Resources\BasePaginatedCollection;
use Illuminate\Http\Request;

class PatientResourceCollection extends BasePaginatedCollection
{
    protected $resourceClass = PatientResource::class;
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
