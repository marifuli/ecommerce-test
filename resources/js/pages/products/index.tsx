import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Product {
    id: number;
    name: string;
    price: string;
    stock_quantity: number;
}

interface ProductsIndexProps {
    products: Product[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '/products',
    },
];

export default function ProductsIndex({ products }: ProductsIndexProps) {
    const handleAddToCart = (productId: number) => {
        // Placeholder for cart functionality
        console.log('Add to cart:', productId);
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
                                        disabled={product.stock_quantity === 0}
                                        className="w-full"
                                        variant={
                                            product.stock_quantity === 0
                                                ? 'secondary'
                                                : 'default'
                                        }
                                    >
                                        {product.stock_quantity === 0
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

