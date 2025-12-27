# Ecommerce Test Project

A small ecommerce application built with Laravel and React for a job application exam.

## Description

This project serves as a foundation for an ecommerce application, demonstrating a modern full-stack setup using Laravel as the backend API and React with Inertia.js for a seamless single-page application experience. It includes essential features like user authentication, profile management, and a responsive UI, providing a solid starting point for building ecommerce functionalities such as product listings, shopping carts, and order management.

## Tech Stack

- **Backend**: Laravel 12.0, PHP 8.2+
- **Frontend**: React 19.2.0, TypeScript
- **SPA Framework**: Inertia.js
- **Styling**: Tailwind CSS 4.0, shadcn/ui component library
- **Authentication**: Laravel Fortify
- **Build Tool**: Vite
- **Database**: SQLite (default), configurable to MySQL/PostgreSQL
- **Testing**: Pest
- **Code Quality**: ESLint, Prettier

## Features

- User registration and login
- Email verification
- Two-factor authentication (2FA)
- User profile management
- Password change functionality
- Appearance settings (theme customization)
- Responsive design with mobile-friendly navigation
- Server-side rendering (SSR) support
- Modern UI components with Radix UI primitives

## Installation

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd ecommerce-test
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Set up environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database** (optional, defaults to SQLite):
   Update `.env` with your database credentials and run:
   ```bash
   php artisan migrate
   ```

5. **Install Node.js dependencies**:
   ```bash
   npm install
   ```

6. **Build assets**:
   ```bash
   npm run build
   ```

Alternatively, use the provided setup script:
```bash
composer run setup
```

## Usage

### Development

Start the development server with hot reloading:
```bash
composer run dev
```

This command runs:
- Laravel server on `http://localhost:8000`
- Queue worker
- Vite dev server for frontend assets
- Log monitoring

### Production

1. Build optimized assets:
   ```bash
   npm run build
   ```

2. Start the application:
   ```bash
   php artisan serve
   ```

### Testing

Run the test suite:
```bash
composer run test
```

### Code Quality

- Lint code: `npm run lint`
- Format code: `npm run format`
- Check types: `npm run types`

## Project Structure

```
├── app/                    # Laravel application code
│   ├── Actions/           # Custom actions (Fortify)
│   ├── Http/Controllers/  # Controllers
│   ├── Models/            # Eloquent models
│   └── Providers/         # Service providers
├── database/              # Migrations, factories, seeders
├── public/                # Public assets
├── resources/             # Views and frontend assets
│   ├── css/              # Stylesheets
│   ├── js/               # React application
│   │   ├── components/   # Reusable UI components
│   │   ├── layouts/      # Page layouts
│   │   ├── pages/        # Inertia pages
│   │   └── types/        # TypeScript definitions
│   └── views/            # Blade templates
├── routes/                # Route definitions
├── storage/               # File storage
├── tests/                 # Test files
├── vite.config.ts         # Vite configuration
├── tailwind.config.js     # Tailwind CSS configuration
└── composer.json          # PHP dependencies
```

## Key Technologies Explained

- **Laravel**: PHP framework for backend logic, routing, and database interactions
- **Inertia.js**: Bridges Laravel and React, allowing server-side routing with client-side rendering
- **React**: Frontend library for building interactive user interfaces
- **TypeScript**: Adds static typing to JavaScript for better code quality
- **Tailwind CSS**: Utility-first CSS framework for rapid UI development
- **shadcn/ui**: High-quality React components built on Radix UI
- **Vite**: Fast build tool and development server

## Product Feature

The application includes a Product model with basic ecommerce functionality.

### Migration

The products table migration (`database/migrations/2025_12_27_084123_create_products_table.php`):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

### Model

The Product model (`app/Models/Product.php`):

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'price',
        'stock_quantity',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }
}
```

### Factory

The Product factory (`database/factories/ProductFactory.php`):

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productTypes = ['Laptop', 'Smartphone', 'Headphones', 'Keyboard', 'Mouse', 'Monitor', 'Tablet', 'Camera', 'Speaker', 'Watch', 'Charger', 'Cable', 'Case', 'Stand', 'Adapter'];
        $adjectives = ['Premium', 'Pro', 'Ultra', 'Wireless', 'Bluetooth', 'USB-C', 'Fast', 'Portable', 'Compact', 'Ergonomic'];
        
        return [
            'name' => fake()->randomElement($adjectives) . ' ' . fake()->randomElement($productTypes) . ' ' . fake()->numberBetween(1, 5),
            'price' => fake()->randomFloat(2, 9.99, 999.99),
            'stock_quantity' => fake()->numberBetween(0, 500),
        ];
    }
}
```

### Seeder

The Product seeder (`database/seeders/ProductSeeder.php`):

```php
<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory(10)->create();
    }
}
```

### Running the Seeder

To run the Product seeder and populate the database with sample products:

1. **Run migrations** (if not already done):
   ```bash
   php artisan migrate
   ```

2. **Run the seeder**:
   ```bash
   php artisan db:seed --class=ProductSeeder
   ```

   Or run all seeders (including ProductSeeder):
   ```bash
   php artisan db:seed
   ```

This will create 10 sample products with random names, prices between $9.99 and $999.99, and stock quantities between 0 and 500.

## Daily Sales Report System

The application includes an automated daily sales report system that tracks sales and sends email summaries to administrators.

### Sales Tracking

Sales are tracked using the `sales` table, which records:
- `user_id`: The customer who made the purchase
- `product_id`: The product that was sold
- `quantity`: Number of units sold
- `price`: Price per unit at time of sale
- `total`: Total sale amount (quantity × price)
- `created_at`: Timestamp of the sale

### Migration

The sales table migration (`database/migrations/2025_12_27_090544_create_sales_table.php`):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
```

### Model

The Sale model (`app/Models/Sale.php`):

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'price',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
```

### Scheduled Job

The daily sales report is handled by the `SendDailySalesReport` command (`app/Console/Commands/SendDailySalesReport.php`):

```php
<?php

namespace App\Console\Commands;

use App\Mail\DailySalesReport;
use App\Models\Sale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailySalesReport extends Command
{
    protected $signature = 'sales:daily-report {--date= : The date to generate report for (Y-m-d format, defaults to yesterday)}';
    protected $description = 'Generate and send daily sales report to admin';

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

        // Get all sales for the specified date
        $sales = Sale::with(['product', 'user'])
            ->whereBetween('created_at', [$reportDate, $endDate])
            ->get();

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

        // Send email
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        
        Mail::to($adminEmail)->send(
            new DailySalesReport(
                $salesData,
                $reportDate->format('Y-m-d'),
                $totalRevenue,
                $totalItemsSold
            )
        );

        return Command::SUCCESS;
    }
}
```

### Mailable Class

The `DailySalesReport` Mailable (`app/Mail/DailySalesReport.php`):

```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySalesReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $salesData,
        public string $reportDate,
        public float $totalRevenue,
        public int $totalItemsSold
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily Sales Report - ' . $this->reportDate,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-sales-report',
        );
    }
}
```

### Scheduler Configuration

The scheduler is configured in `routes/console.php` (Laravel 11):

```php
<?php

use Illuminate\Support\Facades\Schedule;

// Schedule daily sales report to run every day at 9:00 AM
Schedule::command('sales:daily-report')
    ->dailyAt('09:00')
    ->timezone('UTC')
    ->description('Send daily sales report to admin');
```

### Manual Testing

To manually trigger the daily sales report:

1. **Run for yesterday (default)**:
   ```bash
   php artisan sales:daily-report
   ```

2. **Run for a specific date**:
   ```bash
   php artisan sales:daily-report --date=2025-12-26
   ```

3. **Run the scheduler manually** (to test all scheduled tasks):
   ```bash
   php artisan schedule:run
   ```

### Setting Up the Scheduler

To enable the scheduler in production, add this cron entry to your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

This will run the scheduler every minute, and Laravel will execute scheduled tasks at their designated times.

### Configuration

Make sure to set the admin email in your `.env` file:

```env
ADMIN_EMAIL=admin@example.com
```

The report will be sent to this email address daily at 9:00 AM UTC.

## Next Steps for Ecommerce Features

This project provides a solid foundation. To extend it into a full ecommerce application, consider adding:

- Product categories
- Shopping cart functionality
- Order management
- Payment integration (Stripe, PayPal)
- Inventory management
- User reviews and ratings
- Admin dashboard

## Contributing

This is a demonstration project for a job application. For contributions or modifications:

1. Follow Laravel and React best practices
2. Maintain code quality with ESLint and Prettier
3. Write tests for new features
4. Update documentation as needed

## License

This project is licensed under the MIT License.
