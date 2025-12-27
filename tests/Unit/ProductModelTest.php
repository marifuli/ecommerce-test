<?php

use App\Models\CartItem;
use App\Models\Product;

test('product has correct fillable attributes', function () {
    $product = new Product();
    
    expect($product->getFillable())->toBe(['name', 'price', 'stock_quantity']);
});

test('product casts price to decimal', function () {
    $product = Product::factory()->create(['price' => '99.99']);
    
    expect($product->price)->toBe('99.99');
    expect($product->getAttributes()['price'])->toBe('99.99');
});

test('product has cart items relationship', function () {
    $product = Product::factory()->create();
    CartItem::factory()->count(3)->create(['product_id' => $product->id]);
    
    expect($product->cartItems)->toHaveCount(3);
    expect($product->cartItems->first())->toBeInstanceOf(CartItem::class);
});

