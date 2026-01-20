<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard() { return view('admin.dashboard'); }
    public function manageBookings() { return view('admin.manage-bookings'); }
    public function invoices() { return view('admin.invoices'); }
    public function editStatus() { return view('admin.edit-status'); }
    public function about() { return view('admin.about'); }
    public function manageFields() { return view('admin.manage-fields'); }
    public function manageOrders() { return view('admin.manage-orders'); }
    public function manageServices() { return view('admin.manage-services'); }
    public function userServiceHistory() { return view('admin.user_service_history'); }
    public function statistics() { return view('admin.statistics'); }
    public function manageFeedback() { return view('admin.manage-feedback'); }
}
