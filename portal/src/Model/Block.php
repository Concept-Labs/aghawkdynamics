<?php
namespace App\Model;

use App\Core\Model;
use App\Core\Model\Collection;

class Block extends Model
{
    protected string $table = 'block';

    public function getParcel(): Parcel
    {
        return (new Parcel())
            ->load($this->data['parcel_id']);
    }

    
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
