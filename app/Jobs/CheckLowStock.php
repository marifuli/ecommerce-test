<?php

namespace App\Jobs;

use App\Mail\LowStockNotification;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class CheckLowStock implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Product $product,
        public int $threshold = 5
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Refresh product to get latest stock quantity
        $product = $this->product->fresh();

        // Check if product is still low in stock
        if ($product->stock_quantity <= $this->threshold) {
            $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
            
            Mail::to($adminEmail)->send(
                new LowStockNotification($product, $this->threshold)
            );
        }
    }
}
