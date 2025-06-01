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

    /**
     * Find an account by email
     *
     * @param string $email The email to search for
     * @return array|null Returns account data if found, null otherwise
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        
        return $stmt->fetch() ?: null;
    }

    /**
     * Load account data by email
     *
     * @param string $email The email to search for
     * @return self|null Returns the Account object if found, null otherwise
     */
    public function loadByEmail(string $email): ?self
    {
        $data = $this->findByEmail($email);
        if ($data) {
            $this->setData($data);
            return $this;
        }
        return null;
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

    /**
     * Create a reset token for the account
     *
     * @return string The generated reset token
     */
    public function createResetToken(): string
    {
        $token = bin2hex(random_bytes(16));
        $this->set('reset_token', $token);
        $this->save();

        return $token;
    }

    /**
     * Reset the account password using a reset token
     *
     * @param string $token The reset token
     * @param string $newPassword The new password
     * @param string $confirmPassword The confirmation of the new password
     * @return bool Returns true if the password was reset successfully
     * @throws \InvalidArgumentException If the passwords do not match or the token is invalid
     */
    public function resetPassword(string $token, string $newPassword, string $confirmPassword): bool
    {
        if ($newPassword !== $confirmPassword) {
            throw new \InvalidArgumentException('Passwords do not match.');
        }

        if ($this->get('reset_token') !== $token) {
            throw new \InvalidArgumentException('Invalid reset token.');
        }

        $this->set('password', password_hash($newPassword, PASSWORD_DEFAULT));
        $this->set('reset_token', null); // Clear the reset token after use

        $this->save();

        return true;
    }
}
