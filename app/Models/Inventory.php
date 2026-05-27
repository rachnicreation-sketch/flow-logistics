<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Model;
use PDO;

final class Inventory extends Model
{
    protected string $table = 'inventories';

    public function createInventory(array $data): int
    {
        return $this->insert([
            'company_id' => $this->resolveCompanyId(),
            'warehouse_id' => $data['warehouse_id'],
            'title' => $data['title'],
            'status' => 'open',
            'started_at' => date('Y-m-d H:i:s'),
            'created_by' => Auth::id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function addItemsFromWarehouse(int $inventoryId, int $warehouseId): void
    {
        $sql = "INSERT INTO inventory_items (inventory_id, product_id, location_id, theoretical_quantity)
                SELECT :inventory_id, product_id, location_id, quantity
                FROM stocks
                WHERE warehouse_id = :warehouse_id AND company_id = :company_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'inventory_id' => $inventoryId,
            'warehouse_id' => $warehouseId,
            'company_id' => $this->resolveCompanyId(),
        ]);
    }

    public function listWithDetails(): array
    {
        $sql = "SELECT i.*, w.name AS warehouse_name, u.name AS creator_name
                FROM inventories i
                INNER JOIN warehouses w ON w.id = i.warehouse_id
                LEFT JOIN users u ON u.id = i.created_by";
        
        if (!$this->isSuperAdmin()) {
            $sql .= " WHERE i.company_id = :company_id";
        }
        
        $sql .= " ORDER BY i.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!$this->isSuperAdmin()) {
            $stmt->bindValue(':company_id', $this->currentCompanyId(), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getItems(int $inventoryId): array
    {
        $sql = "SELECT ii.*, p.name AS product_name, p.sku, wl.label AS location_label
                FROM inventory_items ii
                INNER JOIN products p ON p.id = ii.product_id
                LEFT JOIN warehouse_locations wl ON wl.id = ii.location_id
                WHERE ii.inventory_id = :inventory_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':inventory_id', $inventoryId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateItemQuantity(int $itemId, float $quantity): void
    {
        $sql = "UPDATE inventory_items 
                SET actual_quantity = :qty, 
                    difference = :qty - theoretical_quantity 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'qty' => $quantity,
            'id' => $itemId
        ]);
    }

    public function close(int $inventoryId): void
    {
        $this->updateById($inventoryId, [
            'status' => 'closed',
            'closed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Optionnel : Mettre à jour le stock réel après clôture
        $items = $this->getItems($inventoryId);
        $stockModel = new Stock();
        foreach ($items as $item) {
            if ($item['actual_quantity'] !== null) {
                // On ajuste le stock pour correspondre à l'inventaire physique
                // Le stock réel devient la quantité physique constatée
                $this->syncStockWithInventory($item);
            }
        }
    }

    private function syncStockWithInventory(array $item): void
    {
        $inventory = $this->find((int)$item['inventory_id']);
        if (!$inventory) return;

        // On réinitialise la quantité exacte dans la table stocks
        $sql = "UPDATE stocks SET quantity = :qty, updated_at = NOW()
                WHERE company_id = :company_id 
                  AND product_id = :product_id 
                  AND warehouse_id = :warehouse_id
                  AND (location_id = :location_id OR (location_id IS NULL AND :location_id IS NULL))";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'qty' => $item['actual_quantity'],
            'company_id' => $inventory['company_id'],
            'product_id' => $item['product_id'],
            'warehouse_id' => $inventory['warehouse_id'],
            'location_id' => $item['location_id'],
        ]);

        // Enregistrer le mouvement d'ajustement
        (new StockMovement())->add([
            'product_id' => $item['product_id'],
            'warehouse_id' => $inventory['warehouse_id'],
            'location_id' => $item['location_id'],
            'type' => 'ADJUST',
            'quantity' => (float)$item['difference'],
            'notes' => 'Inventaire #' . $inventory['id'] . ' : ' . $inventory['title'],
        ]);
    }
}
