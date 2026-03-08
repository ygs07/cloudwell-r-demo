<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BasePaginatedCollection extends ResourceCollection
{
    protected $resourceClass;

    public function toArray(Request $request): array
    {
        $meta = [
            'total' => $this->total(),
            'current_page' => $this->currentPage(),
            'per_page' => $this->perPage(),
            'last_page' => $this->lastPage(),
            'total_records' => $this->total(),
        ];

        return [
            'data' => $this->resourceClass::collection($this->collection),
            'meta' => $meta,
            'links' => [
                'self' => $this->url($this->currentPage()),
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
        ];
    }
}
