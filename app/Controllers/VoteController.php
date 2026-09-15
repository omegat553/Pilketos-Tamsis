<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Vote;

class VoteController extends Controller
{
    private function requireStudent(): void
    {
        if (!Session::has('user_id') || !Session::has('user_username')) {
            $this->redirect('/login');
        }
    }

    public function index(): void
    {
        $this->requireStudent();

        $userId = (int) Session::get('user_id');
        $userModel = new User();
        $currentUser = $userModel->findById($userId);

        if (!$currentUser || $currentUser['status_vote'] === 'sudah') {
            Session::remove('user_id');
            Session::remove('user_username');
            Session::flash('error', 'Akun ini sudah digunakan untuk memilih dan tidak boleh voting lagi.');
            $this->redirect('/login');
        }

        if ($this->isPost()) {
            $this->validateCsrf();

            $candidateId = filter_input(INPUT_POST, 'candidate_id', FILTER_VALIDATE_INT);
            if (!$candidateId) {
                Session::flash('error', 'Pilih salah satu calon ketua.');
                $this->redirect('/vote');
            }

            $voteModel = new Vote();
            $success = $voteModel->submitVote($userId, $candidateId);

            if ($success) {
                Session::remove('user_id');
                Session::remove('user_username');
                Session::set('vote_success', true);
                $this->redirect('/thanks');
            } else {
                Session::flash('error', 'Voting gagal diproses. Silakan coba lagi.');
                $this->redirect('/vote');
            }
        }

        $candidateModel = new Candidate();
        $candidates = $candidateModel->getAll();
        $flash = Session::getFlash();

        $this->render('student/vote', compact('candidates', 'flash'));
    }

    public function thanks(): void
    {
        if (!Session::get('vote_success')) {
            $this->redirect('/login');
        }
        
        // Remove the temporary vote success state upon rendering
        Session::remove('vote_success');
        $this->render('student/thanks');
    }
}
