<?php

namespace App\Console\Commands;

use App\Mail\DailySalesReport;
use App\Models\Sale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailySalesReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:daily-report {--date= : The date to generate report for (Y-m-d format, defaults to yesterday)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send daily sales report to admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the date for the report (defaults to yesterday)
        $dateOption = $this->option('date');
        
        if ($dateOption) {
            $reportDate = \Carbon\Carbon::parse($dateOption)->startOfDay();
        } else {
            $reportDate = \Carbon\Carbon::yesterday()->startOfDay();
        }

        $endDate = $reportDate->copy()->endOfDay();

        $this->info("Generating sales report for: {$reportDate->format('Y-m-d')}");

        // Get all sales for the specified date
        $sales = Sale::with(['product', 'user'])
            ->whereBetween('created_at', [$reportDate, $endDate])
            ->get();

        if ($sales->isEmpty()) {
            $this->warn('No sales found for the specified date.');
            return Command::SUCCESS;
        }

        // Aggregate sales by product
        $salesData = [];
        $totalRevenue = 0;
        $totalItemsSold = 0;

        foreach ($sales as $sale) {
            $productId = $sale->product_id;
            $productName = $sale->product->name;

            if (!isset($salesData[$productId])) {
                $salesData[$productId] = [
                    'product_id' => $productId,
                    'product_name' => $productName,
                    'quantity_sold' => 0,
                    'revenue' => 0,
                ];
            }

            $salesData[$productId]['quantity_sold'] += $sale->quantity;
            $salesData[$productId]['revenue'] += (float) $sale->total;
            $totalRevenue += (float) $sale->total;
            $totalItemsSold += $sale->quantity;
        }

        // Convert to array and sort by revenue descending
        $salesData = array_values($salesData);
        usort($salesData, fn($a, $b) => $b['revenue'] <=> $a['revenue']);

        // Send email
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        
        try {
            Mail::to($adminEmail)->send(
                new DailySalesReport(
                    $salesData,
                    $reportDate->format('Y-m-d'),
                    $totalRevenue,
                    $totalItemsSold
                )
            );

            $this->info("Daily sales report sent successfully to {$adminEmail}");
            $this->info("Total Revenue: $" . number_format($totalRevenue, 2));
            $this->info("Total Items Sold: {$totalItemsSold}");
            $this->info("Products Sold: " . count($salesData));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to send daily sales report: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
