<?php
class OrderModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getTableByNumber(string $tableNumber): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tables WHERE table_number = ?");
        $stmt->execute([$tableNumber]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAllTables(): array
    {
        $stmt = $this->db->query("SELECT * FROM tables ORDER BY table_number ASC");
        return $stmt->fetchAll();
    }

    public function createOrder(array $orderData, array $items): int|false
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO orders 
                (order_number, table_id, table_number, customer_name, total_amount, payment_method, payment_status, notes) 
                VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $orderData['order_number'],
                $orderData['table_id'],
                $orderData['table_number'],
                $orderData['customer_name'] ?? 'Guest',
                $orderData['total_amount'],
                $orderData['payment_method'] ?? 'Cash',
                $orderData['payment_status'] ?? 'Pending',
                $orderData['notes'] ?? ''
            ]);
            $orderId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare("INSERT INTO order_items 
                (order_id, menu_item_id, item_name, item_price, quantity, notes, subtotal) 
                VALUES (?,?,?,?,?,?,?)");

            foreach ($items as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['menu_item_id'],
                    $item['item_name'],
                    $item['item_price'],
                    $item['quantity'],
                    $item['notes'] ?? '',
                    $item['subtotal']
                ]);
            }

            // Mark table as occupied
            $this->db->prepare("UPDATE tables SET status='occupied' WHERE id=?")
                ->execute([$orderData['table_id']]);

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getOrderById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if (!$order)
            return null;
        $order['items'] = $this->getOrderItems($id);
        return $order;
    }

    public function getOrderByNumber(string $orderNumber): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch();
        if (!$order)
            return null;
        $order['items'] = $this->getOrderItems($order['id']);
        return $order;
    }

    public function getOrderItems(int $orderId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function getAllOrders(string $status = ''): array
    {
        $sql = "SELECT o.*, t.capacity FROM orders o 
                JOIN tables t ON o.table_id = t.id";
        $params = [];
        if ($status) {
            $sql .= " WHERE o.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();
        foreach ($orders as &$order) {
            $order['items'] = $this->getOrderItems($order['id']);
        }
        return $orders;
    }

    public function updateOrderStatus(int $id, string $status): bool
    {
        // If status is being updated to Completed, automatically mark as Paid
        if ($status === 'Completed') {
            $stmt = $this->db->prepare("UPDATE orders SET status=?, payment_status='Paid' WHERE id=?");
            $result = $stmt->execute([$status, $id]);
        } elseif ($status === 'Cancelled') {
            $stmt = $this->db->prepare("UPDATE orders SET status=?, payment_status='Cancelled' WHERE id=?");
            $result = $stmt->execute([$status, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE orders SET status=? WHERE id=?");
            $result = $stmt->execute([$status, $id]);
        }

        // If completed/cancelled, free the table
        if ($result && in_array($status, ['Completed', 'Cancelled'])) {
            $order = $this->getOrderById($id);
            if ($order) {
                $this->db->prepare("UPDATE tables SET status='available' WHERE id=?")
                    ->execute([$order['table_id']]);
            }
        }
        return $result;
    }

    public function updatePaymentStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE orders SET payment_status=? WHERE id=?");
        return $stmt->execute([$status, $id]);
    }

    public function getDailySummary(): array
    {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT 
            COUNT(*) AS total_orders,
            IFNULL(SUM(total_amount), 0) AS total_revenue,
            IFNULL(SUM(CASE WHEN payment_method='Cash' AND payment_status='Paid' THEN total_amount ELSE 0 END), 0) AS sum_cash,
            IFNULL(SUM(CASE WHEN payment_method='UPI' AND payment_status='Paid' THEN total_amount ELSE 0 END), 0) AS sum_upi,
            COUNT(CASE WHEN payment_method='Cash' AND payment_status='Paid' THEN 1 END) AS count_cash,
            COUNT(CASE WHEN payment_method='UPI' AND payment_status='Paid' THEN 1 END) AS count_upi,
            COUNT(CASE WHEN status='Completed' THEN 1 END) AS completed_orders,
            COUNT(CASE WHEN status='Pending' THEN 1 END) AS pending_orders,
            COUNT(CASE WHEN status='Preparing' THEN 1 END) AS preparing_orders,
            COUNT(CASE WHEN status='Cancelled' THEN 1 END) AS cancelled_orders
            FROM orders WHERE DATE(created_at) = ?");
        $stmt->execute([$today]);
        return $stmt->fetch();
    }

    public function getWeeklySales(): array
    {
        $stmt = $this->db->query("SELECT DATE(created_at) AS date, 
            COUNT(*) AS orders, SUM(total_amount) AS revenue 
            FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
            AND status != 'Cancelled'
            GROUP BY DATE(created_at) ORDER BY date ASC");
        return $stmt->fetchAll();
    }

    public function getTopItems(): array
    {
        $stmt = $this->db->query("SELECT oi.item_name, SUM(oi.quantity) AS total_qty, 
            SUM(oi.subtotal) AS total_revenue 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            WHERE o.status != 'Cancelled' 
            GROUP BY oi.item_name ORDER BY total_qty DESC LIMIT 5");
        return $stmt->fetchAll();
    }

    public function createTable(array $data): bool
    {
        $stmt = $this->db->prepare("INSERT INTO tables (table_number, capacity) VALUES (?,?)");
        return $stmt->execute([$data['table_number'], $data['capacity'] ?? 4]);
    }

    public function deleteTable(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM tables WHERE id=?");
        return $stmt->execute([$id]);
    }
}
