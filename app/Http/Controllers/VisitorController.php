<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Models\Field;
use App\Models\OrderItem;
use App\Http\Controllers\Concerns\UsesServiceQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // thêm dòng này

class VisitorController extends Controller
{
    use UsesServiceQuery;

    public function about()
    {
        return view('pages.visitor.about');
    }
public function dashboard()
{
    $user = Auth::user();
    
    $stats_total = $user ? $user->bookings()->count() : 0;
    $stats_confirmed = $user ? $user->bookings()->where('status', 'confirmed')->count() : 0;
    $stats_revenue = $user ? $user->bookings()->sum('total_price') : 0;
   

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


   public function feedbacks()
    {
       $serviceFeedbacks = DB::table('feedbacks as f')
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
        'f.message as feedbacks',
        'f.rating'
    ])
    ->get()
    ->toArray();


$bookingFeedbacks = DB::table('bookings as b')
    ->join('users as u', 'u.id', '=', 'b.user_id')
    ->join('fields as f', 'f.id', '=', 'b.field_id')
    ->leftJoin('feedbacks as fb', function ($join) {
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

return view('pages.visitor.feedback', compact('serviceFeedbacks', 'bookingFeedbacks'));        

    }

    public function serviceDetail()
    {
        return view('pages.visitor.Services-detail');
    }

   
public function services(Request $request)
{
$query = Service::query();
    // Search theo tên
    if ($request->q) {
        $query->where('services.name', 'like', '%' . $request->q . '%');
    }



    $services = $query->get();

    // Nếu request từ AJAX → chỉ trả list HTML
    if ($request->ajax()) {

        return view(
            'pages.visitor.service_list',
            compact('services')
        )->render();

    }

    // Cart session
    $cart = session()->get('cart', []);

    $totalItems = array_sum(
        array_column($cart, 'quantity')
    );

    return view(
        'pages.visitor.services',
        compact('services', 'totalItems')
    );
}
}