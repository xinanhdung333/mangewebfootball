<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Show the application home page.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        return view('services.visitor.home', ['user' => $user]);
    }
    public function indexadmin(Request $request)
    {
        $user = Auth::user();
        return view('services.admin.home', ['user' => $user]);
    }
    public function indexboss(Request $request)
    {
        $user = Auth::user();
        return view('services.boss.home', ['user' => $user]);
    }
}
