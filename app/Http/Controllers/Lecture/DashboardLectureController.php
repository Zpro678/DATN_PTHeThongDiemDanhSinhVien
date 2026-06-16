<?php

namespace App\Http\Controllers\Lecture;


use App\Http\Controllers\Controller;


class DashboardLectureController extends Controller
{
    public function index() {
        return view('lecture.dashboard.index');
    }
}