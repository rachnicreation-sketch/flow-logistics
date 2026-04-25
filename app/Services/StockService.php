<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\StockMovement;
use PDO;
use RuntimeException;

final class StockService
{
    private PDO $db;
    private Stock $stocks;
    private StockMovement $movements;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->stocks = new Stock();
        $this->movements = new StockMovement();
    }

    public function move(array $data): void
    {
        $qty = (float) $data['quantity'];
        if ($qty <= 0) {
            throw new RuntimeException('La quantité doit être positive.');
        }

        $type = strtoupper($data['type']);
        $method = strtoupper($data['method'] ?? 'FIFO');
        $companyId = (int) Auth::companyId();
        $productId = (int) $data['product_id'];
        $warehouseId = (int) $data['warehouse_id'];
        $locationId = isset($data['location_id']) && $data['location_id'] !== '' ? (int) $data['location_id'] : null;

        $this->db->beginTransaction();
        try {
            if ($type === 'IN' || $type === 'ADJUST') {
                $this->stocks->upsert($productId, $warehouseId, $locationId, $qty);
                $layerStmt = $this->db->prepare(
                    'INSERT INTO stock_layers (company_id, product_id, warehouse_id, location_id, source_type, source_id, quantity_in, quantity_remaining, unit_cost, created_at)
                     VALUES (:company_id, :product_id, :warehouse_id, :location_id, :source_type, :source_id, :quantity_in, :quantity_remaining, :unit_cost, :created_at)'
                );
                $layerStmt->execute([
                    'company_id' => $companyId,
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'location_id' => $locationId,
                    'source_type' => $data['reference_type'] ?? 'manual',
                    'source_id' => $data['reference_id'] ?? null,
                    'quantity_in' => $qty,
                    'quantity_remaining' => $qty,
                    'unit_cost' => $data['unit_cost'] ?? 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } elseif ($type === 'OUT') {
                $this->consumeByMethod($companyId, $productId, $warehouseId, $locationId, $qty, $method);
                $this->stocks->upsert($productId, $warehouseId, $locationId, -$qty);
            } else {
                throw new RuntimeException('Type de mouvement invalide.');
            }

            $this->movements->add([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'location_id' => $locationId,
                'type' => $type,
                'method' => $method,
                'quantity' => $qty,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function receivePurchase(int $purchaseId, int $warehouseId, ?int $locationId = null): void
    {
        $purchaseModel = new Purchase();
        $items = $purchaseModel->items($purchaseId);
        foreach ($items as $item) {
            $this->move([
                'product_id' => (int) $item['product_id'],
                'warehouse_id' => $warehouseId,
                'location_id' => $locationId,
                'type' => 'IN',
                'method' => 'FIFO',
                'quantity' => (float) $item['quantity'],
                'unit_cost' => (float) $item['unit_price'],
                'reference_type' => 'purchase',
                'reference_id' => $purchaseId,
                'notes' => 'Réception achat ' . $purchaseId,
            ]);
        }
        $purchaseModel->markReceived($purchaseId);
    }

    public function fulfillOrder(
        int $orderId,
        int $warehouseId,
        ?int $locationId = null,
        string $method = 'FIFO'
    ): void {
        $orderModel = new Order();
        $items = $orderModel->items($orderId);
        foreach ($items as $item) {
            $this->move([
                'product_id' => (int) $item['product_id'],
                'warehouse_id' => $warehouseId,
                'location_id' => $locationId,
                'type' => 'OUT',
                'method' => $method,
                'quantity' => (float) $item['quantity'],
                'reference_type' => 'order',
                'reference_id' => $orderId,
                'notes' => 'Sortie commande ' . $orderId,
            ]);
        }
        $orderModel->updateStatus($orderId, 'prepared');
    }

    private function consumeByMethod(
        int $companyId,
        int $productId,
        int $warehouseId,
        ?int $locationId,
        float $quantity,
        string $method
    ): void {
        $remaining = $quantity;
        $orderBy = $method === 'LIFO' ? 'created_at DESC' : 'created_at ASC';
        $sql = 'SELECT id, quantity_remaining
                FROM stock_layers
                WHERE company_id = :company_id
                  AND product_id = :product_id
                  AND warehouse_id = :warehouse_id
                  AND quantity_remaining > 0';

        if ($locationId !== null) {
            $sql .= ' AND location_id = :location_id';
        }

        $sql .= " ORDER BY {$orderBy}, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':warehouse_id', $warehouseId, PDO::PARAM_INT);
        if ($locationId !== null) {
            $stmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $layers = $stmt->fetchAll();

        foreach ($layers as $layer) {
            if ($remaining <= 0) {
                break;
            }
            $available = (float) $layer['quantity_remaining'];
            if ($available <= 0) {
                continue;
            }
            $deduct = min($available, $remaining);
            $remaining -= $deduct;
            $update = $this->db->prepare(
                'UPDATE stock_layers
                 SET quantity_remaining = quantity_remaining - :deduct, updated_at = :updated_at
                 WHERE id = :id'
            );
            $update->execute([
                'deduct' => $deduct,
                'updated_at' => date('Y-m-d H:i:s'),
                'id' => $layer['id'],
            ]);
        }

        if ($remaining > 0) {
            throw new RuntimeException('Stock insuffisant pour la méthode ' . $method . '.');
        }
    }
}

