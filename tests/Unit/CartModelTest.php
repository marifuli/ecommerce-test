<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;

test('cart belongs to user', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    
    expect($cart->user)->toBeInstanceOf(User::class);
    expect($cart->user->id)->toBe($user->id);
});

test('cart has many cart items', function () {
    $cart = Cart::factory()->create();
    CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);
    
    expect($cart->cartItems)->toHaveCount(3);
    expect($cart->cartItems->first())->toBeInstanceOf(CartItem::class);
});

test('cart has correct fillable attributes', function () {
    $cart = new Cart();
    
    expect($cart->getFillable())->toBe(['user_id']);
});

