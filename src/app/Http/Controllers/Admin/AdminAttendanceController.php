<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAttendanceController extends Controller
{
    public function adminList(Request $request)
    {
        return view('admin.admin_attendance_list');
    }

    public function adminAttendance(Request $request)
    {
        return view('admin.admin_attendance_detail');
    }
}