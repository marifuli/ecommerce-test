<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(): Response
    {
        $products = Product::all();

        return Inertia::render('products/index', [
            'products' => $products,
        ]);
    }
}
