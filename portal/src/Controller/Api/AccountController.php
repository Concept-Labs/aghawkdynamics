<?php
namespace App\Controller;

use App\Core\Controller;
use App\Core\Model\Collection;
use App\Model\Account;
use App\Model\Account\User;

class AccountController extends Controller
{
    /**
     * AccountController constructor.
     * Ensures that only admin users can access this controller.
     */
    public function __construct()
    {
        parent::__construct();
        if (!User::isAdmin()) {
            $this->redirect('error/403');
        }
    }
    /**
     * Get JSON list of all accounts for admin users.
     *
     * @return void
     */
    public function list(): void
    {
        $accounts = (new Account())->getCollection()
            ->setItemMode(Collection::ITEM_MODE_OBJECT)
            ->sort('name', 'ASC');

        echo json_encode(
            iterator_to_array($accounts)
        );
    }
}