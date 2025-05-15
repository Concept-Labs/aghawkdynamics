<?php
namespace App\Controller;

use App\Core\Controller;
use App\Model\ServiceRequest;
use App\Model\Block;

class ServiceRequestController extends Controller
{
    public function request(): void
    {
        session_start();
        $uid = $_SESSION['uid'] ?? 0;
        if (!$uid) {
            header('Location: /?q=auth/login');
            exit;
        }
        $blocks = (new Block())->all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new ServiceRequest();
            $data = array_intersect_key($_POST, array_flip([
                'block_id','service_type','reason','urgent','urgent_date',
                'is_recurring','recurring_frequency','recurring_end_date'
            ]));
            $data['account_id'] = $uid;
            $model->create($data);
            echo 'Service request created.';
            return;
        }

        $this->render('service/request', ['blocks'=>$blocks]);
    }
}
