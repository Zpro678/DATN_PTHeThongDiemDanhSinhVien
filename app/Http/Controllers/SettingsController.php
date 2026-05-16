<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('pages.settings');
    }

    public function update(Request $request)
    {
        // TODO: validate & update user settings
        return redirect()->route('settings')->with('success', 'Cài đặt đã được lưu!');
    }
}
