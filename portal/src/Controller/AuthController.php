<?php

namespace App\Controller;

use App\Core\Config;
use App\Core\Controller;
use App\Exception\InvalidLoginException;
use App\Exception\SignUpException;
use App\Model\Account;
use App\Model\Account\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController extends Controller
{

    /**
     * Sign up a new user
     *
     * @return void
     */
    public function signup(): void
    {
        $err = null;
        if ($this->getRequest()->isPost()) {

            $data = $this->getRequest()->post();

            try {

                $data = Account::fillBilling($data);

                $this->validateSignUpData($data);

                $this->createAccount($data);

                $this->redirect('/auth/login');

            } catch (SignUpException $e) {
                $err = $e->getMessage();
            } catch (\Throwable $e) {
                $err = 'An error occurred: ' . $e->getMessage();
            }

        }

        $this->getRequest()->setSession('uid', null);
        $this->render(
            'auth/signup',
            [
                'data' => $this->getRequest()->post()
            ]
        );
    }

    /**
     * Validate signup data
     *
     * @param array $data The signup data
     * @return static
     * @throws SignUpException If validation fails
     */
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

    /**
     * Create a new account
     *
     * @param array $data
     */
    private function createAccount(array $data): Account
    {
        unset($data['password_repeat']);
        $data['password'] = Account::hashPassword($data['password']);
        $data['status'] = Account::STATUS_ACTIVE;

        return (new Account())->create($data);
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

                        if (User::isAdmin()) {
                            $this->redirect('/service/index');
                            return;
                        }

                        if (!User::getInstance()->hasParcels()) {
                            $this->redirect('/parcel/add');
                            return;
                        } else {
                            $this->redirect('/parcel/index');
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

    /**
     * Logout user
     *
     * @return void
     */
    public function logout(): void
    {
        session_destroy();
        $this->redirect('/auth/login');
    }

    /**
     * Render forgot password page
     *
     * @return void
     */
    public function forgot(): void
    {
        if (!$this->getRequest()->isPost()) {
            $this->render('auth/forgot');
            return;
        }
        try {
            $data = $this->getRequest()->post();
            $userEmail = $data['email'];

            if (!$this->validateForgotPasswordData($data)) {
                throw new \InvalidArgumentException('Invalid data provided');
            }

            $account = (new Account())->loadByEmail($userEmail);
            if (!$account->getId()) {
                throw new \InvalidArgumentException('No account found with that email address');
            }
            $resetToken = $account->createResetToken();
            $resetLink = Config::get('domain') . '/?q=auth/reset&token=' . $resetToken;

            ob_start();
            require __DIR__ . '/../../views/email/reset_password.phtml';
            $emailContent = ob_get_clean();

            // Send reset email

            // Use PHPMailer to send the reset email

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = Config::get('smtp_host');
                $mail->SMTPAuth   = true;
                $mail->Username   = Config::get('smtp_username');
                $mail->Password   = Config::get('smtp_password');
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom(Config::get('from_email'), Config::get('from_name'));
                $mail->addAddress($userEmail);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = $emailContent;

                $mail->send();
            } catch (Exception $e) {
                throw new \RuntimeException('Could not send reset email. Mailer Error: ' . $mail->ErrorInfo);
            }

            $this->getRequest()->addInfo('A password reset link has been sent to your email address.');
            $this->redirect('/?q=auth/login');

        } catch (\Throwable $e) {
            $this->getRequest()->addError('An error occurred: ' . $e->getMessage());
            
        }

        $this->redirectReferer();
    }

    /**
     * Validate forgot password data
     *
     * @param array $data The forgot password data
     * @return bool True if valid, false otherwise
     */
    private function validateForgotPasswordData(array $data): bool
    {
        if (empty($data['email'])) {
            $this->getRequest()->addError('Please enter your email address');
            return false;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->getRequest()->addError('Invalid email address');
            return false;
        }

        $account = (new Account())->findByEmail($data['email']);
        if (!$account) {
            $this->getRequest()->addError('No account found with that email address');
            return false;
        }

        return true;
    }

}
