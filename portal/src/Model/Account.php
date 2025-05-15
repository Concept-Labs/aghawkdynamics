<?php
namespace App\Model;

use App\Core\Model;

class Account extends Model
{
    protected string $table = 'account';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }
}
