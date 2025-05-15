<?php
namespace App\Controller;

use App\Core\Config;
use App\Core\Controller;
use App\Model\Account;

class AuthController extends Controller
{
    public function signup(): void
    {
        session_start();
        $err = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array_intersect_key($_POST, array_flip([
                'email','password','password_repeat','name','street','city','state',
                'zip','billing_street','billing_city','billing_state','billing_zip',
                'acreage_size'
            ]));
            if ($data['password'] !== $data['password_repeat']) {
                $err = 'Passwords do not match';
            } elseif ((new Account())->findByEmail($data['email'])) {
                $err = 'Email already exists';
            } else {
                unset($data['password_repeat']);
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                $data['created_at']=date('Y-m-d H:i:s');
                $data['status']='active';
                (new Account())->create($data);
                $this->redirect('/?q=auth/login');
                exit;
            }
        }
        $this->render('auth/signup', 
        [
            'error'=>$err,
            'states' => Config::get('states'),
        ]
    );
    }

    public function login(): void
    {
        session_start();
        $err = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $acc = new Account();
            $user = $acc->findByEmail($_POST['email'] ?? '');
            if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
                $_SESSION['uid'] = $user['id'];
                $this->redirect('/?q=parcel/index');
                exit;
            }
            $err = 'Invalid email or password';
        }
        $this->render('auth/login', ['error' => $err]);
    }

    public function logout(): void
    {
        session_start();
        session_destroy();
        $this->redirect('/?q=auth/login');
    }
}
