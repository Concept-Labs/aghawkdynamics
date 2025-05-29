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
            return;
        }

        $model = (new Account())->load($id);

        if (!$model->getId()) {
            $this->redirect('/?q=auth/login');
            return;
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->post();
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            if ($data) $model->update($data);
            $this->getRequest()->addInfo('Profile updated');
        }

        

        $this->render('account/profile', ['user'=>$model]);
    }

    /**
     * List all accounts for admin users.
     * 
     * @return void
     */
    public function list(): void
    {

        if (!\App\Model\Account\User::isAdmin()) {
            $this->getRequest()->addError('Access denied');
            $this->redirect('/?q=account/profile');
            return;
        }

        $accounts = (new Account())->getCollection()
            ->setItemMode(\App\Core\Model\Collection::ITEM_MODE_OBJECT)
            ->sort('name', 'ASC');

        $this->render('account/list', ['accounts' => $accounts]);

    }
}
