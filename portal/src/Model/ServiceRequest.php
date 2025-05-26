<?php
namespace App\Model;

use App\Core\Model;
use App\Model\Account\User;

class ServiceRequest extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    
    protected string $table = 'service_request';

    /**
     * Get the parcel associated with the service request.
     *
     * @return Parcel
     */
    public function getParcel(): Parcel
    {
        return (new Parcel())
            ->load($this->get('parcel_id'));
    }

    /**
     * Get the block associated with the service request.
     *
     * @return Block
     */
    public function getBlock(): Block
    {
        return (new Block())
            ->load($this->get('block_id'));
    }

    /**
     * Get the user who created the service request.
     *
     * @return User
     */
    public function getAccount(): Account
    {
        return (new Account())
            ->load($this->get('account_id'));
    }

    /**
     * Get the status of the service request.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->get('status') ?? self::STATUS_PENDING;
    }

    /**
     * Set the status of the service request.
     *
     * @param string $status
     * @throws \InvalidArgumentException
     */
    public function setStatus(string $status): void
    {
        if (!in_array($status, [self::STATUS_PENDING, self::STATUS_COMPLETED])) {
            throw new \InvalidArgumentException('Invalid status');
        }
        $this->set('status', $status);
    }

    public function canCancel(): bool
    {
        return $this->getStatus() === self::STATUS_PENDING;
    }

    public function getAdds(): array
    {
        $adds = $this->get('adds');
        if (is_string($adds)) {
            $adds = json_decode($adds, true);
        }
        return is_array($adds) ? $adds : [];
    }

    public function setAdds(array $adds): void
    {
        $this->set('adds', json_encode($adds));
    }

}
