<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

test('cart item belongs to cart', function () {
    $cart = Cart::factory()->create();
    $cartItem = CartItem::factory()->create(['cart_id' => $cart->id]);
    
    expect($cartItem->cart)->toBeInstanceOf(Cart::class);
    expect($cartItem->cart->id)->toBe($cart->id);
});

test('cart item belongs to product', function () {
    $product = Product::factory()->create();
    $cartItem = CartItem::factory()->create(['product_id' => $product->id]);
    
    expect($cartItem->product)->toBeInstanceOf(Product::class);
    expect($cartItem->product->id)->toBe($product->id);
});

test('cart item has correct fillable attributes', function () {
    $cartItem = new CartItem();
    
    expect($cartItem->getFillable())->toBe(['cart_id', 'product_id', 'quantity']);
});

