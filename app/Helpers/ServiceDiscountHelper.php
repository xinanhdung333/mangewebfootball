<?php

namespace App\Helpers;

use App\Models\Service;
use App\Models\ServiceDiscount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ServiceDiscountHelper
{
    /**
     * Cache key for active discount rules.
     */
    private const CACHE_KEY = 'service_discount_rules';
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Get all active discount rules from cache.
     * 
     * @return Collection<ServiceDiscount>
     */
    public static function getCachedRules(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return ServiceDiscount::where('is_active', 1)
                ->orderByRaw('service_id IS NULL')
                ->get();
        });
    }

    /**
     * Clear the discount rules cache (call when rules are updated).
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Calculate the current time in minutes since midnight.
     */
    private static function currentMinutes(): int
    {
        $now = Carbon::now();
        return $now->hour * 60 + $now->minute;
    }

    /**
     * Check if current time falls within a rule's time range.
     */
    private static function isInTimeRange(string $startTime, string $endTime, int $currentMin): bool
    {
        $start = explode(':', $startTime);
        $end = explode(':', $endTime);

        $startMin = $start[0] * 60 + $start[1];
        $endMin = $end[0] * 60 + $end[1];

        return ($startMin <= $endMin && $currentMin >= $startMin && $currentMin < $endMin)
            || ($startMin > $endMin && ($currentMin >= $startMin || $currentMin < $endMin));
    }

    /**
     * Apply discount to a single service.
     *
     * @param Service $service
     * @param Collection|null $rules  Pre-fetched rules (avoids re-querying)
     * @return array{final_price: float, original_price: float, discount_percent: float}
     */
    public static function applyDiscount(Service $service, ?Collection $rules = null): array
    {
        $rules = $rules ?? self::getCachedRules();
        $currentMin = self::currentMinutes();

        $finalPrice = $service->price;
        $discountPercent = 0;

        // Filter rules relevant to this service (specific rules first, then global)
        $relevantRules = $rules->filter(function ($rule) use ($service) {
            return $rule->service_id === null || $rule->service_id == $service->id;
        });

        foreach ($relevantRules as $rule) {
            if (self::isInTimeRange($rule->start_time, $rule->end_time, $currentMin)) {
                $finalPrice = $service->price * $rule->multiplier;
                $discountPercent = (1 - $rule->multiplier) * 100;
                break;
            }
        }

        return [
            'final_price' => $finalPrice,
            'original_price' => $service->price,
            'discount_percent' => $discountPercent,
        ];
    }

    /**
     * Apply discounts to a collection of services (sets final_price & discount_percent attributes).
     *
     * @param Collection $services
     * @return Collection
     */
    public static function applyDiscountToCollection(Collection $services): Collection
    {
        $rules = self::getCachedRules();
        $currentMin = self::currentMinutes();

        foreach ($services as $service) {
            $discount = self::applyDiscount($service, $rules);
            $service->final_price = $discount['final_price'];
            $service->discount_percent = $discount['discount_percent'];
        }

        return $services;
    }

    /**
     * Get flash sale info from cached rules.
     *
     * @return array{start: string|null, end: string|null, percent: float, note: string|null}
     */
    public static function getFlashSaleInfo(): array
    {
        $rules = self::getCachedRules();

        $flashSale = $rules->whereNull('service_id')->first();

        if (!$flashSale) {
            return [
                'start' => null,
                'end' => null,
                'percent' => 0,
                'note' => null,
            ];
        }

        return [
            'start' => substr($flashSale->start_time, 0, 5),
            'end' => substr($flashSale->end_time, 0, 5),
            'percent' => (1 - $flashSale->multiplier) * 100,
            'note' => $flashSale->note,
        ];
    }
}
