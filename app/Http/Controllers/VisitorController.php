<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Models\Category;
use App\Models\Field;
use App\Models\OrderItem;
use App\Http\Controllers\Concerns\UsesServiceQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // thêm dòng này
use App\Models\PriceRule;
use App\Models\ServiceDiscount;
use Carbon\Carbon;
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
$rule = PriceRule::first();
$ruleService = ServiceDiscount::first();
    return view('pages.visitor.dashboard', [
        'user' => $user,
        'stats_total' => $stats_total,
        'stats_confirmed' => $stats_confirmed,
        'stats_revenue' => $stats_revenue,
        'bookings' => $bookings,
        'rule'     => $rule,
        'ruleService'=>$ruleService
    ]);
}
      public function fields()
    {
        // use the Eloquent scope to include ratings
        $fields = Field::get();
$rule = PriceRule::first();
$ruleService = ServiceDiscount::first();
        return view('user.fields', ['fields' => $fields,
        'rule' => $rule,
        'ruleService' => $ruleService
        ]);
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
    $query = Service::with('category');

    // Search
    if ($request->q) {
        $query->where('name', 'like', '%' . $request->q . '%');
    }

    if ($request->filled('category_id')) {
        $query->where('category_id', $request->integer('category_id'));
    }

    // Sort
    if ($request->sort == 'priceAsc') $query->orderBy('price', 'asc');
    if ($request->sort == 'priceDesc') $query->orderBy('price', 'desc');
    if ($request->sort == 'rating') $query->orderByDesc('avg_rating');
    if ($request->sort == 'name') $query->orderBy('name', 'asc');

    $services = $query->get();

    // ===== THÊM LOGIC GIẢM GIÁ =====
    $now = Carbon::now();
    $currentMin = $now->hour * 60 + $now->minute;

    foreach ($services as $service) {

        $finalPrice = $service->price;
        $discountPercent = 0;

        $rules = ServiceDiscount::where(function($q) use ($service) {
            $q->where('service_id', $service->id)
              ->orWhereNull('service_id');
        })
        ->orderByRaw('service_id IS NULL')
        ->get();

        foreach ($rules as $rule) {

            $start = explode(':', $rule->start_time);
            $end   = explode(':', $rule->end_time);

            $startMin = $start[0] * 60 + $start[1];
            $endMin   = $end[0] * 60 + $end[1];

            $inTime =
                ($startMin <= $endMin && $currentMin >= $startMin && $currentMin < $endMin)
                ||
                ($startMin > $endMin && ($currentMin >= $startMin || $currentMin < $endMin));

            if ($inTime) {
                $finalPrice = $service->price * $rule->multiplier;
                $discountPercent = (1 - $rule->multiplier) * 100;
                break;
            }
        }

        // gắn thêm vào object
        $service->final_price = $finalPrice;
        $service->discount_percent = $discountPercent;
    }

    // ===== CART COUNT =====
    $totalItems = 0;
    if (Auth::check()) {
        $totalItems = (int) $this->getCartItemsForUser(Auth::user())
            ->sum('cart_items.quantity');
    }
$flashSale = ServiceDiscount::where('is_active', 1)
    ->whereNull('service_id') // áp dụng toàn bộ
    ->first();

$flashStart = null;
$flashEnd = null;
$flashnote = null;

$flashPercent = 0;

if ($flashSale) {
    $flashStart = substr($flashSale->start_time, 0, 5); // 01:00
    $flashEnd = substr($flashSale->end_time, 0, 5);     // 12:00
    $flashPercent = (1 - $flashSale->multiplier) * 100;
    $flashnote = $flashSale->note;
}
$rule = PriceRule::first();
$categories = Category::orderBy('name')->get();
return view('pages.visitor.services', compact(
    'services',
    'totalItems',
    'flashStart',
    'flashEnd',
    'flashPercent',
    'flashnote',
    'rule',
    'categories'
));
}
}
