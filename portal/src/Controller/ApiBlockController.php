<?php
namespace App\Controller;

use App\Core\Controller;
use App\Model\Block;

class ApiBlockController extends Controller
{
    public function list(): void
    {
        header('Content-Type: application/json');
        $id    = (int)($_GET['id'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 10);
        if ($id <= 0) { echo json_encode(['error'=>'bad id']); return; }

        $blocks = (new Block())->listByParcel($id, $limit);
        echo json_encode($blocks);
    }
}
