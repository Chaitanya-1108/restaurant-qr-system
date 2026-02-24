<?php
class MenuModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllCategories(): array
    {
        $stmt = $this->db->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    }

    public function getAllItems(?int $categoryId = null, ?string $search = null): array
    {
        $sql = "SELECT m.*, c.name AS category_name FROM menu_items m 
                JOIN categories c ON m.category_id = c.id 
                WHERE m.is_available = 1";
        $params = [];

        if ($categoryId) {
            $sql .= " AND m.category_id = ?";
            $params[] = $categoryId;
        }
        if ($search) {
            $sql .= " AND (m.name LIKE ? OR m.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY m.sort_order ASC, m.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getFeaturedItems(): array
    {
        $stmt = $this->db->prepare("SELECT m.*, c.name AS category_name FROM menu_items m 
            JOIN categories c ON m.category_id = c.id 
            WHERE m.is_featured=1 AND m.is_available=1 ORDER BY m.name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getItemById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Admin CRUD
    public function getAllItemsAdmin(): array
    {
        $stmt = $this->db->query("SELECT m.*, c.name AS category_name FROM menu_items m 
            JOIN categories c ON m.category_id = c.id ORDER BY c.sort_order, m.sort_order, m.name");
        return $stmt->fetchAll();
    }

    public function createItem(array $data): bool
    {
        $stmt = $this->db->prepare("INSERT INTO menu_items 
            (category_id, name, description, price, image, is_veg, is_available, is_featured, sort_order) 
            VALUES (?,?,?,?,?,?,?,?,?)");
        return $stmt->execute([
            $data['category_id'], $data['name'], $data['description'],
            $data['price'], $data['image'] ?? null,
            $data['is_veg'] ?? 1, $data['is_available'] ?? 1,
            $data['is_featured'] ?? 0, $data['sort_order'] ?? 0
        ]);
    }

    public function updateItem(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE menu_items SET 
            category_id=?, name=?, description=?, price=?, is_veg=?, 
            is_available=?, is_featured=?, sort_order=?
            WHERE id=?");
        return $stmt->execute([
            $data['category_id'], $data['name'], $data['description'],
            $data['price'], $data['is_veg'] ?? 1,
            $data['is_available'] ?? 1, $data['is_featured'] ?? 0,
            $data['sort_order'] ?? 0, $id
        ]);
    }

    public function updateItemImage(int $id, string $image): bool
    {
        $stmt = $this->db->prepare("UPDATE menu_items SET image=? WHERE id=?");
        return $stmt->execute([$image, $id]);
    }

    public function deleteItem(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM menu_items WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function createCategory(array $data): bool
    {
        $stmt = $this->db->prepare("INSERT INTO categories (name, icon, sort_order) VALUES (?,?,?)");
        return $stmt->execute([$data['name'], $data['icon'] ?? '🍽️', $data['sort_order'] ?? 0]);
    }

    public function updateCategory(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE categories SET name=?, icon=?, sort_order=?, is_active=? WHERE id=?");
        return $stmt->execute([$data['name'], $data['icon'] ?? '🍽️', $data['sort_order'] ?? 0, $data['is_active'] ?? 1, $id]);
    }

    public function deleteCategory(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function getCategoryById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
