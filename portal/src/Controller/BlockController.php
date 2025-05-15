<?php
namespace App\Controller;

use App\Core\Controller;
use App\Model\Block;
use App\Model\Parcel;

class BlockController extends Controller
{
    public function index(): void
    {
        session_start();
        $uid = $_SESSION['uid'] ?? 0;
        if (!$uid) {
            header('Location: /?q=auth/login');
            exit;
        }
        $blocks = (new Block())->all();
        $this->render('block/index', ['blocks' => $blocks]);
    }

    public function add(): void
    {
        session_start();
        $uid = $_SESSION['uid'] ?? 0;
        if (!$uid) {
            header('Location: /?q=auth/login');
            exit;
        }
        $parcelModel = new Parcel();
        $parcels = $parcelModel->all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bmodel = new Block();
            $data = array_intersect_key($_POST, array_flip(['parcel_id','name','crop_category','acres','notes']));
            $data['account_id'] = $uid;
            $bmodel->create($data);
            header('Location: /?q=block/index');
            exit;
        }

        $this->render('block/add', ['parcels'=>$parcels]);
    }
}
