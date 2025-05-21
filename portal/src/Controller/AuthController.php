<?php

namespace App\Controller;

use App\Core\Config;
use App\Core\Controller;
use App\Exception\InvalidLoginException;
use App\Exception\SignUpException;
use App\Model\Account;
use App\Model\Account\User;

class AuthController extends Controller
{

    public function signup(): void
    {
        $err = null;
        if ($this->getRequest()->isPost()) {

            $data = $this->getRequest()->post();

            try {
                ;
                $data = $this->validateSignUpData($data)
                    ->fillBillingAddress($data);
                $err = null;
            } catch (SignUpException $e) {
                $err = $e->getMessage();
            } catch (\Throwable $e) {
                $err = 'An error occurred: ' . $e->getMessage();
            }

            if ($err === null) {
                $this->createAccount($data);
                $this->redirect('/?q=auth/login');
                exit;
            }
        }

        $this->getRequest()->setSession('uid', null);
        $this->render(
            'auth/signup',
            [
                'error' => $err,
                'states' => Config::get('states'),
                'data' => $this->getRequest()->post()
            ]
        );
    }

    private function fillBillingAddress(array $data): array
    {
        $data['billing_street'] = empty($data['billing_street']) ? $data['street'] : $data['billing_street'];
        $data['billing_city'] = empty($data['billing_city']) ? $data['city'] : $data['billing_city'];
        $data['billing_state'] = empty($data['billing_state']) ? $data['state'] : $data['billing_state'];
        $data['billing_zip'] = empty($data['billing_zip']) ? $data['zip'] : $data['billing_zip'];

        return $data;
    }

    private function validateSignUpData(array $data): static
    {
        $requiredFields = [
            'email',
            'password',
            'password_repeat',
            'name',
            'street',
            'city',
            'state',
            'zip',
            'acreage_size'
        ];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new SignUpException('Please fill in all fields');
            }
        }
        if ($data['acreage_size'] < 0) {
            throw new SignUpException('Acreage size must be a positive number');
        }
        if ($data['password'] !== $data['password_repeat']) {
            throw new SignUpException('Passwords do not match');
        }
        if ((new Account())->findByEmail($data['email'])) {
            throw new SignUpException('Email already exists');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new SignUpException('Invalid email address');
        }
        
        // if (!preg_match('/^[0-9]{5}$/', $data['zip'])) {
        //     throw new SignUpException('Invalid zip code');
        // }
        // if (!preg_match('/^[0-9]{10}$/', $data['phone'])) {
        //     throw new SignUpException('Invalid phone number');
        // }

        return $this;
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    /**
     * Create a new account
     *
     * @param array $data
     */
    private function createAccount(array $data): void
    {
        unset($data['password_repeat']);
        $data['password'] = $this->hashPassword($data['password']);
        //$data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = 'active';
        (new Account())->create($data);
    }

    /**
     * Login user
     *
     * @return void
     */
    public function login(): void
    {
        $err = null;
        try {
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->post();
                if (null === $err = $this->validateLoginData($data)) {
                    $acc = new Account();
                    $user = $acc->findByEmail($data['email'] ?? '');
                    if ($user && password_verify($data['password'] ?? '', $user['password'])) {
                        $this->getRequest()->setSession('uid', $user['id']);

                        if (!User::getInstance()->hasParcels()) {
                            $this->redirect('/?q=parcel/add');
                            return;
                        } else {
                            $this->redirect('/?q=parcel/index');
                            return;
                        }
                    }

                    throw new InvalidLoginException('Invalid email or password');
                }
            }
        } catch (InvalidLoginException $e) {
            //$err = $e->getMessage();
            $this->getRequest()->addError($e->getMessage());
        } catch (\Throwable $e) {
            $err = 'An error occurred: ' . $e->getMessage();
        }

        $this->getRequest()->setSession('uid', null);

        $this->render('auth/login', ['error' => $err]);
    }

    /**
     * Validate login data
     *
     * @param array $data  The login data
     * @return string|null Error message or null if valid
     */
    private function validateLoginData(array $data): ?string
    {
        if (empty($data['email']) || empty($data['password'])) {
            return 'Please fill in all fields';
        }
        return null;
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('/?q=auth/login');
    }

    public function forgot(): void
    {
        $this->render('auth/forgot');
    }

}
