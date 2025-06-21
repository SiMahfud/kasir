<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt Order #<?= esc($order['id'] ?? 'N/A') ?></title>
    <!-- Using Tailwind CDN for simplicity in a standalone receipt, or link to compiled style.css if preferred -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"> -->
    <style>
        /* Basic Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt; /* Common for thermal printers */
            color: #000;
            line-height: 1.4;
        }
        .receipt-container-screen { /* For screen preview */
            width: 320px; /* Approx 80mm, adjust as needed */
            margin: 20px auto;
            padding: 15px;
            border: 1px dashed #ccc;
            background-color: #fff;
        }
        .receipt-content {
            padding: 5mm; /* Standard padding for actual receipt */
        }

        /* Print-specific styles */
        @media print {
            body {
                font-family: 'Courier New', Courier, monospace; /* Ensure font is printer-friendly */
                font-size: 10pt;
                margin: 0;
                padding: 0;
                background-color: #fff; /* Ensure background is white for printing */
                -webkit-print-color-adjust: exact; /* Chrome, Safari */
                color-adjust: exact; /* Firefox */
            }
            .receipt-container-screen { /* Remove screen styling for print */
                width: 80mm; /* Target thermal printer width */
                margin: 0;
                padding: 0;
                border: none;
                box-shadow: none;
            }
            .receipt-content {
                 padding: 0; /* Adjust if printer has own margins */
            }
            .no-print {
                display: none !important;
            }
            /* Ensure text is black for printing, override Tailwind colors if used directly */
            * {
                color: #000 !important;
                background-color: #fff !important;
            }
             table, tr, td, th, tbody, thead, tfoot {
                border-color: #000 !important; /* Ensure table borders are black */
            }
        }

        /* Simple structure styling - can be replaced/enhanced by Tailwind if linked */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-semibold { font-weight: 600; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-4 { margin-top: 1rem; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
        .hr-dashed { border-top: 1px dashed #555; margin: 8px 0; }

        .item-list { margin-top: 8px; margin-bottom: 8px; }
        .item-row > div { display: inline-block; vertical-align: top; }
        .item-name { width: 55%; } /* Adjust widths as needed */
        .item-qty-price { width: 20%; text-align: right; }
        .item-total { width: 25%; text-align: right; }

        .totals-section div { display: flex; justify-content: space-between; margin-bottom: 2px;}

    </style>
</head>
<body>
    <div class="receipt-container-screen">
        <div class="receipt-content">
            <div class="text-center">
                <h1 class="text-lg font-bold"><?= esc($storeName ?? 'KasirKu Store') ?></h1>
                <p class="text-xs"><?= esc($storeAddress ?? '123 Main Street, Anytown') ?></p>
                <p class="text-xs"><?= esc($storeContact ?? 'Phone: (123) 456-7890') ?></p>
            </div>

            <div class="hr-dashed mt-2 mb-2"></div>

            <div class="text-xs">
                <div>Order ID: #<?= esc($order['id'] ?? 'N/A') ?></div>
                <div>Date: <?= esc(date('d/m/Y H:i:s', strtotime($order['order_date'] ?? time()))) ?></div>
                <div>Cashier: <?= esc($order['user_name'] ?? 'N/A') ?></div>
                <?php if (!empty($order['customer_name']) && $order['customer_name'] !== 'Walk-in Customer'): ?>
                    <div>Customer: <?= esc($order['customer_name']) ?></div>
                <?php endif; ?>
            </div>

            <div class="hr-dashed mt-2 mb-2"></div>

            <div class="item-list text-xs">
                <?php
                $calculated_subtotal = 0;
                if (!empty($items)): ?>
                    <?php foreach ($items as $item):
                        $item_total = ($item['price_per_unit'] ?? 0) * ($item['quantity'] ?? 0);
                        $calculated_subtotal += $item_total;
                    ?>
                    <div class="item-row mb-1">
                        <div class="item-name"><?= esc($item['product_name'] ?? 'Unknown Product') ?></div>
                        <div class="item-qty-price"><?= esc($item['quantity'] ?? 0) ?>x<?= number_format($item['price_per_unit'] ?? 0, 0, ',', '.') ?></div>
                        <div class="item-total"><?= number_format($item_total, 0, ',', '.') ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No items in this order.</p>
                <?php endif; ?>
            </div>

            <div class="hr-dashed mt-2 mb-2"></div>

            <div class="totals-section text-xs">
                <div>
                    <span>Subtotal:</span>
                    <span><?= number_format($order['subtotal_before_discount'] ?? $calculated_subtotal ?? 0, 0, ',', '.') ?></span>
                </div>

                <?php
                $discountValue = $order['discount_value'] ?? 0;
                $discountType = $order['discount_type'] ?? '';
                $calculatedDiscount = $order['calculated_discount_amount'] ?? 0;

                if ($calculatedDiscount > 0):
                ?>
                <div>
                    <span>Discount
                        <?php if ($discountType === 'percentage'): ?>
                            (<?= number_format($discountValue, 0, ',', '.') ?>%):
                        <?php elseif ($discountType === 'fixed_amount'): ?>
                            (Fixed):
                        <?php endif; ?>
                    </span>
                    <span>-<?= number_format($calculatedDiscount, 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>

                <?php $taxAmount = $order['tax_amount'] ?? 0; ?>
                <?php if ($taxAmount > 0): ?>
                <div>
                    <span>Tax (10%):</span>
                    <span><?= number_format($taxAmount, 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>

                <div class="font-bold mt-1" style="border-top: 1px dashed #555; padding-top: 4px;">
                    <span>Grand Total:</span>
                    <span><?= number_format($order['total_amount'] ?? 0, 0, ',', '.') ?></span>
                </div>
                <!-- Add Amount Paid and Change Due if available -->
                <!--
                <div class="mt-1">
                    <span>Amount Paid:</span>
                    <span><?= number_format($order['amount_paid'] ?? $order['total_amount'] ?? 0, 0, ',', '.') ?></span>
                </div>
                <div>
                    <span>Change Due:</span>
                    <span><?= number_format(($order['amount_paid'] ?? $order['total_amount'] ?? 0) - ($order['total_amount'] ?? 0), 0, ',', '.') ?></span>
                </div>
                -->
            </div>

            <div class="hr-dashed mt-2 mb-2"></div>

            <div class="text-center text-xs mt-2">
                <p>Thank you for your purchase!</p>
                <p class="mt-1">Items once sold are not returnable/refundable.</p>
            </div>

            <div class="text-center mt-4 no-print">
                <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                 <a href="<?= site_url('pesanan/view/' . $order['id']) ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded ml-2">
                    Back to Order View
                </a>
            </div>
        </div>
    </div>
</body>
</html>
