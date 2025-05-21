<?php

namespace App\Model\Account;

use App\Core\Model\Collection;
use App\Core\Request;
use App\Model\Account;
use App\Model\Parcel;

class User
{
    private static ?User $instance = null;
    private ?Request $request = null;
    private Account $accountModel;

    private function __construct()
    {
        $this->request = Request::getInstance();

        $this->accountModel = new Account();
        
        if ($this->isLoggedIn()) {
            $this->accountModel->load($this->request->session('uid'));
        }
        
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

    public function hasParcels(): bool
    {
        $parcels = (new Parcel())->getCollection();
        $parcels->setItemMode(Collection::ITEM_MODE_ARRAY);

        return $parcels->addFilter(['account_id' => $this->getId()])->count() > 0;
    }

    public function isLoggedIn(): bool
    {
        return $this->request->session('uid') !== null;
    }

    public function getId(): int
    {
        return $this->request->session('uid');
    }

}