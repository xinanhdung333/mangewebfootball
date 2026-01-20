<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Field;

class FieldsController extends Controller
{
    public function index()
    {
        // use the Eloquent scope to include ratings
        $fields = Field::withRatings()->get();
        return view('fields.index', ['fields' => $fields]);
    }
}
