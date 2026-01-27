<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Models\Field;
use App\Http\Controllers\Concerns\UsesServiceQuery;
use Illuminate\Support\Facades\DB;

class PagesController extends Controller
{
    use UsesServiceQuery;
    public function about()
    {
        return view('pages.visitor.about');
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        // Get booking stats
        $stats_total = $user ? $user->bookings()->count() : 0;
        $stats_confirmed = $user ? $user->bookings()->where('status', 'confirmed')->count() : 0;
        $stats_revenue = $user ? $user->bookings()->sum('total_price') : 0;
        
        // Get recent bookings
        $bookings = $user ? $user->bookings()->latest()->take(5)->get() : [];
        
        return view('pages.visitor.dashboard', [
            'user' => $user,
            'stats_total' => $stats_total,
            'stats_confirmed' => $stats_confirmed,
            'stats_revenue' => $stats_revenue,
            'bookings' => $bookings,
        ]);  
    }
       public function fields()
    {
        // use the Eloquent scope to include ratings
        $fields = Field::withRatings()->get();
        return view('pages.visitor.fields', ['fields' => $fields]);
    }

                  
    public function profile()
    {
        $user = Auth::user();
        return view('pages.profile', ['user' => $user]);
    }

    public function feedback()
    {
       $serviceFeedbacks = DB::table('feedback as f')
    ->join('services as s', 'f.service_id', '=', 's.id')
    ->join('users as u', 'f.user_id', '=', 'u.id')
    ->whereNotNull('f.service_id')
    ->where(function ($q) {
        $q->whereNotNull('f.message')
          ->orWhereNotNull('f.rating');
    })
    ->orderByDesc('f.id')
    ->select([
        'f.id as feedback_id',
        'u.name as user_name',
        's.name as service_name',
        's.price as service_price',
        'f.message as feedback',
        'f.rating'
    ])
    ->get()
    ->toArray();


$bookingFeedbacks = DB::table('bookings as b')
    ->join('users as u', 'u.id', '=', 'b.user_id')
    ->join('fields as f', 'f.id', '=', 'b.field_id')
    ->leftJoin('feedback as fb', function ($join) {
        $join->on('fb.booking_id', '=', 'b.id')
             ->on('fb.user_id', '=', 'u.id');
    })
    ->where(function ($q) {
        $q->whereNotNull('fb.message')
          ->orWhereNotNull('fb.rating');
    })
    ->orderByDesc('b.created_at')
    ->select([
        'b.id as booking_id',
        'u.name as user_name',
        'f.name as field_name',
        'b.booking_date',
        'b.start_time',
        'b.end_time',
        'fb.message as feedback_message',
        'fb.rating as feedback_rating'
    ])
    ->get()
    ->toArray();

return view('views.pages.visitor.feedback', compact('serviceFeedbacks', 'bookingFeedbacks'));
      
    }   

    public function serviceDetail()
    {
        return view('pages.visitor.Services-detail');
    }

    public function myServices()
    {
        $data = $this->getServicesForRequest();
        return view('pages.visitor.services', $data);
    }
}
 