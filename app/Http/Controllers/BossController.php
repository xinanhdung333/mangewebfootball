<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BossController extends Controller
{
    public function dashboard() { return view('boss.dashboard'); }
    public function manageBookings() { return view('boss.manage-bookings'); }
    public function invoices() { return view('boss.invoices'); }
    public function exportInvoice() { return view('boss.export_invoice'); }
    public function editStatus() { return view('boss.edit-status'); }
    public function about() { return view('boss.about'); }
    public function manageFields() { return view('boss.manage-fields'); }
    public function manageOrders() { return view('boss.manage-orders'); }
    public function manageServices() { return view('boss.manage-services'); }
    public function userServiceHistory() { return view('boss.user_service_history'); }
    public function statistics() { return view('boss.statistics'); }
    public function manageFeedback() { return view('boss.manage-feedback'); }
}
