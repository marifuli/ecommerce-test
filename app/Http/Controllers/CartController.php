<?php

namespace App\Http\Controllers;

use App\Jobs\CheckLowStock;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Display the user's shopping cart.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $cart = Cart::with(['cartItems.product'])
            ->where('user_id', $user->id)
            ->first();

        // If cart doesn't exist, create an empty one
        if (!$cart) {
            $cart = Cart::create(['user_id' => $user->id]);
            $cart->load('cartItems.product');
        }

        // Calculate totals
        $cartItems = $cart->cartItems->map(function ($item) {
            $subtotal = (float) $item->product->price * $item->quantity;
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_price' => $item->product->price,
                'quantity' => $item->quantity,
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'stock_quantity' => $item->product->stock_quantity,
            ];
        });

        $total = $cartItems->sum(fn($item) => (float) $item['subtotal']);

        return Inertia::render('cart/index', [
            'cart' => [
                'id' => $cart->id,
                'items' => $cartItems,
                'total' => number_format($total, 2, '.', ''),
            ],
        ]);
    }
    /**
     * Add a product to the cart.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $product = Product::findOrFail($validated['product_id']);
        $quantity = $validated['quantity'] ?? 1;

        // Check if product has stock
        if ($product->stock_quantity === 0) {
            throw ValidationException::withMessages([
                'product_id' => 'This product is out of stock.',
            ]);
        }

        // Check if requested quantity exceeds stock
        if ($quantity > $product->stock_quantity) {
            throw ValidationException::withMessages([
                'product_id' => 'Cannot add ' . $quantity . ' items. Only ' . $product->stock_quantity . ' available in stock.',
            ]);
        }

        // Get or create cart for the user
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id]
        );

        // Check if product already exists in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            // Product already in cart, add to existing quantity
            $newQuantity = $cartItem->quantity + $quantity;

            // Check if new quantity exceeds stock
            if ($newQuantity > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'product_id' => 'Cannot add more items. Only ' . $product->stock_quantity . ' available in stock.',
                ]);
            }

            $cartItem->update(['quantity' => $newQuantity]);
            $message = 'Product quantity updated in cart.';
        } else {
            // Product not in cart, create new cart item
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
            $message = 'Product added to cart successfully.';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update the quantity of a cart item.
     */
    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $user = $request->user();

        // Ensure the cart item belongs to the user's cart
        $cart = $cartItem->cart;
        
        if ($cart->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = $cartItem->product;

        // Check if quantity exceeds stock
        if ($validated['quantity'] > $product->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Cannot set quantity to ' . $validated['quantity'] . '. Only ' . $product->stock_quantity . ' available in stock.',
            ]);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return redirect()->back()->with('success', 'Cart item quantity updated.');
    }

    /**
     * Remove a cart item from the cart.
     */
    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        $user = $request->user();

        // Ensure the cart item belongs to the user's cart
        $cart = $cartItem->cart;
        
        if ($cart->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $cartItem->delete();

        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    /**
     * Process checkout and complete the order.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        $cart = Cart::with(['cartItems.product'])
            ->where('user_id', $user->id)
            ->first();

        // Validate cart exists and has items
        if (!$cart || $cart->cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty. Please add items before checkout.',
            ]);
        }

        // Validate stock availability for all items
        $stockErrors = [];
        foreach ($cart->cartItems as $item) {
            if ($item->quantity > $item->product->stock_quantity) {
                $stockErrors[] = "Insufficient stock for {$item->product->name}. Only {$item->product->stock_quantity} available, but {$item->quantity} requested.";
            }
        }

        if (!empty($stockErrors)) {
            $errorMessage = implode(' ', $stockErrors);
            throw ValidationException::withMessages([
                'stock' => $errorMessage,
            ]);
        }

        // Process checkout in a transaction
        $lowStockThreshold = 5;
        $lowStockProducts = [];

        DB::transaction(function () use ($cart, $user, $lowStockThreshold, &$lowStockProducts) {
            foreach ($cart->cartItems as $item) {
                $product = $item->product;
                
                // Reduce stock
                $product->decrement('stock_quantity', $item->quantity);
                
                // Refresh product to get updated stock quantity
                $product->refresh();
                
                // Check if product is now low in stock
                if ($product->stock_quantity <= $lowStockThreshold) {
                    $lowStockProducts[] = $product;
                }
                
                // Record sale
                Sale::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $product->price,
                    'total' => (float) $product->price * $item->quantity,
                ]);
            }
            
            // Clear cart items
            $cart->cartItems()->delete();
        });

        // Dispatch low stock notification jobs for products that are low in stock
        foreach ($lowStockProducts as $product) {
            CheckLowStock::dispatch($product, $lowStockThreshold);
        }

        return redirect()->route('cart.index')->with('success', 'Checkout completed successfully! Thank you for your purchase.');
    }
}
