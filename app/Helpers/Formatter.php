<?php

namespace App\Helpers;

class Formatter
{
    public static function formatCurrency($amount)
    {
        return number_format($amount, 0, ',', '.') . ' VNĐ';
    }

    public static function formatDateTime($datetime)
    {
        return date('d/m/Y H:i', strtotime($datetime));
    }
}
