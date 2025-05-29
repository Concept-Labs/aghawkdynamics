<?php
namespace App\Model;

use App\Core\Model;
use App\Model\Account\User;

class ServiceRequest extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    private ?Account $account = null;
    private ?Parcel $parcel = null;
    private ?Block $block = null;
    
    protected string $table = 'service_request';

    /**
     * Get the additional data associated with the service request.
     *
     * @return array
     */
    public function getAdditionalData(): array
    {
        $adds = $this->get('adds');
        if (is_string($adds)) {
            $adds = json_decode($adds, true);
        }
        return is_array($adds) ? $adds : [];
    }

    /**
     * Set additional data for the service request.
     *
     * @param array $adds
     */
    public function setAdditionalData(array $adds): void
    {
        $this->set('adds', json_encode($adds));
    }

    /**
     * Get the parcel associated with the service request.
     *
     * @return Parcel
     */
    public function getParcel(): Parcel
    {
        if (!$this->parcel instanceof Parcel) {
            $this->parcel =  (new Parcel())
                ->load($this->get('parcel_id') ?? 0);
        }

        return $this->parcel;
    }

    /**
     * Get the block associated with the service request.
     *
     * @return Block
     */
    public function getBlock(): Block
    {
        if (!$this->block instanceof Block) {
            $this->block =  (new Block())
                ->load($this->get('block_id'));
        }

        return $this->block;
    }

    /**
     * Get the user who created the service request.
     *
     * @return User
     */
    public function getAccount(): Account
    {
        if (!$this->account instanceof Account) {
            $this->account = (new Account())
            ->load($this->get('account_id'));
        }

        return $this->account;
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
        if (!in_array($status, self::STATUSES)) {
            throw new \InvalidArgumentException('Invalid status');
        }
        $this->set('status', $status);
    }

    /**
     * Check if the service request can be completed.
     *
     * @return bool
     */
    public function canCancel(): bool
    {
        return in_array($this->getStatus(), [self::STATUS_PENDING]);
    }

    

}
