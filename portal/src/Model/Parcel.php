<?php
namespace App\Model;

use App\Core\Model;
use App\Core\Model\Collection;
use App\Core\Model\CollectionInterface;
use App\Model\Account\User;

class Parcel extends Model
{
    const BLOCKS_LIMIT = 10;

    protected string $table = 'parcel';

    private ?Account $account = null;

    private ?CollectionInterface $blocks = null;

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
     * Set the account ID for the parcel.
     *
     * @param int $accountId
     * @return $this
     */
    public function getAccount(): Account
    {
        if ($this->account === null) {
            $this->account = (new Account())->load($this->getAccountId());
        }
        return $this->account;
    }

    /**
     * Get the name of the parcel.
     *
     * @return string|null
     */
    public function getName(): string|null
    {
        return $this->get('name') ?? null;
    }

    /**
     * Get the collection of blocks associated with this parcel.
     *
     * @return CollectionInterface
     */
    public function getBlocks(): CollectionInterface
    {
        if ($this->blocks === null) {
            $this->blocks = (new Block())
                ->getCollection()
                ->setItemMode(Collection::ITEM_MODE_OBJECT)
                ->addFilter(['parcel_id' => $this->getId()])
                ->setItemMode(Collection::ITEM_MODE_OBJECT)
                ->sort('created_at', 'DESC');
        }

        return $this->blocks;
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
