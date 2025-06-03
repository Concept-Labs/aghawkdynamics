<?php
namespace App\Model;

use App\Core\Model;
use App\Core\Model\Collection;

class Block extends Model
{
    const TABLE = 'block';
    
    protected string $table = self::TABLE;

    private ?Account $account = null;

    public function getAccountId(): int
    {
        return (int)($this->data['account_id'] ?? 0);
    }

    public function getAccount(): Account
    {
        if (!$this->account instanceof Account) {
            $this->account = (new Account())->load($this->getAccountId());
        }

        return $this->account;
    }
    /**
     * Get the ID of the block.
     *
     * @return int
     */

    /**
     * Get the ID of the block.
     *
     * @return int
     */
    public function getName(): string
    {
        return $this->data['name'] ?? '';
    }

    /**
     * Get the Parcel ID associated with this block.
     *
     * @return int
     */
    public function getParcelId(): int
    {
        return (int)($this->get('parcel_id') ?? 0);
    }

    /**
     * Get the Parcel associated with this block.
     * 
     * @return Parcel
     */
    public function getParcel(): Parcel
    {
        return (new Parcel())
            ->load($this->data['parcel_id']);
    }

    /**
     * Add an attachment to this block.
     * 
     * @param array $data
     */
    public function addAttachment(array $data): void
    {
        $data['block_id'] = $this->getId();
        if (empty($data['path']) || !file_exists($data['path'])) {
            throw new \InvalidArgumentException('Attachment path is invalid or does not exist.');
        }

        (new BlockAttachment())
            ->setData($data)
            ->save();
    }
    
    /**
     * Get the collection of attachments associated with this block.
     *
     * @return Collection
     */
    public function getAttachments(): Collection
    {
        $collection = (new BlockAttachment())->getCollection();
        $collection->setItemMode(Collection::ITEM_MODE_OBJECT);
        $collection->addFilter(['block_id' => $this->getId()]);
        $collection->sort('created_at', 'DESC');

        return $collection;
    }

}
