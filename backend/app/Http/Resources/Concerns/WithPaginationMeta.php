<?php

namespace App\Http\Resources\Concerns;

trait WithPaginationMeta
{
    protected function paginationMeta(): ?array
    {
        if (! method_exists($this->resource, 'total')) {
            return null;
        }

        return [
            'current_page' => $this->resource->currentPage(),
            'last_page' => $this->resource->lastPage(),
            'per_page' => $this->resource->perPage(),
            'total' => $this->resource->total(),
            'from' => $this->resource->firstItem(),
            'to' => $this->resource->lastItem(),
            'has_more_pages' => $this->resource->hasMorePages(),
        ];
    }
}
