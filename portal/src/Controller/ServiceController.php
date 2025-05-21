<?php
namespace App\Controller;

use App\Core\Controller;
use App\Core\Model\Collection;
use App\Model\Account\User;
use App\Model\ServiceRequest;

class ServiceController extends Controller
{

    public function index(): void
    {
        $uid = User::getInstance()->getId();
        $RequestCollection = (new ServiceRequest())->getCollection();
        $RequestCollection->setItemMode(Collection::ITEM_MODE_OBJECT);
        $RequestCollection->addFilter(
            [
                'account_id' => $uid,
            ]
        );
        $RequestCollection->sort('created_at', 'DESC');

        $this->render('service/list', ['requestCollection' => $RequestCollection]);
    }
}
