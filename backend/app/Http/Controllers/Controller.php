<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;

    protected function paginationParams(int $defaultPerPage = 12, int $maxPerPage = 50): array
    {
        $perPage = max(1, (int) request()->input('per_page', $defaultPerPage));
        $perPage = min($perPage, $maxPerPage);
        $page = max(1, (int) request()->input('page', 1));

        return [
            'page' => $page,
            'per_page' => $perPage,
            'q' => trim((string) request()->input('q', '')),
        ];
    }

    protected function cleanFilters(array $filters): array
    {
        return collect($filters)
            ->filter(function ($value) {
                return $value !== null && $value !== '';
            })
            ->toArray();
    }
}
