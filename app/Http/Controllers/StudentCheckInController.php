<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentCheckInController extends Controller
{
    public function index()
    {
        return view('pages.student-checkin');
    }
}
