<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Queue;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    public function index()
    {
        return view('display.index');
    }
}
