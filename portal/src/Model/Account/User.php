<?php

namespace App\Model\Account;

use App\Core\Request;
use App\Model\Account;

class User
{
    private static ?User $instance = null;
    private ?Request $request = null;
    private Account $accountModel;

    private function __construct()
    {
        $this->request = Request::getInstance();
        $this->accountModel = new Account();
        $this->accountModel->load($this->request->session('uid'));
    }

    public static function getInstance(): User
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getAccount(): Account
    {
        return $this->accountModel;
    }
    
}