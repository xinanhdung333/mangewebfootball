<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Http\Controllers\Concerns\UsesServiceQuery;

class ServiceController extends Controller
{
    use UsesServiceQuery;

    public function index()
    {
        $data = $this->getServicesForRequest();
        return view('services.index', $data);
    }

    public function show($id)
    {
        $service = Service::findOrFail($id);
        return view('services.show', ['service' => $service]);
    }
}
