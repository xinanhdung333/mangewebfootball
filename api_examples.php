<!-- // ============================================================================
// API CONVERSION EXAMPLES - Copy & Paste Ready Code
// ============================================================================

/**
 * STEP 1: Update routes/api.php
 * Delete everything in routes/api.php and replace with this:
 */

// routes/api.php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FieldController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserController;

// Public endpoints (no auth required)
Route::middleware('throttle:60,1')->group(function () {
    
    // Auth endpoints
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    
    // Public data (heavily cached)
    Route::get('/fields', [FieldController::class, 'index'])->middleware('cache:3600');
    Route::get('/fields/{id}', [FieldController::class, 'show'])->middleware('cache:3600');
    Route::get('/services', [ServiceController::class, 'index'])->middleware('cache:1800');
    Route::get('/services/{id}', [ServiceController::class, 'show'])->middleware('cache:1800');
    
    // Availability check (not cached because it changes frequently)
    Route::post('/bookings/check-available', [BookingController::class, 'checkAvailable']);
});

// Protected endpoints (auth required)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // User profile
    Route::get('/users/profile', [UserController::class, 'profile']);
    Route::patch('/users/profile', [UserController::class, 'updateProfile']);
    Route::patch('/users/password', [UserController::class, 'changePassword']);
    
    // Bookings (full CRUD)
    Route::apiResource('bookings', BookingController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::get('/bookings/{booking}/invoice', [BookingController::class, 'invoice']);
    
    // Payments
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify']);
    
    // Favorites
    Route::get('/favorites', [UserController::class, 'favorites']);
    Route::post('/favorites/{field}', [UserController::class, 'addFavorite']);
    Route::delete('/favorites/{field}', [UserController::class, 'removeFavorite']);
});

// Admin endpoints
Route::middleware('auth:sanctum', 'admin')->group(function () {
    Route::apiResource('admin/fields', 'App\Http\Controllers\Api\Admin\FieldController');
    Route::apiResource('admin/bookings', 'App\Http\Controllers\Api\Admin\BookingController')->only(['index', 'show', 'update']);
    Route::apiResource('admin/payments', 'App\Http\Controllers\Api\Admin\PaymentController')->only(['index', 'show', 'update']);
    Route::apiResource('admin/services', 'App\Http\Controllers\Api\Admin\ServiceController');
    Route::apiResource('admin/users', 'App\Http\Controllers\Api\Admin\UserController');
    Route::get('/admin/dashboard', 'App\Http\Controllers\Api\Admin\DashboardController@index');
    Route::get('/admin/reports/revenue', 'App\Http\Controllers\Api\Admin\ReportController@revenue');
});

// Webhook endpoints (MoMo payment callback)
Route::post('/webhooks/momo', [PaymentController::class, 'momoCallback']);

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});


// ============================================================================
// STEP 2: Create API Resource Classes
// ============================================================================

// app/Http/Resources/FieldResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'address' => $this->address,
            'description' => $this->description,
            'price_per_hour' => (float) $this->price_per_hour,
            'rating' => $this->average_rating ?? 0,
            'review_count' => $this->reviews_count ?? 0,
            'status' => $this->status,
            'capacity' => $this->capacity,
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'available_slots' => $this->when($request->has('date'), fn() => $this->getAvailableSlots($request->date)),
            'is_favorite' => $this->when($request->user(), fn() => $request->user()->hasFavorited($this)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

// app/Http/Resources/BookingResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'field' => new FieldResource($this->whenLoaded('field')),
            'user' => new UserResource($this->whenLoaded('user')),
            'booking_date' => $this->booking_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'duration_hours' => $this->duration_hours,
            'field_price' => (float) $this->field_price,
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'services_total' => (float) $this->services_total,
            'total_price' => (float) $this->total_price,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

// app/Http/Resources/UserResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'avatar' => $this->avatar_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

// ============================================================================
// STEP 3: Create API Controllers
// ============================================================================

// app/Http/Controllers/Api/AuthController.php
<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // POST /api/auth/register
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        $token = $user->createToken('api-token', ['*'], 
            now()->addHours(24)  // Token expires in 24 hours
        )->plainTextToken;

        return response()->json([
            'message' => 'Đăng ký thành công',
            'token' => $token,
            'user' => new UserResource($user),
        ], 201);
    }

    // POST /api/auth/login
    public function login(LoginRequest $request)
    {
        // Rate limit check
        if ($this->tooManyAttempts($request->email)) {
            return response()->json([
                'message' => 'Quá nhiều lần đăng nhập thất bại. Vui lòng thử lại sau 15 phút.'
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->recordFailedAttempt($request->email);
            return response()->json(['message' => 'Email hoặc mật khẩu không đúng'], 401);
        }

        $this->clearFailedAttempts($request->email);

        $token = $user->createToken('api-token', ['*'], 
            now()->addHours(24)
        )->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    // POST /api/auth/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đã đăng xuất']);
    }

    // POST /api/auth/refresh
    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        
        $token = $user->createToken('api-token', ['*'], 
            now()->addHours(24)
        )->plainTextToken;

        return response()->json(['token' => $token]);
    }

    // GET /api/auth/me
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    // POST /api/auth/forgot-password
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users']);

        $user = User::where('email', $request->email)->first();
        
        // Send password reset link
        \Illuminate\Support\Facades\Password::sendResetLink(
            ['email' => $user->email]
        );

        return response()->json([
            'message' => 'Liên kết đặt lại mật khẩu đã được gửi đến email của bạn'
        ]);
    }

    protected function tooManyAttempts($email)
    {
        return \Illuminate\Support\Facades\RateLimiter::tooManyAttempts(
            'login:' . $email, 5
        );
    }

    protected function recordFailedAttempt($email)
    {
        \Illuminate\Support\Facades\RateLimiter::hit('login:' . $email, 900);
    }

    protected function clearFailedAttempts($email)
    {
        \Illuminate\Support\Facades\RateLimiter::clear('login:' . $email);
    }
}

// app/Http/Controllers/Api/FieldController.php
<?php

namespace App\Http\Controllers\Api;

use App\Models\Field;
use App\Http\Resources\FieldResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FieldController extends Controller
{
    // GET /api/fields
    public function index(Request $request)
    {
        $cacheKey = 'fields_list_' . md5(serialize($request->query()));
        
        $fields = Cache::remember($cacheKey, 3600, function () use ($request) {
            return Field::query()
                ->where('status', 'active')
                ->with('images', 'services')
                ->when($request->location, fn($q) => $q->where('location', $request->location))
                ->when($request->min_price, fn($q) => $q->where('price_per_hour', '>=', $request->min_price))
                ->when($request->max_price, fn($q) => $q->where('price_per_hour', '<=', $request->max_price))
                ->when($request->rating, fn($q) => $q->where('average_rating', '>=', $request->rating))
                ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
                ->paginate(15);
        });

        return FieldResource::collection($fields);
    }

    // GET /api/fields/{id}
    public function show($id, Request $request)
    {
        $field = Cache::remember("field_{$id}", 3600, function () use ($id) {
            return Field::with(['images', 'services', 'reviews'])
                ->findOrFail($id);
        });

        return new FieldResource($field);
    }
}

// app/Http/Controllers/Api/BookingController.php
<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\Field;
use App\Http\Requests\BookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Controllers\Controller;
use App\Jobs\SendBookingConfirmationEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    // GET /api/bookings
    public function index(Request $request)
    {
        $bookings = auth()->user()->bookings()
            ->with(['field', 'services', 'payment'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->payment_status, fn($q) => $q->where('payment_status', $request->payment_status))
            ->orderBy('booking_date', 'desc')
            ->paginate(15);

        return BookingResource::collection($bookings);
    }

    // POST /api/bookings
    public function store(BookingRequest $request)
    {
        DB::beginTransaction();

        try {
            // Check availability again (double-check)
            $conflicts = Booking::where('field_id', $request->field_id)
                ->where('booking_date', $request->booking_date)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('start_time', [$request->start_time, $request->end_time])
                ->exists();

            if ($conflicts) {
                return response()->json([
                    'message' => 'Khung giờ này đã bị đặt. Vui lòng chọn khung giờ khác.'
                ], 409);
            }

            $field = Field::findOrFail($request->field_id);
            
            // Calculate pricing
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);
            $durationHours = $endTime->diffInHours($startTime);
            $fieldPrice = $durationHours * $field->price_per_hour;

            // Services total
            $servicesTotal = 0;
            if ($request->services) {
                $services = $field->services()->whereIn('id', $request->services)->get();
                $servicesTotal = $services->sum('price');
            }

            $booking = Booking::create([
                'user_id' => auth()->id(),
                'field_id' => $request->field_id,
                'booking_date' => $request->booking_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'duration_hours' => $durationHours,
                'field_price' => $fieldPrice,
                'services_total' => $servicesTotal,
                'total_price' => $fieldPrice + $servicesTotal,
                'notes' => $request->notes,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            // Attach services
            if ($request->services) {
                $booking->services()->attach($request->services);
            }

            DB::commit();

            // Send confirmation email asynchronously
            SendBookingConfirmationEmail::dispatch($booking);

            return response()->json(
                new BookingResource($booking->load('field', 'services')),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Booking creation failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại.'
            ], 500);
        }
    }

    // GET /api/bookings/{id}
    public function show(Booking $booking)
    {
        if (auth()->id() !== $booking->user_id && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new BookingResource($booking->load('field', 'services', 'payment'));
    }

    // PATCH /api/bookings/{id}
    public function update(Booking $booking, BookingRequest $request)
    {
        if (auth()->id() !== $booking->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Chỉ có thể sửa đơn đặt chưa được xác nhận'
            ], 422);
        }

        $booking->update($request->validated());
        
        // Clear cache
        \Illuminate\Support\Facades\Cache::forget('bookings_' . auth()->id());

        return new BookingResource($booking->load('field', 'services'));
    }

    // DELETE /api/bookings/{id}
    public function destroy(Booking $booking)
    {
        if (auth()->id() !== $booking->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Chỉ có thể hủy đơn đặt chưa được xác nhận'
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);
        
        return response()->json(['message' => 'Đã hủy đặt sân']);
    }

    // POST /api/bookings/{id}/cancel
    public function cancel(Booking $booking)
    {
        if (auth()->id() !== $booking->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'message' => 'Không thể hủy đơn đặt này'
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);
        
        // Refund if already paid
        if ($booking->payment_status === 'success') {
            // Trigger refund job
            \App\Jobs\ProcessRefund::dispatch($booking);
        }

        return response()->json(['message' => 'Đã hủy đặt sân']);
    }

    // POST /api/bookings/check-available
    public function checkAvailable(Request $request)
    {
        $request->validate([
            'field_id' => 'required|exists:fields,id',
            'booking_date' => 'required|date|after:today',
            'start_time' => 'required|date_format:H:i|between:06:00,23:00',
            'end_time' => 'required|date_format:H:i|after:start_time|before_or_equal:23:00',
        ]);

        $conflicts = Booking::where('field_id', $request->field_id)
            ->where('booking_date', $request->booking_date)
            ->where('status', '!=', 'cancelled')
            ->whereRaw('? BETWEEN start_time AND end_time OR ? BETWEEN start_time AND end_time', 
                [$request->start_time, $request->end_time])
            ->exists();

        if ($conflicts) {
            return response()->json([
                'available' => false,
                'message' => 'Khung giờ này đã bị đặt',
                'suggested_slots' => $this->suggestAlternativeSlots(
                    $request->field_id,
                    $request->booking_date,
                    $request->start_time,
                    $request->end_time
                ),
            ]);
        }

        return response()->json(['available' => true]);
    }

    // GET /api/bookings/{id}/invoice
    public function invoice(Booking $booking)
    {
        if (auth()->id() !== $booking->user_id && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Generate PDF invoice
        $pdf = \PDF::loadView('invoices.booking', ['booking' => $booking]);
        
        return $pdf->download("invoice_{$booking->booking_number}.pdf");
    }

    private function suggestAlternativeSlots($fieldId, $date, $startTime, $endTime)
    {
        // Implementation to suggest alternative time slots
        return [];
    }
}

// ============================================================================
// STEP 4: Create Validation Requests
// ============================================================================

// app/Http/Requests/LoginRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không hợp lệ',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự',
        ];
    }
}

// app/Http/Requests/BookingRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'field_id' => 'required|integer|exists:fields,id',
            'booking_date' => 'required|date|after:today|before:+1 year',
            'start_time' => 'required|date_format:H:i|between:06:00,23:00',
            'end_time' => 'required|date_format:H:i|after:start_time|before_or_equal:23:00',
            'services' => 'array',
            'services.*' => 'integer|exists:services,id',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'field_id.required' => 'Sân bóng là bắt buộc',
            'field_id.exists' => 'Sân bóng không tồn tại',
            'booking_date.required' => 'Ngày đặt là bắt buộc',
            'booking_date.after' => 'Ngày đặt phải sau hôm nay',
            'start_time.required' => 'Giờ bắt đầu là bắt buộc',
            'end_time.required' => 'Giờ kết thúc là bắt buộc',
            'end_time.after' => 'Giờ kết thúc phải sau giờ bắt đầu',
        ];
    }
}

// ============================================================================
// STEP 5: Update Models with Relationships
// ============================================================================

// app/Models/Booking.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'field_id', 'booking_date', 'start_time', 'end_time',
        'duration_hours', 'field_price', 'services_total', 'total_price',
        'notes', 'status', 'payment_status',
    ];

    protected $with = ['field', 'user', 'services', 'payment'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'booking_services');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', today())
            ->where('status', '!=', 'cancelled')
            ->orderBy('booking_date');
    }
}

// ============================================================================
// STEP 6: Error Handling Middleware
// ============================================================================

// app/Exceptions/Handler.php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        // API requests
        if ($request->expectsJson()) {
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof AuthenticationException) {
                return response()->json(['message' => 'Chưa xác thực'], 401);
            }

            if ($exception instanceof HttpException && $exception->getStatusCode() === 404) {
                return response()->json(['message' => 'Không tìm thấy'], 404);
            }

            // Log errors for debugging
            \Log::error($exception->getMessage(), [
                'exception' => $exception,
                'url' => $request->url(),
                'method' => $request->method(),
            ]);

            return response()->json([
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau.',
                'error' => app()->isLocal() ? $exception->getMessage() : null,
            ], 500);
        }

        return parent::render($request, $exception);
    }
}

// ============================================================================
// This is ready-to-use code. Just follow the STEP-by-STEP implementation!
// ============================================================================ -->
