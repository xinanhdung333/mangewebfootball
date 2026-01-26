<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FEEDBACK TABLE DATA ===\n\n";
$feedbacks = DB::table('feedback')->get();
echo "Total feedback: " . count($feedbacks) . "\n\n";

if (count($feedbacks) > 0) {
    foreach ($feedbacks as $f) {
        echo json_encode($f, JSON_PRETTY_PRINT) . "\n";
    }
} else {
    echo "No feedback found\n";
}

echo "\n=== SERVICE FEEDBACK QUERY ===\n";
$serviceFeedbacks = DB::table('feedback')
    ->whereNotNull('feedback.service_id')
    ->whereNotNull('feedback.order_id')
    ->leftJoin('orders', 'feedback.order_id', '=', 'orders.id')
    ->leftJoin('services', 'feedback.service_id', '=', 'services.id')
    ->leftJoin('users', 'orders.user_id', '=', 'users.id')
    ->select(
        'feedback.id as feedback_id',
        'feedback.feedback',
        'feedback.rating',
        'users.name as user_name',
        'services.price as service_price'
    )
    ->get();
echo "Service feedback count: " . count($serviceFeedbacks) . "\n";
if (count($serviceFeedbacks) > 0) {
    foreach ($serviceFeedbacks as $sf) {
        echo json_encode($sf, JSON_PRETTY_PRINT) . "\n";
    }
}

echo "\n=== BOOKING FEEDBACK QUERY ===\n";
$bookingFeedbacks = DB::table('feedback')
    ->whereNotNull('feedback.booking_id')
    ->leftJoin('bookings', 'feedback.booking_id', '=', 'bookings.id')
    ->leftJoin('fields', 'bookings.field_id', '=', 'fields.id')
    ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
    ->select(
        'feedback.id as feedback_id',
        'bookings.id as booking_id',
        'feedback.feedback_message',
        'feedback.feedback_rating',
        'users.name as user_name',
        'fields.name as field_name',
        'bookings.start_time as booking_date',
        DB::raw("DATE_FORMAT(bookings.start_time, '%H:%i') as start_time"),
        DB::raw("DATE_FORMAT(bookings.end_time, '%H:%i') as end_time")
    )
    ->get();
echo "Booking feedback count: " . count($bookingFeedbacks) . "\n";
if (count($bookingFeedbacks) > 0) {
    foreach ($bookingFeedbacks as $bf) {
        echo json_encode($bf, JSON_PRETTY_PRINT) . "\n";
    }
}
?>
