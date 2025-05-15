<?php
namespace App\Controller;

use App\Core\Controller;
use App\Model\Account;

/**
 * Handles account listing (admin) and profile editing for current user
 */
class AccountController extends Controller
{
    /** List of accounts (admin usage) */
    public function index(): void
    {
        $accounts = (new Account())->all();
        $this->render('account/index', ['accounts'=>$accounts]);
    }

    /** Profile page for logged‑in user  */
    public function profile(): void
    {
        session_start();
        $id = $_SESSION['uid'] ?? 0;
        if (!$id) {
            header('Location: /?q=auth/login');
            exit;
        }

        $model = new Account();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array_intersect_key($_POST, array_flip(['name','street','city','state','zip','phone']));
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            if ($data) $model->update($id,$data);
        }
        $user = $model->find($id);
        $this->render('account/profile', ['user'=>$user]);
    }
}
