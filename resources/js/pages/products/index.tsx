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
import { Head, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { CheckCircle2, AlertCircle } from 'lucide-react';

interface Product {
    id: number;
    name: string;
    price: string;
    stock_quantity: number;
}

interface ProductsIndexProps {
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
    const { flash } = usePage<ProductsIndexProps>().props;
    const [processingProductId, setProcessingProductId] = useState<number | null>(null);
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

    const handleAddToCart = (productId: number) => {
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
        <AppLayout breadcrumbs={breadcrumbs}>
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
        </AppLayout>
    );
}

