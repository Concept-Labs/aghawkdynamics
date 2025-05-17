<?php
namespace App\Controller;

use App\Core\Config;
use App\Core\Controller;
use App\Model\Parcel;
use App\Core\Database;
use App\Model\Account\User;

class ParcelController extends Controller
{

    private ?Parcel $parcel = null;

    protected function getParcel(string $id): ?Parcel
    {
        if (!$this->parcel instanceof Parcel) {
            
            $parcel = new Parcel();
            $parcel->load($id);
            if (!$parcel->getId()) {
                return null;
            }
        }

        if ($parcel->get('account_id') != User::getInstance()->getId()) {
            return null;
        }

        return $this->parcel = $parcel;
    }

    /** List with filters, sort, pagination, page size */
    public function index(): void
    {
        $uid = User::getInstance()->getId();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPageOpts = [10,25,50];
        $perPage = in_array((int)($_GET['limit'] ?? 10), $perPageOpts) ? (int)$_GET['limit'] : 10;

        $sort  = $_GET['sort'] ?? 'name';
        $dir   = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
        $allowedSort = ['name','status','created_at'];
        if (!in_array($sort, $allowedSort)) $sort = 'name';

        $nameFilter = trim($_GET['f_name'] ?? '');
        $addrFilter = trim($_GET['f_address'] ?? '');

        $model  = new Parcel();
        $params = ['account_id'=>$uid];
        $where  = 'WHERE account_id = :account_id';

        if ($nameFilter !== '') {
            $where .= ' AND name LIKE :fname';
            $params['fname'] = '%' . $nameFilter . '%';
        }
        if ($addrFilter !== '') {
            $where .= ' AND (street LIKE :addr OR city LIKE :addr OR state LIKE :addr OR zip LIKE :addr)';
            $params['addr'] = '%' . $addrFilter . '%';
        }

        $total = $model->countWhere($where, $params);
        $perPage = max(1,$perPage); // safeguard
$perPage = 10000; //hardcode for now
        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) $page = $pages;
        $offset = ($page - 1) * $perPage;

        $parcels = $model->listWhere($where, $params, $sort, $dir, $perPage, $offset);

        // Block counts
        $blockCounts = [];
        if ($parcels) {
            $db  = Database::connect();
            $ids = array_column($parcels, 'id');
            $in  = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("SELECT parcel_id, COUNT(*) cnt FROM block WHERE parcel_id IN ($in) GROUP BY parcel_id");
            $stmt->execute($ids);
            foreach ($stmt->fetchAll() as $row) {
                $blockCounts[$row['parcel_id']] = $row['cnt'];
            }
        }

        $this->render('parcel/index', [
            'parcels'     => $parcels,
            'counts'      => $blockCounts,
            'page'        => $page,
            'pages'       => $pages,
            'perPage'     => $perPage,
            'perPageOpts' => $perPageOpts,
            'sort'        => $sort,
            'dir'         => $dir,
            'nameFilter'  => $nameFilter,
            'addrFilter'  => $addrFilter
        ]);
    }

    /** Add new parcel + blocks (unchanged) */
    public function add(): void
    {
        $uid = User::getInstance()->getId();
        if (!$uid) { $this->redirect('/?q=auth/login'); exit; }

        if ($this->getRequest()->isPost()) {
            $parcelModel = new Parcel();
            $data = $this->getRequest()->post('parcel', []);
            $data['account_id'] = $uid;
            $parcelModel->create($data);
            $this->redirect('/?q=parcel/edit&id=' . $parcelModel->getId());
            exit;
        }

        $this->render('parcel/parcel', [
            'states' => Config::get('states'),
            'crop_category' => Config::get('crop_category'),
        ]);
    }

    public function edit(): void
    {
        
        $pid = (int)$this->getRequest('id', 0);
       
        $parcel = $this->getParcel($pid);

        if (!$parcel instanceof Parcel) {
            $this->redirectReferer();
            exit;
        }

        if ($this->getRequest()->isPost()) {
            $pdata = $this->getRequest()->post('parcel', []);
            $parcel
                ->setData($pdata)
                ->save();
            $this->getRequest()->addMessage('Parcel updated successfully!');
            $this->redirectReferer();
            exit;
        }

        $this->render('parcel/parcel', [
            'states' => Config::get('states'),
            'crop_category' => Config::get('crop_category'),
            'parcel' => $parcel->getData(),
        ]);
    }

    public function delete(): void
    {
        $pid = (int)$this->getRequest()->request('id', 0);
        if ($pid < 1) { $this->redirect('parcel/index'); exit; }

        $parcel = new Parcel();
        $parcel->load($pid);
        if (!$parcel || $parcel->get('account_id') != $this->getRequest()->session('uid')) {
            $this->redirectReferer();
            exit;
        }
        if ($this->getRequest()->isPost()) {
            $parcel->delete($pid);
            $this->redirectReferer();
            exit;
        }

    }


    /** CSV export */
    public function export(): void
    {
        $uid = $_SESSION['uid'] ?? 0;
        if (!$uid) { header('Location: /?q=auth/login'); exit; }

        $model = new Parcel();
        $rows  = $model->listWhere('WHERE account_id = :id', ['id'=>$uid], 'name', 'ASC', 100000, 0);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="parcels.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','Name','Street','City','State','ZIP','Status','Created']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['id'],$r['name'],$r['street'],$r['city'],$r['state'],$r['zip'],$r['status'],$r['created_at']]);
        }
        fclose($out);
    }
}
