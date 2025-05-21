<?php
namespace App\Model;

use App\Core\Model;
use App\Core\Model\Collection;
use App\Core\Model\CollectionInterface;

class Parcel extends Model
{
    const BLOCKS_LIMIT = 10;
    protected string $table = 'parcel';

   public function getAccountId(): int
    {
        return (int)($this->data['account_id'] ?? 0);
    }

    public function getBlocks(): CollectionInterface
    {
        $blockCollection = (new Block())->getCollection();
        $blockCollection->setItemMode(Collection::ITEM_MODE_OBJECT);
        $blockCollection->addFilter(
            [
                'parcel_id' => $this->getId(),
            ]
        );
        $blockCollection->sort('created_at', 'DESC');

        return $blockCollection;
    }

    public function isBlockLimitReached(): bool
    {
        return $this->getBlocks()->count() >= self::BLOCKS_LIMIT;
    }


}
