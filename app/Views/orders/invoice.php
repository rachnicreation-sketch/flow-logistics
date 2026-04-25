<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture <?= e($order['invoice_number'] ?? $order['reference']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #0f766e;
            --brand-light: #e6f4f3;
            --ink: #1a202c;
            --muted: #64748b;
            --line: #e2e8f0;
        }
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--ink);
            background: #f8fafc;
            font-size: 13px;
            line-height: 1.5;
        }
        .invoice-box {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--line);
        }
        .company-info h1 {
            font-size: 24px;
            color: var(--brand);
            margin: 0 0 8px 0;
            letter-spacing: -0.5px;
        }
        .company-info p { margin: 2px 0; color: var(--muted); }
        .invoice-details { text-align: right; }
        .invoice-details h2 {
            font-size: 32px;
            color: var(--line);
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .invoice-details table { width: 100%; border: none; }
        .invoice-details th { text-align: right; color: var(--muted); padding-right: 12px; font-weight: 500; }
        .invoice-details td { text-align: right; font-weight: 600; }
        
        .addresses {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 6px;
        }
        .address-block h3 {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--muted);
            margin: 0 0 8px 0;
            letter-spacing: 0.5px;
        }
        .address-block p { margin: 4px 0; font-size: 14px; font-weight: 500; }
        
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.items th, table.items td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--line);
        }
        table.items th {
            background: var(--brand-light);
            color: var(--brand);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        table.items td { font-size: 13px; }
        table.items .right { text-align: right; }
        table.items .center { text-align: center; }
        
        .totals {
            width: 300px;
            margin-left: auto;
        }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals th, .totals td { padding: 8px 12px; text-align: right; }
        .totals th { color: var(--muted); font-weight: 500; }
        .totals .grand-total th, .totals .grand-total td {
            font-size: 18px;
            font-weight: 700;
            color: var(--brand);
            border-top: 2px solid var(--line);
            padding-top: 12px;
        }
        
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid var(--line);
            text-align: center;
            color: var(--muted);
            font-size: 11px;
        }
        
        .print-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 12px;
            background: var(--brand);
            color: white;
            text-align: center;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        .print-btn:hover { background: #0b5e57; }
        
        @media print {
            body { background: #fff; }
            .invoice-box { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">🖨️ Imprimer la Facture</button>

<div class="invoice-box">
    <div class="header">
        <div class="company-info">
            <h1>⚡ Flow Logistics</h1>
            <p>123 Avenue de la Logistique</p>
            <p>69000 Lyon, France</p>
            <p>TVA: FR88 123456789</p>
        </div>
        <div class="invoice-details">
            <h2>FACTURE</h2>
            <table>
                <tr><th>N° Facture:</th><td><?= e($order['invoice_number'] ?? '-') ?></td></tr>
                <tr><th>Réf Commande:</th><td><?= e($order['reference']) ?></td></tr>
                <tr><th>Date:</th><td><?= e(date('d/m/Y', strtotime($order['created_at']))) ?></td></tr>
            </table>
        </div>
    </div>

    <div class="addresses">
        <div class="address-block">
            <h3>Facturé à</h3>
            <p><?= e($customer['name'] ?? '-') ?></p>
            <p><?= e($customer['address'] ?? '-') ?></p>
            <p><?= e($customer['email'] ?? '') ?></p>
        </div>
        <div class="address-block">
            <h3>Livré à</h3>
            <p><?= e($customer['name'] ?? '-') ?></p>
            <p><?= e($order['delivery_address'] ?? ($customer['address'] ?? '-')) ?></p>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Réf / SKU</th>
                <th>Description</th>
                <th class="center">Qté</th>
                <th class="right">Prix U. (HT)</th>
                <th class="right">Total (HT)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><code><?= e($item['sku']) ?></code></td>
                <td><?= e($item['product_name']) ?></td>
                <td class="center"><?= e((string)(float) $item['quantity']) ?></td>
                <td class="right"><?= number_format((float) $item['unit_price'], 2, ',', ' ') ?> €</td>
                <td class="right"><?= number_format((float) $item['total_price'], 2, ',', ' ') ?> €</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <th>Total HT:</th>
                <td><?= number_format((float) $order['total_amount'], 2, ',', ' ') ?> €</td>
            </tr>
            <tr>
                <th>TVA (0% - Exonéré):</th>
                <td>0,00 €</td>
            </tr>
            <tr class="grand-total">
                <th>Total TTC:</th>
                <td><?= number_format((float) $order['total_amount'], 2, ',', ' ') ?> €</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Merci pour votre confiance. Facture payable à réception.</p>
        <p>Flow Logistics SAS - Capital de 50 000€ - SIRET: 123 456 789 00012</p>
    </div>
</div>

</body>
</html>
