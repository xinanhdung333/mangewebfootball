# 🚀 PERFORMANCE TESTING & OPTIMIZATION GUIDE

## Mục tiêu

```
Hiện tại: Average response time ~500-800ms, memory usage ~50MB+
Mục tiêu: Average response time <100ms, memory usage <30MB

Cải thiện:
- Response time: 5-8x nhanh hơn
- Throughput: Từ 20 req/s → 200+ req/s
- Database queries: Giảm từ 10-15 queries → 2-3 queries
```

---

## PHẦN 1: Baseline Testing (Before Optimization)

### 1.1 Setup Testing Tools

```bash
# Install Apache Bench (macOS)
brew install httpd

# Install Apache Bench (Ubuntu)
sudo apt-get install apache2-utils

# Or use wrk (more advanced)
brew install wrk

# Or use vegeta (for load testing)
go install github.com/tsenart/vegeta@latest
```

### 1.2 Test Current Performance

```bash
# Single request time
time curl -X GET http://localhost:8000/api/fields

# 100 requests with 10 concurrent
ab -n 100 -c 10 http://localhost:8000/api/fields

# Wrk test (10 threads, 100 connections, 30 seconds)
wrk -t10 -c100 -d30s http://localhost:8000/api/fields

# Vegeta test
echo "GET http://localhost:8000/api/fields" | vegeta attack -duration=30s -rate=100 | vegeta report

# Expected output example:
# Requests/sec:      15.45
# Latencies:
#   50%:     32.123ms
#   95%:     89.456ms
#   99%:    234.567ms
# Transfer rate:     2.34 Mbit/s
```

### 1.3 Monitor System Resources

```bash
# Monitor CPU, Memory, Disk
watch -n 1 'top -bn1 | head -20'

# Monitor network
vnstat -h

# Monitor MySQL queries
mysql -u root -p -e "SHOW PROCESSLIST;"
```

---

## PHẦN 2: Database Query Optimization

### 2.1 Identify Slow Queries

```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 0.1;  -- Queries slower than 100ms

-- Check slow queries
SHOW VARIABLES LIKE 'slow_query%';

-- View slow query log
tail -f /var/log/mysql/slow-query.log
```

### 2.2 Check Laravel Queries

```php
// In development, enable query logging
// app/Providers/AppServiceProvider.php

use Illuminate\Support\Facades\DB;

public function boot()
{
    if (app()->isLocal()) {
        DB::listen(function ($query) {
            \Log::info($query->sql, $query->bindings, $query->time);
        });
    }
}
```

### 2.3 Use Laravel Debugbar

```bash
# Install Laravel Debugbar
composer require barryvdh/laravel-debugbar --dev

# Visit /api/fields and check debugbar for:
# - Number of queries
# - Execution time
# - Memory usage
```

### 2.4 Optimization Examples

#### Problem 1: N+1 Query Problem

```php
// ❌ BAD - 11 queries (1 for bookings + 10 for each field)
$bookings = Booking::all();
foreach ($bookings as $booking) {
    echo $booking->field->name;  // 10 queries!
}

// ✅ GOOD - Only 1 query
$bookings = Booking::with('field')->get();
foreach ($bookings as $booking) {
    echo $booking->field->name;  // No more queries
}
```

#### Problem 2: Too Many Columns

```php
// ❌ BAD - Fetches all columns
$fields = Field::all();

// ✅ GOOD - Fetch only needed columns
$fields = Field::select('id', 'name', 'price_per_hour')->get();
```

#### Problem 3: Missing Indexes

```sql
-- Check existing indexes
SHOW INDEX FROM bookings;

-- Add indexes for frequently queried columns
ALTER TABLE bookings ADD INDEX idx_user_id (user_id);
ALTER TABLE bookings ADD INDEX idx_field_id (field_id);
ALTER TABLE bookings ADD INDEX idx_booking_date (booking_date);
ALTER TABLE bookings ADD INDEX idx_status (status);

-- Composite indexes
ALTER TABLE bookings ADD INDEX idx_field_date_status (field_id, booking_date, status);
```

### 2.5 Query Optimization Checklist

```php
// Model with all optimizations
class Booking extends Model
{
    // 1. Define relationships properly
    protected $with = ['field', 'user', 'services'];
    
    // 2. Select only needed columns
    protected $attributes = [];
    
    // 3. Add scopes for common queries
    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', today())
            ->orderBy('booking_date');
    }
    
    // 4. Cache expensive calculations
    public function getTotalPriceAttribute()
    {
        return cache()->remember(
            "booking_{$this->id}_total",
            3600,
            fn() => $this->field_price + $this->services_total
        );
    }
}

// In controller - use optimized query
public function index()
{
    return Booking::upcoming()
        ->paginate(15);  // Don't forget pagination!
}
```

---

## PHẦN 3: Caching Strategy

### 3.1 Cache Configuration

```env
# .env
CACHE_DRIVER=redis
CACHE_ALLOW_EMPTY_STRING=true
```

### 3.2 Cache Different Data Types

```php
// 1. Cache Configuration (24 hours)
Cache::rememberForever('app_config', function () {
    return Config::query()->get();
});

// 2. Cache Master Data (1 hour)
Cache::remember('fields_list', 3600, function () {
    return Field::with('images', 'services')->get();
});

// 3. Cache User Data (30 minutes)
$bookings = Cache::remember(
    'user_bookings_' . auth()->id(),
    1800,
    fn() => auth()->user()->bookings()->get()
);

// 4. Cache API Responses (varies)
$fields = Cache::remember('api_fields_page_1', 3600, function () {
    return Field::paginate(15);
});

// 5. Cache Calculations (depends on frequency)
$revenue = Cache::remember('monthly_revenue', 86400, function () {
    return Payment::whereMonth('created_at', now()->month)->sum('amount');
});
```

### 3.3 Cache Invalidation Strategy

```php
// In model events
class Booking extends Model
{
    protected static function booted()
    {
        static::created(function ($booking) {
            // Invalidate user's booking cache
            Cache::forget('user_bookings_' . $booking->user_id);
            
            // Invalidate field availability
            Cache::forget("field_{$booking->field_id}_availability");
            
            // Invalidate revenue cache
            Cache::forget('monthly_revenue');
        });
    }
}

// Or manually in controller
public function store(BookingRequest $request)
{
    $booking = Booking::create($request->validated());
    
    Cache::tags('bookings')->flush();  // Flush all bookings cache
    Cache::forget('user_bookings_' . auth()->id());
    
    return new BookingResource($booking);
}
```

### 3.4 Cache Hit Rate Monitoring

```php
// Check cache effectiveness
use Redis;

$redis = Redis::connection();

// Get cache stats
$info = $redis->info('stats');
echo $info['keyspace_hits'];      // Successful cache hits
echo $info['keyspace_misses'];    // Cache misses

// Calculate hit rate
$hitRate = $hits / ($hits + $misses) * 100;
echo "Cache hit rate: {$hitRate}%";

// Target: > 80% hit rate
```

---

## PHẦN 4: Frontend Optimization

### 4.1 Lazy Loading

```vue
<template>
  <!-- Load images only when visible -->
  <img v-lazy="field.image_url" :alt="field.name" />
  
  <!-- Lazy load components -->
  <Suspense>
    <template #default>
      <FieldDetails :field-id="fieldId" />
    </template>
    <template #fallback>
      <LoadingSpinner />
    </template>
  </Suspense>
</template>

<script setup>
import { defineAsyncComponent } from 'vue'

// Code splitting
const FieldDetails = defineAsyncComponent(() =>
  import('@/components/FieldDetails.vue')
)
</script>
```

### 4.2 HTTP/2 Push

```nginx
# nginx.conf
http2_push_resources "/css/style.css";
http2_push_resources "/js/app.js";
```

### 4.3 Content Compression

```php
// Laravel automatically gzip responses
// But ensure it's enabled in nginx

# nginx.conf
gzip on;
gzip_types text/plain text/css text/xml text/javascript 
           application/x-javascript application/xml+rss 
           application/javascript application/json;
gzip_min_length 1000;
gzip_disable "msie6";
```

### 4.4 API Response Pagination

```php
// Always paginate large responses
public function index(Request $request)
{
    return Booking::paginate(
        $request->per_page ?? 15  // Default 15 per page
    );
}

// Frontend handles pagination
const { data, links, meta } = await api.get('/bookings', {
    params: { page: currentPage, per_page: 15 }
});
```

---

## PHẦN 5: Database Connection Pooling

### 5.1 Setup Connection Pooling with PgBouncer (if using PostgreSQL)

```bash
# Or use MySQL connection pooling
# Use ProxySQL for MySQL
```

### 5.2 Laravel Connection Configuration

```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST'),
    'pool' => [
        'min_idle' => 2,
        'max_size' => 10,
    ],
],
```

---

## PHẦN 6: Load Testing

### 6.1 Prepare Test Environment

```bash
# Create test database
php artisan tinker
DB::table('bookings')->truncate();
Booking::factory(1000)->create();

# Or seed properly
php artisan db:seed --class=BookingSeeder
```

### 6.2 Load Test Different Endpoints

```bash
# Test 1: List fields (should be cached)
wrk -t4 -c50 -d60s \
  -s /path/to/script.lua \
  http://localhost:8000/api/fields

# Test 2: List user bookings (auth required)
# Create script.lua
request = function()
  wrk.headers["Authorization"] = "Bearer " .. token
  return wrk.format(nil, "/api/bookings")
end

wrk -t4 -c50 -d60s \
  -s script.lua \
  http://localhost:8000/api/bookings

# Test 3: Create booking (complex operation)
# Create booking_test.lua
request = function()
  local payload = "{\"field_id\":1,\"booking_date\":\"2024-01-15\",\"start_time\":\"10:00\",\"end_time\":\"11:00\"}"
  wrk.headers["Authorization"] = "Bearer " .. token
  wrk.headers["Content-Type"] = "application/json"
  return wrk.format("POST", "/api/bookings", payload)
end

wrk -t2 -c20 -d60s \
  -s booking_test.lua \
  http://localhost:8000/api/bookings
```

### 6.3 Monitor During Load Test

```bash
# Terminal 1: Run load test
wrk -t10 -c100 -d120s http://localhost:8000/api/fields

# Terminal 2: Monitor CPU/Memory
watch -n 1 'top -bn1 | head -20'

# Terminal 3: Monitor Redis
watch -n 1 'redis-cli INFO stats'

# Terminal 4: Monitor MySQL
watch -n 1 'mysql -e "SHOW PROCESSLIST;" -u root -p'

# Terminal 5: Monitor Logs
tail -f storage/logs/laravel.log
```

---

## PHẦN 7: Performance Metrics

### 7.1 Key Metrics to Track

```php
// Track in monitoring service (Sentry, Datadog, etc)
use Illuminate\Support\Facades\Log;

Log::channel('performance')->info('API Request', [
    'endpoint' => request()->path(),
    'method' => request()->method(),
    'duration_ms' => microtime(true) - LARAVEL_START,
    'memory_mb' => memory_get_peak_usage(true) / 1024 / 1024,
    'queries' => count(DB::getQueryLog()),
    'cache_hits' => $cacheHits,
    'status_code' => response()->status(),
]);
```

### 7.2 Performance Targets

```
Tier 1 (Excellent):
- Response time: < 100ms
- Cache hit rate: > 90%
- Error rate: < 0.1%
- Throughput: > 500 req/s

Tier 2 (Good):
- Response time: 100-250ms
- Cache hit rate: 70-90%
- Error rate: < 1%
- Throughput: > 100 req/s

Tier 3 (Acceptable):
- Response time: 250-500ms
- Cache hit rate: > 50%
- Error rate: < 5%
- Throughput: > 50 req/s

Tier 4 (Poor):
- Response time: > 500ms
- Cache hit rate: < 50%
- Error rate: > 5%
- Throughput: < 50 req/s
```

---

## PHẦN 8: Optimization Checklist

### Before Optimization
- [ ] Record baseline metrics
- [ ] Identify bottlenecks
- [ ] Setup monitoring

### Database Optimization
- [ ] Add proper indexes
- [ ] Use eager loading (with)
- [ ] Remove N+1 queries
- [ ] Select only needed columns
- [ ] Use query scopes

### Caching Optimization
- [ ] Cache master data
- [ ] Cache calculations
- [ ] Cache API responses
- [ ] Setup cache invalidation
- [ ] Monitor cache hit rate

### Frontend Optimization
- [ ] Lazy load images
- [ ] Code splitting
- [ ] Gzip compression
- [ ] Pagination

### Infrastructure
- [ ] Use Redis connection pooling
- [ ] Setup load balancer
- [ ] Enable HTTP/2
- [ ] Setup CDN for static files

### Monitoring
- [ ] Setup error tracking (Sentry)
- [ ] Setup APM (Datadog, New Relic)
- [ ] Setup alerts for performance degradation
- [ ] Regular load testing

---

## PHẦN 9: Common Bottlenecks & Solutions

### Bottleneck 1: Slow Database Queries
```
Problem: Query takes > 1 second
Solution:
  - Add indexes
  - Use EXPLAIN to analyze
  - Break into multiple queries
  - Cache results
```

### Bottleneck 2: Too Many Requests
```
Problem: 100 requests to get data
Solution:
  - Combine endpoints
  - Use batch requests
  - Implement GraphQL
```

### Bottleneck 3: Large Response Size
```
Problem: API response > 1MB
Solution:
  - Use pagination
  - Select fewer columns
  - Compress with gzip
  - Use CDN
```

### Bottleneck 4: Memory Leaks
```
Problem: Memory grows over time
Solution:
  - Profile with XDebug
  - Check for circular references
  - Use php artisan tinker:profile
```

### Bottleneck 5: High CPU Usage
```
Problem: CPU at 100%
Solution:
  - Move processing to queue
  - Use caching
  - Optimize algorithms
  - Scale horizontally
```

---

## PHẦN 10: Expected Improvements

### Before Optimization
```
GET /api/fields
- Response time: 450ms
- Database queries: 8
- Memory: 45MB
- Throughput: 20 req/s
```

### After Optimization
```
GET /api/fields
- Response time: 45ms (10x faster)
- Database queries: 1 (cached)
- Memory: 25MB
- Throughput: 500+ req/s
```

### Optimization Breakdown
- Database: 200ms → 20ms (-180ms, 90% improvement)
- Redis cache: 50ms
- App processing: 50ms → 5ms (-45ms)
- Network: 150ms → 20ms
- Total: 450ms → 45ms (10x)

---

## Tools Recommendation

```
Load Testing:
- wrk (CLI)
- Apache Bench (CLI)
- Vegeta (CLI)
- K6 (Cloud-based)

Monitoring:
- Sentry (Error tracking)
- Datadog (APM)
- New Relic (APM)
- Scout APM (Performance)

Database:
- MySQL Workbench (GUI)
- DBeaver (GUI)
- pgAdmin (PostgreSQL)

Profiling:
- XDebug (PHP)
- Blackfire (PHP profiler)
- Laravel Debugbar
```

---

## Target Performance After Implementation

```
Metric                    Before      After       Improvement
Response Time             450ms       45ms        10x faster
Throughput                20 req/s    500 req/s   25x faster
Database Queries          8           1           87.5% fewer
Cache Hit Rate            0%          85%         -
Memory Usage              45MB        25MB        44% less
Error Rate                2%          0.1%        20x lower
```

**Expected Result**: Can handle 10x more concurrent users with same hardware!
