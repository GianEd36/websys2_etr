<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $reviews = auth()->user()->reviews()->latest()->get();

        return view('dashboard', compact('reviews'));
    }
}
