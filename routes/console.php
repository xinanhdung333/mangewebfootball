<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use App\Models\Booking;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bookings:auto-update-status', function () {
    $result = Booking::autoUpdateBookingStatus();

    $this->info(sprintf(
        'Bookings updated: %d expired, %d in progress, %d completed.',
        $result['expired'],
        $result['in_progress'],
        $result['completed']
    ));
})->purpose('Auto update booking statuses by time and unpaid expiry');

Schedule::command('bookings:auto-update-status')->everyMinute()->withoutOverlapping();
