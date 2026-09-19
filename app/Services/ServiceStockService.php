<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;

class ServiceStockService
{
    public function decreaseForOrder(Order $order): void
    {
        $items = OrderItem::where('order_id', $order->id)
            ->where('status', '!=', 'confirmed')
            ->get();

        foreach ($items as $item) {
            $service = Service::whereKey($item->service_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($service->quantity < $item->quantity) {
                throw new \RuntimeException(
                    "Dich vu {$service->name} chi con {$service->quantity} san pham"
                );
            }

            $service->decrement('quantity', $item->quantity);
        }
    }
}
