<?php
declare(strict_types=1);

namespace App\Models;

class Candidate extends BaseModel
{
    public function getAll(): array
    {
        return $this->db->query('SELECT * FROM candidates ORDER BY number ASC')->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, number, chair_name, total_votes, vision, mission, photo FROM candidates WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $candidate = $stmt->fetch();
        return $candidate ?: null;
    }

    public function updateCandidate(int $id, string $chairName, string $vision, string $mission, ?string $photo): bool
    {
        if ($photo !== null) {
            $stmt = $this->db->prepare('UPDATE candidates SET chair_name = ?, vision = ?, mission = ?, photo = ? WHERE id = ?');
            return $stmt->execute([$chairName, $vision, $mission, $photo, $id]);
        } else {
            $stmt = $this->db->prepare('UPDATE candidates SET chair_name = ?, vision = ?, mission = ? WHERE id = ?');
            return $stmt->execute([$chairName, $vision, $mission, $id]);
        }
    }
}
