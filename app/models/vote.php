<?php
declare(strict_types=1);

namespace App\Models;

use Throwable;

class Vote extends BaseModel
{
    public function submitVote(int $userId, int $candidateId): bool
    {
        try {
            $this->db->beginTransaction();

            // Row-lock User to check status securely
            $stmtUser = $this->db->prepare("SELECT status_vote FROM users WHERE id = ? FOR UPDATE");
            $stmtUser->execute([$userId]);
            $user = $stmtUser->fetch();

            // Row-lock Candidate to ensure candidate validity
            $stmtCand = $this->db->prepare("SELECT id FROM candidates WHERE id = ? FOR UPDATE");
            $stmtCand->execute([$candidateId]);
            $candidate = $stmtCand->fetch();

            if (!$user || $user['status_vote'] === 'sudah' || !$candidate) {
                $this->db->rollBack();
                return false;
            }

            // Record the vote
            $stmtInsert = $this->db->prepare('INSERT INTO votes (user_id, candidate_id, voted_at) VALUES (?, ?, NOW())');
            $stmtInsert->execute([$userId, $candidateId]);

            // Increment candidate total votes
            $stmtUpdateCand = $this->db->prepare('UPDATE candidates SET total_votes = total_votes + 1 WHERE id = ?');
            $stmtUpdateCand->execute([$candidateId]);

            // Update user voting status
            $stmtUpdateUser = $this->db->prepare("UPDATE users SET status_vote = 'sudah' WHERE id = ?");
            $stmtUpdateUser->execute([$userId]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function countTotal(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM votes')->fetchColumn();
    }
}
