<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alert</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #dc3545; margin-top: 0;">⚠️ Low Stock Alert</h1>
    </div>

    <div style="background-color: #fff; padding: 20px; border: 1px solid #dee2e6; border-radius: 5px;">
        <p>Hello Admin,</p>
        
        <p>A product in your inventory has fallen below the stock threshold.</p>
        
        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <h2 style="margin-top: 0; color: #856404;">{{ $product->name }}</h2>
            <p style="margin: 5px 0;"><strong>Current Stock:</strong> <span style="color: #dc3545; font-size: 1.2em; font-weight: bold;">{{ $product->stock_quantity }}</span> units</p>
            <p style="margin: 5px 0;"><strong>Stock Threshold:</strong> {{ $threshold }} units</p>
            <p style="margin: 5px 0;"><strong>Product Price:</strong> ${{ number_format($product->price, 2) }}</p>
        </div>
        
        <p>Please consider restocking this product to avoid running out of inventory.</p>
        
        <p style="margin-top: 30px;">
            <a href="{{ url('/products') }}" style="background-color: #007bff; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">View Products</a>
        </p>
    </div>
    
    <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px; font-size: 12px; color: #6c757d;">
        <p style="margin: 0;">This is an automated notification from your ecommerce system.</p>
    </div>
</body>
</html>

