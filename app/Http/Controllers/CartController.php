<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        ]);

        $user = $request->user();
        $product = Product::findOrFail($validated['product_id']);

        // Check if product has stock
        if ($product->stock_quantity === 0) {
            throw ValidationException::withMessages([
                'product_id' => 'This product is out of stock.',
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
            // Product already in cart, increment quantity
            $newQuantity = $cartItem->quantity + 1;

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
                'quantity' => 1,
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
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        
        if ($cartItem->cart_id !== $cart->id) {
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
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        
        if ($cartItem->cart_id !== $cart->id) {
            abort(403, 'Unauthorized action.');
        }

        $cartItem->delete();

        return redirect()->back()->with('success', 'Item removed from cart.');
    }
}
