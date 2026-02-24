<?php

class WaiterModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createRequest($tableId, $tableNumber, $requestType)
    {
        $sql = "INSERT INTO waiter_requests (table_id, table_number, request_type) VALUES (:table_id, :table_number, :request_type)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'table_id' => $tableId,
            'table_number' => $tableNumber,
            'request_type' => $requestType
        ]);
    }

    public function getPendingRequests()
    {
        $sql = "SELECT * FROM waiter_requests WHERE status = 'pending' ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function completeRequest($id)
    {
        $sql = "UPDATE waiter_requests SET status = 'completed' WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
