<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// app/Models/HomeSetting.php
class HomeSetting extends Model
{
    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'promotion_text',
        'about_text',
    ];
}
