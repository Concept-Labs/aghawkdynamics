<?php
namespace App\Model;

use App\Core\Model;
use App\Core\Model\Collection;
use App\Core\Model\CollectionInterface;

class Account extends Model
{
    
    protected string $table = 'account';

    public function isAdmin(): bool
    {
        return (bool)$this->get('is_admin', false);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        
        return $stmt->fetch() ?: null;
    }


    public function getParcels(): CollectionInterface
    {
        $parcelCollection = (new Parcel())
            ->getCollection()
            ->setItemMode(Collection::ITEM_MODE_OBJECT)
            ->addFilter(['account_id' => $this->getId()])
            ->sort('created_at', 'DESC');

        return $parcelCollection;
    }
}
