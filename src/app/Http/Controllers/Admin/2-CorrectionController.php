<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CorrectionController extends Controller
{
    public function adminCorrectionList(Request $request)
    {
        return view('admin_attendance_list', compact());
    }

    public function adminCorrection(Request $request)
    {
        return view('admin_attendance_list', compact());
    }
}
