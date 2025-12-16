<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $payment->reference }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #fff; color: #000; }
        .receipt { max-width: 300px; margin: 0 auto; padding: 10px; border: 1px dashed #ccc; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 12px; }
        .details { margin-bottom: 15px; font-size: 12px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .label { font-weight: bold; }
        .amount { font-size: 16px; font-weight: bold; text-align: right; border-top: 1px solid #000; padding-top: 5px; margin-top: 5px; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; border-top: 1px solid #000; padding-top: 10px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 0; }
            .receipt { border: none; max-width: 100%; width: 58mm; } /* Thermal printer width usually ~58mm or 80mm */
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>Elite Waste</h1>
            <p>Accra, Ghana</p>
            <p>+233 24 123 4567</p>
        </div>
        
        <div class="details">
            <div class="row">
                <span class="label">Date:</span>
                <span>{{ $payment->paid_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="row">
                <span class="label">Receipt No:</span>
                <span>#{{ $payment->id }}</span>
            </div>
            <div class="row">
                <span class="label">Customer:</span>
                <span>{{ Str::limit($payment->customer->name, 18) }}</span>
            </div>
            <div class="row">
                <span class="label">Payment Ref:</span>
                <span>{{ Str::limit($payment->reference, 10) }}</span>
            </div>
        </div>

        <div class="row amount">
            <span class="label">TOTAL PAID:</span>
            <span>GHS {{ number_format($payment->amount, 2) }}</span>
        </div>
        
        <div class="rows" style="margin-top: 5px; font-size: 12px;">
             <span class="label">Method:</span> {{ $payment->channel }}
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p><strong>www.elitewaste.com</strong></p>
        </div>

        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Print Receipt</button>
        </div>
    </div>
</body>
</html>
