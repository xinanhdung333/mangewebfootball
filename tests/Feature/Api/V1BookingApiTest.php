<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Field;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V1BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_field_and_service_endpoints_return_active_records(): void
    {
        Field::create([
            'name' => 'San A',
            'location' => 'Quan 1',
            'price_per_hour' => 200000,
            'status' => 'active',
        ]);

        Service::create([
            'name' => 'Nuoc suoi',
            'price' => 10000,
            'quantity' => 20,
            'status' => 'active',
        ]);

        $this->getJson('/api/v1/fields')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'San A');

        $this->getJson('/api/v1/services')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Nuoc suoi');
    }

    public function test_user_can_login_and_use_token_for_booking_flow(): void
    {
        $user = User::factory()->create([
            'email' => 'api-user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $field = Field::create([
            'name' => 'San Token',
            'location' => 'Quan 3',
            'price_per_hour' => 300000,
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Ao bib',
            'price' => 20000,
            'quantity' => 5,
            'status' => 'active',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'expires_at', 'user'])
            ->json('token');

        $payload = [
            'field_id' => $field->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'services' => [
                $service->id => 2,
            ],
        ];

        $this->withToken($token)
            ->postJson('/api/v1/bookings/check-available', $payload)
            ->assertOk()
            ->assertJsonPath('available', true);

        $bookingId = $this->withToken($token)
            ->postJson('/api/v1/bookings', $payload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->json('data.id');

        $this->withToken($token)
            ->getJson('/api/v1/bookings')
            ->assertOk()
            ->assertJsonPath('data.0.id', $bookingId);

        $this->assertDatabaseHas('booking_payments', [
            'booking_id' => $bookingId,
            'status' => 'pending',
        ]);
    }

    public function test_check_available_detects_conflicting_booking(): void
    {
        $user = User::factory()->create();
        $field = Field::create([
            'name' => 'San Busy',
            'location' => 'Quan 7',
            'price_per_hour' => 250000,
            'status' => 'active',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'field_id' => $field->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:30',
            'total_price' => 375000,
            'status' => 'confirmed',
        ]);

        $this->postJson('/api/v1/bookings/check-available', [
            'field_id' => $field->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
        ])
            ->assertOk()
            ->assertJsonPath('available', false);
    }
}
