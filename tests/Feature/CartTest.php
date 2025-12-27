<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

test('guests cannot access cart page', function () {
    $this->get(route('cart.index'))->assertRedirect(route('login'));
});

test('authenticated users can view empty cart', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('cart.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('cart/index')
        ->has('cart.items', 0)
        ->where('cart.total', '0.00')
    );
});

test('cart page shows cart items with products', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['price' => '50.00']);
    
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response = $this->actingAs($user)->get(route('cart.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('cart/index')
        ->has('cart.items', 1)
        ->where('cart.items.0.quantity', 2)
        ->where('cart.items.0.subtotal', '100.00')
    );
});

test('guests cannot add products to cart', function () {
    $product = Product::factory()->create();

    $response = $this->post(route('cart.store'), [
        'product_id' => $product->id,
    ]);

    $response->assertRedirect(route('login'));
});

test('authenticated users can add product to cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $response = $this->actingAs($user)->post(route('cart.store'), [
        'product_id' => $product->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
});

test('adding same product to cart increments quantity', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['stock_quantity' => 10]);
    
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($user)->post(route('cart.store'), [
        'product_id' => $product->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);
});

test('cannot add out of stock product to cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 0]);

    $response = $this->actingAs($user)->post(route('cart.store'), [
        'product_id' => $product->id,
    ]);

    $response->assertSessionHasErrors('product_id');
    $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
});

test('cannot add product when quantity exceeds stock', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['stock_quantity' => 5]);
    
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    $response = $this->actingAs($user)->post(route('cart.store'), [
        'product_id' => $product->id,
    ]);

    $response->assertSessionHasErrors('product_id');
});

test('cannot add invalid product to cart', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('cart.store'), [
        'product_id' => 99999,
    ]);

    $response->assertSessionHasErrors('product_id');
});

test('users can update cart item quantity', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($user)->put(route('cart.items.update', $cartItem), [
        'quantity' => 3,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItem->id,
        'quantity' => 3,
    ]);
});

test('cannot update quantity below 1', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($user)->put(route('cart.items.update', $cartItem), [
        'quantity' => 0,
    ]);

    $response->assertSessionHasErrors('quantity');
});

test('cannot update quantity above stock', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['stock_quantity' => 5]);
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($user)->put(route('cart.items.update', $cartItem), [
        'quantity' => 10,
    ]);

    $response->assertSessionHasErrors('quantity');
});

test('users cannot update other users cart items', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $cart2 = Cart::factory()->create(['user_id' => $user2->id]);
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart2->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($user1)->put(route('cart.items.update', $cartItem), [
        'quantity' => 3,
    ]);

    $response->assertForbidden();
});

test('users can remove cart item', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create();
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
    ]);

    $response = $this->actingAs($user)->delete(route('cart.items.destroy', $cartItem));

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
});

test('users cannot remove other users cart items', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $cart2 = Cart::factory()->create(['user_id' => $user2->id]);
    $product = Product::factory()->create();
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart2->id,
        'product_id' => $product->id,
    ]);

    $response = $this->actingAs($user1)->delete(route('cart.items.destroy', $cartItem));

    $response->assertForbidden();
    $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id]);
});

test('guests cannot checkout', function () {
    $response = $this->post(route('cart.checkout'));

    $response->assertRedirect(route('login'));
});

test('cannot checkout with empty cart', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('cart.checkout'));

    $response->assertSessionHasErrors('cart');
});

test('users can checkout successfully', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create([
        'price' => '50.00',
        'stock_quantity' => 10,
    ]);
    
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response = $this->actingAs($user)->post(route('cart.checkout'));

    $response->assertRedirect(route('cart.index'));
    $response->assertSessionHas('success');

    // Verify sale was created
    $this->assertDatabaseHas('sales', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => '50.00',
        'total' => '100.00',
    ]);

    // Verify stock was reduced
    $product->refresh();
    expect($product->stock_quantity)->toBe(8);

    // Verify cart items were cleared
    $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
});

test('cannot checkout when stock is insufficient', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['stock_quantity' => 5]);
    
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $response = $this->actingAs($user)->post(route('cart.checkout'));

    $response->assertSessionHasErrors('stock');
    $this->assertDatabaseMissing('sales', ['product_id' => $product->id]);
});

test('checkout processes multiple products correctly', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $product1 = Product::factory()->create(['price' => '30.00', 'stock_quantity' => 10]);
    $product2 = Product::factory()->create(['price' => '20.00', 'stock_quantity' => 10]);
    
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product1->id,
        'quantity' => 2,
    ]);
    
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product2->id,
        'quantity' => 3,
    ]);

    $response = $this->actingAs($user)->post(route('cart.checkout'));

    $response->assertRedirect(route('cart.index'));

    // Verify both sales were created
    $this->assertDatabaseHas('sales', [
        'product_id' => $product1->id,
        'quantity' => 2,
        'total' => '60.00',
    ]);
    
    $this->assertDatabaseHas('sales', [
        'product_id' => $product2->id,
        'quantity' => 3,
        'total' => '60.00',
    ]);

    // Verify stocks were reduced
    $product1->refresh();
    $product2->refresh();
    expect($product1->stock_quantity)->toBe(8);
    expect($product2->stock_quantity)->toBe(7);
});

