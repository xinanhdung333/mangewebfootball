# 🔒 SECURITY CHECKLIST - QUICK REFERENCE

## NGAY LẬP TỨC (Do today!)

### 1. Remove Sensitive Files from Repo
```bash
# Step 1: Create .gitignore properly
echo "account.text" >> .gitignore
echo "hello.txt" >> .gitignore
echo "testbranch.txt" >> .gitignore
echo ".env" >> .gitignore
echo ".env.local" >> .gitignore

# Step 2: Remove from git history
git rm --cached account.text hello.txt testbranch.txt .env
git commit -m "Remove sensitive files"
git push

# Step 3: Regenerate all passwords/credentials
# - Change database password
# - Regenerate JWT secret
# - Change MoMo API credentials
# - Reset admin passwords
```

### 2. Force HTTPS
```php
// app/Http/Middleware/ForceHttps.php
class ForceHttps {
    public function handle($request, $next) {
        if (!app()->environment('local') && !$request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }
        return $next($request);
    }
}

// app/Http/Kernel.php
protected $middleware = [
    // ...
    \App\Http\Middleware\ForceHttps::class,
];
```

### 3. Security Headers
```php
// app/Http/Middleware/SecurityHeaders.php
class SecurityHeaders {
    public function handle($request, $next) {
        $response = $next($request);
        
        // Prevent clickjacking
        $response->header('X-Frame-Options', 'DENY');
        
        // Prevent MIME sniffing
        $response->header('X-Content-Type-Options', 'nosniff');
        
        // Enable XSS protection
        $response->header('X-XSS-Protection', '1; mode=block');
        
        // HSTS - force HTTPS for 1 year
        $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        
        // Content Security Policy
        $response->header('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline';");
        
        return $response;
    }
}
```

### 4. Update Dependencies
```bash
# Check for vulnerabilities
composer audit
npm audit

# Update packages
composer update
npm update

# Fix vulnerabilities
composer update --with-all-dependencies
npm audit fix
```

### 5. Disable Debug Mode in Production
```env
# .env production
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stack
CACHE_DRIVER=redis
SESSION_DRIVER=cookie
```

### 6. Strong Database Passwords
```bash
# Generate strong password
openssl rand -base64 32

# Update .env
DB_PASSWORD=YOUR_STRONG_PASSWORD_HERE
```

---

## TRONG 1 TUẦN

### 7. Rate Limiting on Login
```php
// app/Http/Controllers/AuthController.php
public function login(Request $request) {
    // Rate limit: 5 attempts per minute per email
    if (RateLimiter::tooManyAttempts('login:' . $request->email, 5)) {
        return response()->json(['message' => 'Too many login attempts'], 429);
    }
    
    if (!Auth::attempt($request->only('email', 'password'))) {
        RateLimiter::hit('login:' . $request->email, 60);
        return response()->json(['message' => 'Invalid credentials'], 401);
    }
    
    RateLimiter::clear('login:' . $request->email);
    return response()->json(['token' => auth()->user()->createToken('api')->plainTextToken]);
}
```

### 8. SQL Injection Prevention (Check All Queries)
```php
// ❌ BAD - Never do this
$bookings = DB::select("SELECT * FROM bookings WHERE user_id = " . $userId);

// ✅ GOOD - Use parameter binding
$bookings = DB::select("SELECT * FROM bookings WHERE user_id = ?", [$userId]);

// ✅ BETTER - Use Eloquent
$bookings = Booking::where('user_id', $userId)->get();
```

### 9. CSRF Protection
```php
// Already in Laravel, but ensure it's not disabled
// In routes/api.php - Don't add CSRF to API!
// CSRF should only protect state-changing web requests

// routes/web.php should use CSRF
Route::middleware(['csrf'])->group(function () {
    Route::post('/form-submission', ...);
});

// routes/api.php should NOT use CSRF (use token auth instead)
Route::middleware(['sanctum'])->group(function () {
    Route::post('/api/data', ...);
});
```

### 10. Input Validation & Sanitization
```php
// app/Http/Requests/BookingRequest.php
class BookingRequest extends FormRequest {
    public function authorize() {
        return auth()->check();
    }
    
    public function rules() {
        return [
            'field_id' => 'required|integer|exists:fields,id',
            'booking_date' => 'required|date|after:today|before:+1 year',
            'start_time' => 'required|date_format:H:i|between:06:00,23:00',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'services' => 'array',
            'services.*' => 'integer|exists:services,id',
            'notes' => 'nullable|string|max:500|regex:/^[a-zA-Z0-9\s\.\,\-\_\n]*$/',
        ];
    }
    
    public function messages() {
        return [
            'field_id.exists' => 'Sân bóng không hợp lệ',
            'booking_date.after' => 'Ngày đặt phải sau hôm nay',
            'notes.regex' => 'Ghi chú chứa ký tự không hợp lệ',
        ];
    }
}
```

### 11. Environment Variables
```env
# .env.example (commit to repo)
APP_NAME=Football_Booking
APP_ENV=production
APP_KEY=
APP_DEBUG=false

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=football_db
DB_USERNAME=root
DB_PASSWORD=

# Cache
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Auth
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=yourdomain.com

# Email
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls

# MoMo Payment
MOMO_PARTNER_CODE=your_partner_code
MOMO_ACCESS_KEY=your_access_key
MOMO_SECRET_KEY=your_secret_key
MOMO_ENDPOINT=https://test-payment.momo.vn/v2/gateway/api/create
```

### 12. Logging Sensitive Operations
```php
// app/Models/Booking.php
class Booking extends Model {
    protected static function booted() {
        static::created(function ($booking) {
            Log::channel('bookings')->info('Booking created', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'field_id' => $booking->field_id,
                'total_price' => $booking->total_price,
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);
        });
        
        static::updated(function ($booking) {
            Log::channel('bookings')->info('Booking updated', [
                'booking_id' => $booking->id,
                'changes' => $booking->getChanges(),
            ]);
        });
    }
}

// config/logging.php
'channels' => [
    'bookings' => [
        'driver' => 'daily',
        'path' => storage_path('logs/bookings.log'),
        'days' => 90,
    ],
    'payments' => [
        'driver' => 'daily',
        'path' => storage_path('logs/payments.log'),
        'days' => 365, // Keep payment logs for 1 year
    ],
],
```

---

## TRONG 2 TUẦN

### 13. API Versioning
```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/fields', [FieldController::class, 'index']);
});

// Can create v2 later without breaking v1
Route::prefix('v2')->group(function () {
    Route::post('/auth/login', [AuthControllerV2::class, 'login']);
});
```

### 14. API Documentation (Swagger/OpenAPI)
```php
// Install: composer require "darkaonline/l5-swagger"

/**
 * @OA\Post(
 *     path="/api/v1/auth/login",
 *     tags={"Auth"},
 *     summary="User login",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", format="email"),
 *             @OA\Property(property="password", type="string", format="password")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="token", type="string")
 *         )
 *     )
 * )
 */
public function login(Request $request) { ... }
```

### 15. Backup Strategy
```php
// config/backup.php (use spatie/laravel-backup)
return [
    'backup' => [
        'name' => 'football-booking',
        'source' => [
            'files' => [
                'include' => [base_path()],
                'exclude' => ['node_modules', 'storage/logs'],
            ],
            'databases' => ['mysql'],
        ],
        'destination' => [
            'disks' => ['backups'],
        ],
        'password' => env('BACKUP_PASSWORD'),
        'encryption' => 'openssl',
    ],
];

// Schedule backups
// app/Console/Kernel.php
protected function schedule(Schedule $schedule) {
    $schedule->command('backup:run')->daily()->at('02:00');
}
```

### 16. 2FA for Admin
```php
// composer require "laravel-lang/common" "qirolab/laravel-thump"

// Setup 2FA in admin users
class User extends Model {
    public function enable2FA() {
        $this->update([
            'two_factor_secret' => encrypt(google2fa()->generateSecretKey()),
        ]);
    }
}
```

---

## COMPLIANCE CHECKLIST

### GDPR Compliance
- [ ] User can request data export
- [ ] User can request account deletion
- [ ] Privacy policy displayed
- [ ] Cookie consent banner
- [ ] Data retention policy (delete old logs)

### PCI-DSS (Payment Security)
- [ ] Never store credit card numbers (use MoMo only)
- [ ] Log all payment transactions
- [ ] Encrypt sensitive data
- [ ] Regular security audits
- [ ] Disable unnecessary services

### Vietnam E-commerce Law
- [ ] Privacy policy in Vietnamese
- [ ] Return/refund policy clear
- [ ] Business registration displayed
- [ ] Contact information provided
- [ ] Complaint handling process

---

## MONITORING & ALERTS

### Setup Sentry (Error Tracking)
```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=YOUR_SENTRY_DSN
```

```php
// config/sentry.php
'dsn' => env('SENTRY_DSN'),
'breadcrumbs' => [
    'sql_bindings' => true,
    'logs' => true,
],
'traces_sample_rate' => 0.1, // 10% of transactions
```

### Setup Monitoring Dashboard
- Datadog / New Relic / Scout APM
- Monitor API response times
- Monitor database queries
- Alert on error spikes
- Monitor Redis cache hit rate

### Health Check Endpoint
```php
// routes/api.php
Route::get('/health', function() {
    return response()->json([
        'status' => 'ok',
        'database' => DB::connection()->getPdo() ? 'ok' : 'error',
        'cache' => Cache::get('health_check') === 'ok' ? 'ok' : 'error',
        'timestamp' => now()->toIso8601String(),
    ]);
});
```

---

## PENETRATION TESTING CHECKLIST

Hire a security firm to test:
- [ ] SQL Injection vulnerabilities
- [ ] XSS vulnerabilities  
- [ ] CSRF vulnerabilities
- [ ] Authentication bypass
- [ ] Authorization bypass
- [ ] API endpoint brute forcing
- [ ] Rate limiting effectiveness
- [ ] Password reset flow security
- [ ] Session management
- [ ] File upload vulnerabilities

**Estimated Cost**: $500-2000
**Recommended**: Every 6 months or after major changes

---

## TESTING SECURITY

```php
// tests/Feature/SecurityTest.php
class SecurityTest extends TestCase {
    public function test_unauthorized_user_cannot_access_admin() {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }
    
    public function test_rate_limiting_on_login() {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/api/auth/login', [
                'email' => 'user@example.com',
                'password' => 'wrong',
            ]);
        }
        
        $response = $this->post('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);
        
        $response->assertStatus(429); // Too Many Requests
    }
    
    public function test_sql_injection_prevented() {
        $response = $this->post('/api/bookings', [
            'field_id' => "1 OR 1=1",
            'notes' => "'; DROP TABLE bookings; --",
        ]);
        
        $response->assertStatus(422); // Validation error
    }
}
```

---

## DEPLOYMENT SECURITY

### Docker Security
```dockerfile
# Dockerfile
FROM php:8.2-fpm-alpine

# Run as non-root user
RUN addgroup -g 1000 appuser && adduser -D -u 1000 -G appuser appuser

# Copy only necessary files
COPY --chown=appuser:appuser . /var/www/html

# Set proper permissions
RUN chmod -R 755 /var/www/html/storage

USER appuser

EXPOSE 9000
```

### Nginx Security Headers
```nginx
# nginx.conf
server {
    # HTTPS only
    listen 443 ssl http2;
    ssl_certificate /etc/letsencrypt/live/domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/domain.com/privkey.pem;
    
    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer" always;
    
    # Hide server version
    server_tokens off;
    
    # Hide PHP version
    fastcgi_hide_header X-Powered-By;
}
```

---

## FINAL CHECKLIST

- [ ] All sensitive files removed from repo
- [ ] HTTPS forced in production
- [ ] All dependencies updated
- [ ] Rate limiting implemented
- [ ] Input validation on all endpoints
- [ ] SQL injection prevention verified
- [ ] CSRF protection enabled
- [ ] Security headers added
- [ ] Logging setup for sensitive operations
- [ ] Error handling doesn't expose sensitive info
- [ ] Database backups configured
- [ ] Monitoring/alerts setup
- [ ] Incident response plan documented
- [ ] Regular security updates scheduled
- [ ] Team trained on security practices

**Status**: Ready to secure!
**Priority**: 🔴 DO THIS FIRST before going production
