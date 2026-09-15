<?php
declare(strict_types=1);

namespace App\Models;

class Admin extends BaseModel
{
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT id, username, password FROM admin WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        return $admin ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, username, password FROM admin WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $admin = $stmt->fetch();
        return $admin ?: null;
    }

    public function updatePassword(int $adminId, string $newPasswordHash): bool
    {
        $stmt = $this->db->prepare('UPDATE admin SET password = ? WHERE id = ?');
        return $stmt->execute([$newPasswordHash, $adminId]);
    }
}
