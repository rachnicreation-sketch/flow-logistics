<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Stock;
use App\Services\ReportService;

final class ReportController extends Controller
{
    public function index(): void
    {
        $stockRows    = (new Stock())->summary();
        $orderRows    = (new Order())->listOrders();
        $deliveryRows = (new Delivery())->listDeliveries();

        $db  = \App\Core\Database::connection();
        $cid = ((\App\Core\Auth::user()['role_slug'] ?? '') !== 'super_admin')
             ? \App\Core\Auth::companyId()
             : null;
        $w = $cid ? ' WHERE company_id = :cid' : '';

        $scalar = function (string $sql) use ($db, $cid, $w): mixed {
            $stmt = $db->prepare($sql);
            if ($cid) $stmt->bindValue(':cid', $cid, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        };

        $this->view('reports/index', [
            'stats' => [
                'stock_rows'      => count($stockRows),
                'order_rows'      => count($orderRows),
                'delivery_rows'   => count($deliveryRows),
                'return_rows'     => (int) $scalar('SELECT COUNT(*) FROM return_authorizations' . $w),
                'total_revenue'   => (float) $scalar('SELECT COALESCE(SUM(total_incl_tax),0) FROM invoices WHERE status="paid"' . ($w ? str_replace('WHERE','WHERE',  $w) : '')),
                'unpaid_invoices' => (float) $scalar('SELECT COALESCE(SUM(total_incl_tax),0) FROM invoices WHERE status IN ("unpaid","partially_paid")' . ($cid ? ' AND company_id = :cid' : '')),
                'total_expenses'  => (float) $scalar('SELECT COALESCE(SUM(amount),0) FROM expenses' . $w),
            ],
        ]);
    }

    public function stock(): void
    {
        $rows = (new Stock())->summary();

        $tableRows = [];
        foreach ($rows as $row) {
            $tableRows[] = [
                'SKU' => (string) ($row['sku'] ?? '-'),
                'Produit' => (string) ($row['product_name'] ?? '-'),
                'Entrepôt' => (string) ($row['warehouse_name'] ?? '-'),
                'Emplacement' => (string) ($row['location_label'] ?? '-'),
                'Quantite' => number_format((float) ($row['quantity'] ?? 0), 2, '.', ' '),
            ];
        }

        (new ReportService())->outputStructuredPdf(
            'Rapport Stock',
            'Etat du stock global par entrepot et emplacement',
            [
                ['label' => 'SKU', 'weight' => 1.1],
                ['label' => 'Produit', 'weight' => 2.0],
                ['label' => 'Entrepôt', 'weight' => 1.4],
                ['label' => 'Emplacement', 'weight' => 1.2],
                ['label' => 'Quantite', 'weight' => 1.0],
            ],
            $tableRows,
            [
                'Lignes' => count($tableRows),
                'Total quantite' => number_format(array_sum(array_map(static fn (array $r): float => (float) ($r['quantity'] ?? 0), $rows)), 2, '.', ' '),
            ],
            'rapport-stock.pdf'
        );
    }

    public function orders(): void
    {
        $rows = (new Order())->listOrders();
        $tableRows = [];

        foreach ($rows as $row) {
            $tableRows[] = [
                'Reference' => (string) ($row['reference'] ?? '-'),
                'Client' => (string) ($row['customer_name'] ?? '-'),
                'Statut' => (string) ($row['status'] ?? '-'),
                'Montant' => number_format((float) ($row['total_amount'] ?? 0), 2, '.', ' '),
                'Date' => isset($row['created_at']) ? date('d/m/Y', strtotime((string) $row['created_at'])) : '-',
            ];
        }

        (new ReportService())->outputStructuredPdf(
            'Rapport Commandes',
            'Suivi des commandes clients',
            [
                ['label' => 'Reference', 'weight' => 1.4],
                ['label' => 'Client', 'weight' => 2.2],
                ['label' => 'Statut', 'weight' => 1.1],
                ['label' => 'Montant', 'weight' => 1.1],
                ['label' => 'Date', 'weight' => 1.0],
            ],
            $tableRows,
            [
                'Lignes' => count($tableRows),
                'Total montant' => number_format(array_sum(array_map(static fn (array $r): float => (float) ($r['total_amount'] ?? 0), $rows)), 2, '.', ' '),
            ],
            'rapport-commandes.pdf'
        );
    }

    public function deliveries(): void
    {
        $rows = (new Delivery())->listDeliveries();
        $tableRows = [];

        foreach ($rows as $row) {
            $tableRows[] = [
                'Commande' => (string) ($row['order_ref'] ?? '-'),
                'Client' => (string) ($row['customer_name'] ?? '-'),
                'Chauffeur' => (string) ($row['driver_name'] ?? '-'),
                'Véhicule' => (string) ($row['plate_number'] ?? '-'),
                'Statut' => (string) ($row['status'] ?? '-'),
            ];
        }

        (new ReportService())->outputStructuredPdf(
            'Rapport Livraisons',
            'Suivi transport et execution des livraisons',
            [
                ['label' => 'Commande', 'weight' => 1.4],
                ['label' => 'Client', 'weight' => 2.0],
                ['label' => 'Chauffeur', 'weight' => 1.4],
                ['label' => 'Véhicule', 'weight' => 1.2],
                ['label' => 'Statut', 'weight' => 1.0],
            ],
            $tableRows,
            [
                'Lignes' => count($tableRows),
                'En cours' => count(array_filter($rows, static fn (array $r): bool => in_array((string) ($r['status'] ?? ''), ['pending', 'in_transit'], true))),
            ],
            'rapport-livraisons.pdf'
        );
    }

    public function exportStockCsv(): void
    {
        $rows = (new Stock())->summary();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=export-stock-' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['SKU', 'Produit', 'Entrepôt', 'Emplacement', 'Quantité']);
        
        foreach ($rows as $row) {
            fputcsv($output, [
                $row['sku'] ?? '',
                $row['product_name'] ?? '',
                $row['warehouse_name'] ?? '',
                $row['location_label'] ?? '',
                $row['quantity'] ?? 0
            ]);
        }
        fclose($output);
        exit;
    }
}
