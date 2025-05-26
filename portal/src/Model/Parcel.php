<?php
namespace App\Model;

use App\Core\Model;
use App\Core\Model\Collection;
use App\Core\Model\CollectionInterface;

class Parcel extends Model
{
    const BLOCKS_LIMIT = 10;
    protected string $table = 'parcel';

    /**
     * Get the ID of the parcel.
     *
     * @return int
     */
   public function getAccountId(): int
    {
        return (int)($this->data['account_id'] ?? 0);
    }

    /**
     * Get the collection of blocks associated with this parcel.
     *
     * @return CollectionInterface
     */
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

    /**
     * Validate block data before saving.
     *
     * @param array $blockData
     * @return array
     */
    public function isBlockLimitReached(): bool
    {
        return $this->getBlocks()->count() >= self::BLOCKS_LIMIT;
    }
    
    /**
     * Check if the user can request a service based on the presence of blocks.
     *
     * @return bool
     */
    public function canRequestService(): bool
    {
        return !$this->getBlocks()->isEmpty(); 
    }

}
