<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function provider()
    {
        return view('admin.provider');
    }


    public function editJobber()
    {
        return view('admin.edit-jobber');
    }
}
