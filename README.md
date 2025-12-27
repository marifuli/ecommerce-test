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
