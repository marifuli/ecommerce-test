import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { CheckCircle2, AlertCircle } from 'lucide-react';
import { login, register } from '@/routes';

interface Product {
    id: number;
    name: string;
    price: string;
    stock_quantity: number;
}

interface ProductsIndexProps extends SharedData {
    products: Product[];
    flash?: {
        success?: string;
        error?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '/products',
    },
];

export default function ProductsIndex({ products }: ProductsIndexProps) {
    const { auth, flash } = usePage<ProductsIndexProps>().props;
    const [processingProductId, setProcessingProductId] = useState<number | null>(null);
    const [showMessage, setShowMessage] = useState(false);
    const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);
    const [showLoginDialog, setShowLoginDialog] = useState(false);

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

    const handleAddToCart = (productId: number) => {
        if (!auth.user) {
            setShowLoginDialog(true);
            return;
        }

        setProcessingProductId(productId);

        router.post(
            '/cart',
            { product_id: productId },
            {
                preserveScroll: true,
                onFinish: () => {
                    setProcessingProductId(null);
                },
                onError: (errors) => {
                    if (errors.product_id) {
                        setMessage({ type: 'error', text: errors.product_id });
                        setShowMessage(true);
                        setTimeout(() => setShowMessage(false), 3000);
                    }
                },
            }
        );
    };

    return (
        <AppHeaderLayout>
            <Head title="Products" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="mb-4">
                    <h1 className="text-2xl font-bold">Products</h1>
                    <p className="text-muted-foreground text-sm">
                        Browse our available products
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

                {products.length === 0 ? (
                    <div className="flex items-center justify-center py-12">
                        <p className="text-muted-foreground">No products available.</p>
                    </div>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {products.map((product) => (
                            <Card key={product.id}>
                                <CardHeader>
                                    <CardTitle>{product.name}</CardTitle>
                                    <CardDescription>
                                        Stock: {product.stock_quantity} available
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold">
                                        ${parseFloat(product.price).toFixed(2)}
                                    </div>
                                </CardContent>
                                <CardFooter>
                                    <Button
                                        onClick={() => handleAddToCart(product.id)}
                                        disabled={
                                            product.stock_quantity === 0 ||
                                            processingProductId === product.id
                                        }
                                        className="w-full"
                                        variant={
                                            product.stock_quantity === 0
                                                ? 'secondary'
                                                : 'default'
                                        }
                                    >
                                        {processingProductId === product.id
                                            ? 'Adding...'
                                            : product.stock_quantity === 0
                                                ? 'Out of Stock'
                                                : 'Add to Cart'}
                                    </Button>
                                </CardFooter>
                            </Card>
                        ))}
                    </div>
                )}
            </div>

            <Dialog open={showLoginDialog} onOpenChange={setShowLoginDialog}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Login Required</DialogTitle>
                        <DialogDescription>
                            To add products to your cart and make purchases, please login or create an account.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="flex flex-col space-y-4">
                        <p className="text-sm text-muted-foreground">
                            You need to be logged in to add items to your cart.
                        </p>
                        <div className="flex space-x-2">
                            <Button asChild>
                                <Link href={login()}>Login</Link>
                            </Button>
                            <Button variant="outline" asChild>
                                <Link href={register()}>Create Account</Link>
                            </Button>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>
        </AppHeaderLayout>
    );
}

