<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Report</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #007bff; padding: 20px; border-radius: 5px; margin-bottom: 20px; color: #fff;">
        <h1 style="margin-top: 0; color: #fff;">📊 Daily Sales Report</h1>
        <p style="margin: 0; font-size: 1.1em;">{{ $reportDate }}</p>
    </div>

    <div style="background-color: #fff; padding: 20px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="margin-top: 0; color: #495057;">Summary</h2>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
            <div style="background-color: #e7f3ff; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;">
                <p style="margin: 0; font-size: 0.9em; color: #6c757d;">Total Revenue</p>
                <p style="margin: 5px 0 0 0; font-size: 1.5em; font-weight: bold; color: #007bff;">${{ number_format($totalRevenue, 2) }}</p>
            </div>
            <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
                <p style="margin: 0; font-size: 0.9em; color: #6c757d;">Total Items Sold</p>
                <p style="margin: 5px 0 0 0; font-size: 1.5em; font-weight: bold; color: #856404;">{{ number_format($totalItemsSold) }}</p>
            </div>
        </div>
    </div>

    <div style="background-color: #fff; padding: 20px; border: 1px solid #dee2e6; border-radius: 5px;">
        <h2 style="margin-top: 0; color: #495057;">Product Sales Breakdown</h2>
        
        @if(count($salesData) > 0)
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Product</th>
                        <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Quantity Sold</th>
                        <th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesData as $sale)
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px;">{{ $sale['product_name'] }}</td>
                            <td style="padding: 12px; text-align: right;">{{ number_format($sale['quantity_sold']) }}</td>
                            <td style="padding: 12px; text-align: right; font-weight: bold;">${{ number_format($sale['revenue'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td style="padding: 12px; border-top: 2px solid #dee2e6;">Total</td>
                        <td style="padding: 12px; text-align: right; border-top: 2px solid #dee2e6;">{{ number_format($totalItemsSold) }}</td>
                        <td style="padding: 12px; text-align: right; border-top: 2px solid #dee2e6;">${{ number_format($totalRevenue, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @else
            <p style="color: #6c757d; font-style: italic;">No sales recorded for this date.</p>
        @endif
    </div>
    
    <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px; font-size: 12px; color: #6c757d;">
        <p style="margin: 0;">This is an automated daily sales report from your ecommerce system.</p>
        <p style="margin: 5px 0 0 0;">Report generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>

