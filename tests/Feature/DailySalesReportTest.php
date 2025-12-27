<?php

use App\Console\Commands\SendDailySalesReport;
use App\Mail\DailySalesReport;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('daily sales report command generates report for yesterday', function () {
    Mail::fake();
    
    $user = User::factory()->create();
    $product1 = Product::factory()->create(['price' => '50.00']);
    $product2 = Product::factory()->create(['price' => '30.00']);
    
    // Create sales for yesterday
    Sale::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product1->id,
        'quantity' => 2,
        'price' => '50.00',
        'total' => '100.00',
        'created_at' => now()->yesterday(),
    ]);
    
    Sale::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product2->id,
        'quantity' => 3,
        'price' => '30.00',
        'total' => '90.00',
        'created_at' => now()->yesterday(),
    ]);

    $this->artisan('sales:daily-report')
        ->assertSuccessful();

    Mail::assertSent(DailySalesReport::class, function ($mail) {
        return $mail->totalRevenue === 190.00
            && $mail->totalItemsSold === 5
            && count($mail->salesData) === 2;
    });
});

test('daily sales report command can generate report for specific date', function () {
    Mail::fake();
    
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => '50.00']);
    
    $specificDate = now()->subDays(5)->format('Y-m-d');
    
    Sale::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => '50.00',
        'total' => '100.00',
        'created_at' => $specificDate,
    ]);

    $this->artisan("sales:daily-report --date={$specificDate}")
        ->assertSuccessful();

    Mail::assertSent(DailySalesReport::class, function ($mail) use ($specificDate) {
        return $mail->reportDate === $specificDate
            && $mail->totalRevenue === 100.00;
    });
});

test('daily sales report command handles no sales gracefully', function () {
    Mail::fake();

    $this->artisan('sales:daily-report')
        ->expectsOutput('No sales found for the specified date.')
        ->assertSuccessful();

    Mail::assertNothingSent();
});

test('daily sales report aggregates sales by product correctly', function () {
    Mail::fake();
    
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => '50.00']);
    
    // Multiple sales of same product
    Sale::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => '50.00',
        'total' => '100.00',
        'created_at' => now()->yesterday(),
    ]);
    
    Sale::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'price' => '50.00',
        'total' => '150.00',
        'created_at' => now()->yesterday(),
    ]);

    $this->artisan('sales:daily-report')
        ->assertSuccessful();

    Mail::assertSent(DailySalesReport::class, function ($mail) use ($product) {
        $productData = collect($mail->salesData)->firstWhere('product_id', $product->id);
        
        return $productData['quantity_sold'] === 5
            && $productData['revenue'] === 250.00;
    });
});

test('daily sales report email has correct content', function () {
    $salesData = [
        [
            'product_id' => 1,
            'product_name' => 'Test Product',
            'quantity_sold' => 5,
            'revenue' => 250.00,
        ],
    ];
    
    $mailable = new DailySalesReport(
        $salesData,
        '2025-12-27',
        250.00,
        5
    );
    
    $mailable->assertSeeInHtml('2025-12-27');
    $mailable->assertSeeInHtml('$250.00');
    $mailable->assertSeeInHtml('5');
    $mailable->assertSeeInHtml('Test Product');
});

test('daily sales report excludes sales from other dates', function () {
    Mail::fake();
    
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => '50.00']);
    
    // Sale from yesterday
    Sale::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => '50.00',
        'total' => '100.00',
        'created_at' => now()->yesterday(),
    ]);
    
    // Sale from today (should be excluded)
    Sale::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => '50.00',
        'total' => '50.00',
        'created_at' => now(),
    ]);

    $this->artisan('sales:daily-report')
        ->assertSuccessful();

    Mail::assertSent(DailySalesReport::class, function ($mail) {
        return $mail->totalRevenue === 100.00
            && $mail->totalItemsSold === 2;
    });
});

