<?php

use App\Helpers\Formatter;

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount)
    {
        return Formatter::formatCurrency($amount);
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime($datetime)
    {
        return Formatter::formatDateTime($datetime);
    }
}
