<?php

use App\Jobs\CheckLowStock;
use App\Mail\LowStockNotification;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

test('low stock job is dispatched after checkout when stock is low', function () {
    Queue::fake();
    
    $user = \App\Models\User::factory()->create();
    $cart = \App\Models\Cart::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['stock_quantity' => 6]);
    
    \App\Models\CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)->post(route('cart.checkout'));

    Queue::assertPushed(CheckLowStock::class, function ($job) use ($product) {
        return $job->product->id === $product->id && $job->threshold === 5;
    });
});

test('low stock job sends email when stock is below threshold', function () {
    Mail::fake();
    
    $product = Product::factory()->create(['stock_quantity' => 3]);
    $job = new CheckLowStock($product, 5);
    
    $job->handle();
    
    Mail::assertSent(LowStockNotification::class, function ($mail) use ($product) {
        return $mail->product->id === $product->id && $mail->threshold === 5;
    });
});

test('low stock job does not send email when stock is above threshold', function () {
    Mail::fake();
    
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $job = new CheckLowStock($product, 5);
    
    $job->handle();
    
    Mail::assertNothingSent();
});

test('low stock notification email has correct content', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => '99.99',
        'stock_quantity' => 3,
    ]);
    
    $mailable = new LowStockNotification($product, 5);
    
    $mailable->assertSeeInHtml('Test Product');
    $mailable->assertSeeInHtml('3');
    $mailable->assertSeeInHtml('5');
    $mailable->assertSeeInHtml('$99.99');
});

