<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function adminList(Request $request)
    {
        return view('admin_attendance_list', compact());
    }

    public function adminAttendance(Request $request)
    {
        return view('admin_attendance_list', compact());
    }
}