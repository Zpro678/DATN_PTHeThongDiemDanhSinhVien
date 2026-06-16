<?php

namespace App\Http\Controllers\Lecture;

use App\Http\Controllers\Controller;


class AttendanceSessionController extends Controller
{
    public function index() {
        return view('lecture.attendance.management_attendace');
    }
}