<?php

use App\Models\Product;
use App\Models\User;

test('guests cannot access products page', function () {
    $this->get(route('products.index'))->assertRedirect(route('login'));
});

test('authenticated users can view products page', function () {
    $user = User::factory()->create();
    Product::factory()->count(5)->create();

    $response = $this->actingAs($user)->get(route('products.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('products/index')
        ->has('products', 5)
    );
});

test('products page shows product details', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => '99.99',
        'stock_quantity' => 10,
    ]);

    $response = $this->actingAs($user)->get(route('products.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('products/index')
        ->has('products', 1)
        ->where('products.0.name', 'Test Product')
        ->where('products.0.price', '99.99')
        ->where('products.0.stock_quantity', 10)
    );
});

test('products page shows empty state when no products exist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('products.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('products/index')
        ->has('products', 0)
    );
});

