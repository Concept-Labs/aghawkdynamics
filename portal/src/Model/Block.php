<?php
namespace App\Model;

use App\Core\Model;
use PDO;

class Block extends Model
{
    protected string $table = 'block';

    /** Повертає до $limit блоків, прив’язаних до parcel */
    public function listByParcel(int $parcelId, int $limit = 10): array
    {
        $sql = "SELECT id, name, acres, crop_category
                FROM {$this->table}
                WHERE parcel_id = :pid
                LIMIT :lim";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':pid', $parcelId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit,     PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
