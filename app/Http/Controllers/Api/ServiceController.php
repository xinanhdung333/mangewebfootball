<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $services = Cache::remember('api:v1:services:active', 1800, function () {
            return Service::query()
                ->with('category')
                ->active()
                ->orderBy('name')
                ->get();
        });

        return ServiceResource::collection($services);
    }

    public function show(Service $service): ServiceResource
    {
        abort_unless($service->status === 'active', 404);

        $service = Cache::remember("api:v1:services:{$service->id}", 1800, function () use ($service) {
            return $service->load('category');
        });

        return new ServiceResource($service);
    }
}
