<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    function index()
    {
        $data = [
            'products' => \App\Models\Product::all(),
        ];

        if (Auth::check()) {
            $data['cart'] = Auth::user()->cart;
        }

        return Inertia::render('products/index', $data);
    }
}
