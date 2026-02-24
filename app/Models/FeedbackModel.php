<?php

class FeedbackModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createFeedback($data)
    {
        $sql = "INSERT INTO feedback (order_id, customer_name, rating, comment) VALUES (:order_id, :customer_name, :rating, :comment)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'order_id' => $data['order_id'] ?? null,
            'customer_name' => $data['customer_name'] ?? 'Anonymous',
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? ''
        ]);
    }

    public function getAllFeedback()
    {
        $sql = "SELECT f.*, o.order_number FROM feedback f 
                LEFT JOIN orders o ON f.order_id = o.id 
                ORDER BY f.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getAverageRating()
    {
        $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_count FROM feedback";
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
}
