<?php

namespace App\Http\Controllers\Concerns;

trait UsesServiceQuery
{
    protected function getServicesForRequest()
    {
        $q = request()->query('q', '');
        $min = request()->query('min', '');
        $max = request()->query('max', '');

        $query = \App\Models\Service::withRatings();

        if ($q !== '') {
            $query->where('services.name', 'like', "%{$q}%");
        }

        if ($min !== '') {
            $query->where('services.price', '>=', $min);
        }

        if ($max !== '') {
            $query->where('services.price', '<=', $max);
        }

        $services = $query->get();
        $total_items = session('cart.total_items', 0);

        return [
            'services' => $services,
            'search' => $q,
            'min_price' => $min,
            'max_price' => $max,
            'total_items' => $total_items,
        ];
    }

    protected function formatCurrency($amount)
    {
        return number_format($amount, 0, ',', '.') . ' VNĐ';
    }
}
