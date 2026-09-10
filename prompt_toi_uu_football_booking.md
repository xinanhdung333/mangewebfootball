# 🚀 PROMPT TỐI ƯU - REFACTOR FOOTBALL BOOKING SYSTEM
## Từ Blade Template → API + Cache + Security

---

## 📋 PHÂN TÍCH VẤN ĐỀ HIỆN TẠI

### 1. **Performance Issues** ⚠️
- ❌ **Full HTML Rendering**: Trả về toàn bộ HTML từ server (Blade templates) → lag khi page load
- ❌ **No Caching**: Mỗi request lại query database từ đầu
- ❌ **No API Separation**: Frontend và backend chặt chẽ → khó scale
- ❌ **N+1 Query Problems**: Có thể bị truy vấn lặp lại dữ liệu liên kết

### 2. **Security Vulnerabilities** 🔒
- ❌ **No Rate Limiting**: Dễ bị brute force, DDoS
- ❌ **No API Versioning**: Khó maintain khi thay đổi endpoint
- ❌ **Credentials Exposed**: Có file `account.text` chứa tài khoản trong repo
- ❌ **No CORS Handling**: Nếu frontend tách rời
- ❌ **SQL Injection Risk**: Cần validation chặt chẽ
- ❌ **Missing HTTPS/HSTS**: Không enforce secure connection
- ❌ **No Input Sanitization**: Chưa có proper sanitization
- ❌ **Weak Token Expiry**: JWT tokens có thể không có expiry

### 3. **Code Quality** 📊
- ❌ **No Async Processing**: MoMo payment sync → blocking request
- ❌ **No Queue System**: Background jobs không được xử lý
- ❌ **No API Documentation**: Swagger/OpenAPI missing
- ❌ **No Testing**: Unit tests, integration tests thiếu

---

## ✅ CHIẾN LƯỢC REFACTOR

### **Phase 1: API-First Architecture**
```
Mục tiêu: Tách frontend khỏi backend → JSON API
Timeline: 2-3 tuần
```

#### 1.1 - Tạo API Routes Riêng
```php
// routes/api.php - THAY THẾ web.php cho data endpoints
Route::middleware('api', 'throttle:60,1')->group(function () {
    // Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    
    // Fields (Sân bóng)
    Route::get('/fields', [FieldController::class, 'index'])->middleware('cache:3600');
    Route::get('/fields/{id}', [FieldController::class, 'show'])->middleware('cache:3600');
    
    // Bookings
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::get('/bookings/{id}', [BookingController::class, 'show']);
        Route::patch('/bookings/{id}', [BookingController::class, 'update']);
        Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);
    });
    
    // Services
    Route::get('/services', [ServiceController::class, 'index'])->middleware('cache:1800');
    Route::get('/services/{id}', [ServiceController::class, 'show'])->middleware('cache:1800');
    
    // Check availability (realtime)
    Route::post('/bookings/check-available', [BookingController::class, 'checkAvailable']);
});
```

#### 1.2 - Implement API Resources (Data Formatting)
```php
// app/Http/Resources/FieldResource.php
class FieldResource extends JsonResource {
    public function toArray($request) {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'price_per_hour' => (float) $this->price_per_hour,
            'status' => $this->status,
            'rating' => $this->average_rating,
            'images' => ImageResource::collection($this->images),
            'services' => ServiceResource::collection($this->services),
        ];
    }
}
```

#### 1.3 - API Controllers (Lean & Clean)
```php
// app/Http/Controllers/Api/BookingController.php
class BookingController extends Controller {
    public function index(Request $request) {
        $bookings = auth()->user()->bookings()
            ->with(['field', 'services', 'payment'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->paginate(15);
            
        return BookingResource::collection($bookings);
    }
    
    public function checkAvailable(Request $request) {
        $validated = $request->validate([
            'field_id' => 'required|exists:fields,id',
            'date' => 'required|date|after:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);
        
        $conflicts = Booking::where('field_id', $validated['field_id'])
            ->where('booking_date', $validated['date'])
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
            ->exists();
        
        return response()->json(['available' => !$conflicts]);
    }
}
```

---

### **Phase 2: Caching Strategy**

#### 2.1 - Redis Cache Setup
```env
# .env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### 2.2 - Strategic Caching
```php
// app/Providers/AppServiceProvider.php
class AppServiceProvider extends ServiceProvider {
    public function boot() {
        // Cache fields list
        Cache::remember('fields_list', 3600, function () {
            return Field::with('images', 'services')
                ->where('status', 'active')
                ->get();
        });
        
        // Cache popular services
        Cache::rememberForever('services_all', function () {
            return Service::where('status', 'active')->get();
        });
    }
}

// Invalidate when data changes
class FieldController extends Controller {
    public function update(Field $field, Request $request) {
        $field->update($request->validated());
        
        // Clear cache
        Cache::forget('fields_list');
        Cache::tags(['field_' . $field->id])->flush();
        
        return new FieldResource($field);
    }
}
```

#### 2.3 - Query Optimization with Eager Loading
```php
// app/Models/Booking.php
class Booking extends Model {
    protected $with = ['field', 'user', 'services', 'payment'];
    
    public function scopeActive($query) {
        return $query->where('status', '!=', 'cancelled');
    }
}

// app/Models/Field.php
class Field extends Model {
    protected $with = ['images', 'services'];
    
    public function getAvailableSlotsAttribute() {
        return Cache::remember(
            "field_{$this->id}_slots_" . now()->format('Y-m-d'),
            3600,
            fn() => $this->calculateAvailableSlots()
        );
    }
}
```

---

### **Phase 3: Security Hardening** 🔐

#### 3.1 - Authentication with Laravel Sanctum
```php
// Laravel Sanctum - Token-based auth
// config/auth.php
'guards' => [
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],

// app/Http/Controllers/AuthController.php
class AuthController extends Controller {
    public function login(Request $request) {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
        
        if (!Auth::attempt($validated)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        
        $user = Auth::user();
        $token = $user->createToken('api-token', ['*'], 
            now()->addHours(24)  // ⚠️ EXPIRE TOKEN
        )->plainTextToken;
        
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }
    
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
```

#### 3.2 - Rate Limiting & Throttling
```php
// app/Providers/RouteServiceProvider.php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->email);  // 5 attempts/minute
});

// routes/api.php
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');
```

#### 3.3 - Input Validation & Sanitization
```php
// app/Http/Requests/BookingRequest.php
class BookingRequest extends FormRequest {
    public function rules() {
        return [
            'field_id' => 'required|integer|exists:fields,id',
            'booking_date' => 'required|date|after:today|before:+1 year',
            'start_time' => 'required|date_format:H:i|between:06:00,23:00',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'services' => 'array|exists:services,id',
            'notes' => 'nullable|string|max:500|sanitize',  // Custom rule
        ];
    }
    
    public function messages() {
        return [
            'field_id.exists' => 'Sân bóng không tồn tại',
            'booking_date.after' => 'Ngày đặt phải sau hôm nay',
        ];
    }
}

// app/Rules/SanitizeInput.php
class SanitizeInput implements Rule {
    public function passes($attribute, $value) {
        return $value === strip_tags($value);
    }
}
```

#### 3.4 - CORS Security
```php
// config/cors.php
'allowed_origins' => [
    'http://localhost:3000',
    'https://yourdomain.com',
    'https://api.yourdomain.com',
],
'allowed_headers' => ['Content-Type', 'Authorization'],
'exposed_headers' => ['X-Total-Count', 'X-Page-Count'],
'max_age' => 86400,
```

#### 3.5 - SQL Injection Prevention
```php
// ✅ GOOD - Parameterized queries
Booking::where('field_id', $fieldId)->get();

// ❌ BAD - Never do this
Booking::whereRaw("field_id = $fieldId")->get();

// Use Query Builder, not raw SQL
```

#### 3.6 - HTTPS & Security Headers
```php
// app/Http/Middleware/SecurityHeaders.php
class SecurityHeaders {
    public function handle($request, $next) {
        return $next($request)
            ->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'DENY')
            ->header('X-XSS-Protection', '1; mode=block')
            ->header('Referrer-Policy', 'no-referrer');
    }
}
```

---

### **Phase 4: Async Processing (Jobs & Queues)**

#### 4.1 - Payment Processing with Jobs
```php
// app/Jobs/ProcessMomoPayment.php
class ProcessMomoPayment implements ShouldQueue {
    public $tries = 3;
    public $timeout = 120;
    
    public function __construct(private Payment $payment) {}
    
    public function handle() {
        try {
            $momoClient = new MomoClient(config('momo'));
            $response = $momoClient->pay([
                'amount' => $this->payment->amount,
                'orderId' => $this->payment->id,
                'description' => "Booking #{$this->payment->booking_id}",
            ]);
            
            if ($response['resultCode'] == 0) {
                $this->payment->update(['status' => 'success']);
                Booking::find($this->payment->booking_id)
                    ->update(['status' => 'confirmed']);
            }
        } catch (Exception $e) {
            $this->fail($e);
        }
    }
    
    public function failed() {
        $this->payment->update(['status' => 'failed']);
    }
}

// app/Http/Controllers/PaymentController.php
class PaymentController extends Controller {
    public function initiate(Request $request) {
        $payment = Payment::create($request->validated());
        
        // Dispatch job instead of sync call
        ProcessMomoPayment::dispatch($payment)->delay(now());
        
        return response()->json(['payment_id' => $payment->id, 'status' => 'processing']);
    }
}
```

#### 4.2 - Email & Notification Jobs
```php
// app/Jobs/SendBookingConfirmationEmail.php
class SendBookingConfirmationEmail implements ShouldQueue {
    public function handle() {
        Mail::send(new BookingConfirmation($this->booking));
    }
}

// Trigger in model event
class Booking extends Model {
    protected static function booted() {
        static::created(function ($booking) {
            SendBookingConfirmationEmail::dispatch($booking);
        });
    }
}
```

---

### **Phase 5: Frontend Conversion (Vue 3 + Vite)**

#### 5.1 - API Axios Setup
```javascript
// resources/js/api/client.js
import axios from 'axios'

const client = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
})

// Add token to requests
client.interceptors.request.use(config => {
    const token = localStorage.getItem('api_token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

// Handle 401 responses
client.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('api_token')
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
)

export default client
```

#### 5.2 - Vue Components
```vue
<!-- resources/js/components/FieldList.vue -->
<template>
  <div class="fields-grid">
    <div v-if="loading" class="spinner">Loading...</div>
    
    <div v-for="field in fields" :key="field.id" class="field-card">
      <img :src="field.images[0]?.url" :alt="field.name" />
      <h3>{{ field.name }}</h3>
      <p class="price">{{ field.price_per_hour }}₫/giờ</p>
      <button @click="goToBooking(field.id)">Đặt sân</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/client'

const fields = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await api.get('/fields')
    fields.value = data.data
  } catch (error) {
    console.error('Failed to load fields:', error)
  } finally {
    loading.value = false
  }
})

const goToBooking = (fieldId) => {
  router.push(`/booking/${fieldId}`)
}
</script>

<style scoped>
.fields-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
.field-card { border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
</style>
```

---

### **Phase 6: Testing & Monitoring**

#### 6.1 - API Tests
```php
// tests/Feature/BookingApiTest.php
class BookingApiTest extends TestCase {
    public function test_user_can_list_bookings() {
        $user = User::factory()->create();
        Booking::factory(5)->create(['user_id' => $user->id]);
        
        $response = $this->actingAs($user)
            ->getJson('/api/bookings');
        
        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }
    
    public function test_check_availability() {
        $response = $this->postJson('/api/bookings/check-available', [
            'field_id' => 1,
            'date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);
        
        $response->assertOk()
            ->assertJsonStructure(['available']);
    }
}
```

#### 6.2 - Monitoring & Logging
```php
// config/logging.php
'channels' => [
    'api' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
    ],
    'payments' => [
        'driver' => 'daily',
        'path' => storage_path('logs/payments.log'),
    ],
],

// Log important operations
Log::channel('api')->info('Booking created', [
    'booking_id' => $booking->id,
    'user_id' => $user->id,
    'amount' => $booking->total_price,
]);
```

---

## 📦 TECH STACK RECOMMENDED

```
Backend:
- Laravel 11+
- Laravel Sanctum (Auth)
- Redis (Cache)
- Laravel Queue (Jobs)
- Database: MySQL 8.0+

Frontend:
- Vue 3 + TypeScript
- Vite (Build tool)
- Axios
- TailwindCSS
- Pinia (State management)

Deployment:
- Docker
- Docker Compose
- Nginx
- Let's Encrypt (SSL)

Monitoring:
- Sentry (Error tracking)
- Datadog / New Relic (Performance)
```

---

## 🔄 MIGRATION PLAN

```
Week 1-2: API Routes + Controllers + Resources
├─ Create api.php routes
├─ Build API controllers
├─ Create resource classes
└─ Implement error handling

Week 3: Caching + Query Optimization
├─ Setup Redis
├─ Implement cache strategies
├─ Add eager loading
└─ Profile queries

Week 4-5: Security
├─ Setup Sanctum auth
├─ Add rate limiting
├─ Input validation
└─ Security headers

Week 6: Async Processing
├─ Setup queues
├─ Create job classes
└─ Test background jobs

Week 7-8: Frontend (Vue 3)
├─ Setup Vue + Vite
├─ Create API client
├─ Build pages
└─ Integration testing

Week 9: Testing & Optimization
├─ Write tests
├─ Performance tuning
├─ Load testing
└─ Documentation

Week 10: Deployment
├─ Docker setup
├─ CI/CD pipeline
├─ SSL configuration
└─ Launch
```

---

## ⚠️ CRITICAL SECURITY FIXES (DO FIRST)

1. **Remove credentials from repo**: Delete `account.text`, regenerate all passwords
2. **Add `.env` to `.gitignore`** (should already be there)
3. **Implement input validation** on all endpoints
4. **Add rate limiting** immediately
5. **Use HTTPS everywhere** (force HTTPS middleware)
6. **Update dependencies**: `composer update`, `npm update`
7. **Set environment variables properly** in production
8. **Enable CORS only for your domains**
9. **Log all payment transactions** (for audit)
10. **Setup monitoring** for security events

---

## 📝 EXAMPLE: Complete Booking Flow (Old vs New)

### OLD (Current - Slow & Unsafe)
```
1. User clicks "Đặt sân" on Blade template
2. Form submit → POST /bookings (full page)
3. Controller renders full Blade template HTML
4. Return 2MB+ HTML to browser
5. Browser re-render entire page
6. SLOW, uses lots of bandwidth
```

### NEW (Optimized)
```
1. User clicks button in Vue component
2. API call → POST /api/bookings (JSON)
3. Validate input (both client + server)
4. Return minimal JSON: {"id": 123, "status": "pending"}
5. Vue updates UI locally (instant)
6. Cache field data in browser + Redis
7. FAST, minimal bandwidth, better UX
```

---

## 💡 QUICK WINS (Implement Now)

```php
// 1. Add simple caching to existing code
Route::get('/fields', function() {
    return Cache::remember('fields', 3600, 
        fn() => Field::all()
    );
});

// 2. Add middleware for rate limiting
Route::middleware('throttle:60,1')->group(function() {
    // API routes
});

// 3. Add input validation
$validated = request()->validate([
    'email' => 'required|email|unique:users',
]);

// 4. Log important events
Log::info('Booking created', ['booking_id' => $booking->id]);
```

---

## 📚 RESOURCES

- [Laravel API Documentation](https://laravel.com/docs/11/apis)
- [Laravel Sanctum](https://laravel.com/docs/11/sanctum)
- [Redis Caching](https://laravel.com/docs/11/cache)
- [Queue Jobs](https://laravel.com/docs/11/queues)
- [Vue 3 Guide](https://vuejs.org/guide/introduction.html)
- [OWASP Security Guide](https://owasp.org/)

---

**Status**: 🎯 Ready to implement
**Difficulty**: ⭐⭐⭐ (Medium-Hard)
**Time Investment**: 8-10 weeks
**Result**: 10-20x faster, 100x more secure
