<?php
class AdminModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE admins SET password=?, reset_token=NULL, token_expiry=NULL WHERE id=?");
        return $stmt->execute([$hash, $id]);
    }

    public function createAdmin(string $username, string $password, string $name): bool
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO admins (username, password, name) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $hash, $name]);
    }
}
