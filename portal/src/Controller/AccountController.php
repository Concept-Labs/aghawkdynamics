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
        $id = $this->getRequest()->session('uid');

        if (!$id) {
            $this->redirect('/?q=auth/login');
            exit;
        }

        $model = new Account();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->post();
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            if ($data) $model->update($id,$data);
        }
        $user = $model->find($id);
        $this->render('account/profile', ['user'=>$user]);
    }
}
