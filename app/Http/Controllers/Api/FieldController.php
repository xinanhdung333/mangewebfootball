<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FieldResource;
use App\Models\Field;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class FieldController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $fields = Cache::remember('api:v1:fields:active', 3600, function () {
            return Field::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        });

        return FieldResource::collection($fields);
    }

    public function show(Field $field): FieldResource
    {
        abort_unless($field->status === 'active', 404);

        $field = Cache::remember("api:v1:fields:{$field->id}", 3600, fn () => $field);

        return new FieldResource($field);
    }
}
