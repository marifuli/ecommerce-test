import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Plus, Minus, Trash2 } from 'lucide-react';
import { useState, useEffect } from 'react';
import { CheckCircle2, AlertCircle } from 'lucide-react';

interface CartItem {
    id: number;
    product_id: number;
    product_name: string;
    product_price: string;
    quantity: number;
    subtotal: string;
    stock_quantity: number;
}

interface Cart {
    id: number;
    items: CartItem[];
    total: string;
}

interface CartIndexProps {
    cart: Cart;
    flash?: {
        success?: string;
        error?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Shopping Cart',
        href: '/cart',
    },
];

export default function CartIndex({ cart }: CartIndexProps) {
    const page = usePage();
    const flash = (page.props as unknown as CartIndexProps).flash;
    const [processingItemId, setProcessingItemId] = useState<number | null>(null);
    const [showMessage, setShowMessage] = useState(false);
    const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

    useEffect(() => {
        if (flash?.success) {
            setMessage({ type: 'success', text: flash.success });
            setShowMessage(true);
            setTimeout(() => setShowMessage(false), 3000);
        } else if (flash?.error) {
            setMessage({ type: 'error', text: flash.error });
            setShowMessage(true);
            setTimeout(() => setShowMessage(false), 3000);
        }
    }, [flash]);

    const handleIncreaseQuantity = (item: CartItem) => {
        const newQuantity = item.quantity + 1;
        handleUpdateQuantity(item.id, newQuantity);
    };

    const handleDecreaseQuantity = (item: CartItem) => {
        const newQuantity = item.quantity - 1;
        handleUpdateQuantity(item.id, newQuantity);
    };

    const handleUpdateQuantity = (itemId: number, quantity: number) => {
        setProcessingItemId(itemId);
        
        router.put(
            `/cart/items/${itemId}`,
            { quantity },
            {
                preserveScroll: true,
                onFinish: () => {
                    setProcessingItemId(null);
                },
                onError: (errors) => {
                    if (errors.quantity) {
                        setMessage({ type: 'error', text: errors.quantity });
                        setShowMessage(true);
                        setTimeout(() => setShowMessage(false), 3000);
                    }
                },
            }
        );
    };

    const handleRemoveItem = (itemId: number) => {
        if (!confirm('Are you sure you want to remove this item from your cart?')) {
            return;
        }

        setProcessingItemId(itemId);
        
        router.delete(`/cart/items/${itemId}`, {
            preserveScroll: true,
            onFinish: () => {
                setProcessingItemId(null);
            },
            onError: (errors) => {
                setMessage({ type: 'error', text: 'Failed to remove item. Please try again.' });
                setShowMessage(true);
                setTimeout(() => setShowMessage(false), 3000);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Shopping Cart" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="mb-4">
                    <h1 className="text-2xl font-bold">Shopping Cart</h1>
                    <p className="text-muted-foreground text-sm">
                        Review your items before checkout
                    </p>
                </div>

                {showMessage && message && (
                    <Alert
                        variant={message.type === 'error' ? 'destructive' : 'default'}
                        className="mb-4"
                    >
                        {message.type === 'success' ? (
                            <CheckCircle2 className="h-4 w-4" />
                        ) : (
                            <AlertCircle className="h-4 w-4" />
                        )}
                        <AlertDescription>{message.text}</AlertDescription>
                    </Alert>
                )}

                {cart.items.length === 0 ? (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <p className="text-muted-foreground mb-4">
                                Your cart is empty.
                            </p>
                            <Button asChild>
                                <Link href="/products">Browse Products</Link>
                            </Button>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-3">
                        <div className="lg:col-span-2 space-y-4">
                            {cart.items.map((item) => (
                                <Card key={item.id}>
                                    <CardHeader>
                                        <CardTitle>{item.product_name}</CardTitle>
                                        <CardDescription>
                                            ${parseFloat(item.product_price).toFixed(2)} each
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <p className="text-sm text-muted-foreground">
                                                    Quantity: {item.quantity}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    Stock available: {item.stock_quantity}
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-lg font-semibold">
                                                    ${item.subtotal}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    Subtotal
                                                </p>
                                            </div>
                                        </div>
                                    </CardContent>
                                    <CardFooter className="flex gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => handleDecreaseQuantity(item)}
                                            disabled={
                                                item.quantity <= 1 ||
                                                processingItemId === item.id
                                            }
                                            className="flex-1"
                                        >
                                            <Minus className="h-4 w-4" />
                                            {processingItemId === item.id ? 'Updating...' : 'Decrease'}
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => handleIncreaseQuantity(item)}
                                            disabled={
                                                item.quantity >= item.stock_quantity ||
                                                processingItemId === item.id
                                            }
                                            className="flex-1"
                                        >
                                            <Plus className="h-4 w-4" />
                                            {processingItemId === item.id ? 'Updating...' : 'Increase'}
                                        </Button>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            onClick={() => handleRemoveItem(item.id)}
                                            disabled={processingItemId === item.id}
                                            className="flex-1"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                            {processingItemId === item.id ? 'Removing...' : 'Remove'}
                                        </Button>
                                    </CardFooter>
                                </Card>
                            ))}
                        </div>

                        <div className="lg:col-span-1">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Order Summary</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        <div className="flex justify-between text-sm">
                                            <span className="text-muted-foreground">
                                                Items ({cart.items.length})
                                            </span>
                                            <span>${cart.total}</span>
                                        </div>
                                        <div className="border-t pt-4">
                                            <div className="flex justify-between text-lg font-bold">
                                                <span>Total</span>
                                                <span>${cart.total}</span>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                                <CardFooter>
                                    <Button className="w-full" size="lg">
                                        Proceed to Checkout
                                    </Button>
                                </CardFooter>
                            </Card>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

