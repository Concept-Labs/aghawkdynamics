<?php
namespace App\Controller\Block;

use App\Core\ApiController as CoreApiController;
use App\Core\Model\Collection;
use App\Model\Account\User;
use App\Model\Parcel;

class ApiController extends CoreApiController
{
   public function list(): void
   {
        $parcelId = (int)$this->getRequest()->request('parcel_id', 0);
        if (!$parcelId) {
            return;
        }

        $parcel = (new Parcel())->load($parcelId);
        if (!$parcel->getId()) {
            $this->getRequest()->addError('Parcel not found');
            return;
        }

        if (User::getInstance()->getId() !== $parcel->getAccountId()) {
            return;
        }

        $blocks = $parcel->getBlocks()
            ->setItemMode(Collection::ITEM_MODE_ARRAY)
            ->sort('name', 'ASC');

        $this->json( iterator_to_array($blocks) );

   }
}
