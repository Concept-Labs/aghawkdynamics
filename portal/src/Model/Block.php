<?php
namespace App\Model;

use App\Core\Model;

class Block extends Model
{
    protected string $table = 'block';

    public function getParcel(): Parcel
    {
        return (new Parcel())
            ->load($this->data['parcel_id']);
    }

}
