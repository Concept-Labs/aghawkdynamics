<?php
namespace App\Controller;

use App\Core\Config;
use App\Core\Controller;
use App\Model\Parcel;
use App\Core\Database;
use App\Core\Model\Collection;
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

        $collection = (new Parcel())->getCollection();
        $collection->setItemMode(Collection::ITEM_MODE_OBJECT);

        $collection->addFilter(
            [
                'account_id' => $uid,
            ]
        );

        $collection->sort('name', 'ASC');

        $collection->setPageSize(1000);

        $this->render('parcel/index', [
            'parcels'     => $collection->fetch()
        ]);
    }

    public function add(): void
    {
        $this->getRequest()->holdReferer();
        
        $uid = User::getInstance()->getId();

        if (!$uid) { $this->redirect('/?q=auth/login'); exit; }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->post('parcel', []);
            $data['account_id'] = $uid;

            $parcelModel = (new Parcel())->create($data);
            $this->redirect('/?q=parcel/edit&id=' . $parcelModel->getId());
            exit;
        }

        $this->render('parcel/parcel', [
            'parcelModel' => new Parcel(),
            'states' => Config::get('states'),
            'crop_category' => Config::get('crop_category'),
        ]);
    }

    public function edit(): void
    {
        $this->getRequest()->holdReferer();

        try {
            $pid = (int)$this->getRequest('id', 0);
        
            $parcel = $this->getParcel($pid);

            if (!$parcel instanceof Parcel) {
                throw new \Exception('Parcel not found');
            }

            if ($this->getRequest()->isPost()) {
                $pdata = $this->getRequest()->post('parcel', []);

                $parcel
                    ->setData($pdata)
                    ->save();

                $this->getRequest()->addInfo('Parcel has been updated');
                $this->redirectReferer();
                exit;
            }

            
        } catch (\Throwable $e) {
            $this->getRequest()->addError($e->getMessage());
            $this->redirectReferer();
            exit;
        }

        $blocks = $parcel->getBlocks();

        if ($blocks->count() < 1) {
            $this->getRequest()->addWarning('Parcel must have at least one block');
        }

        $this->render('parcel/parcel', [
            'states' => Config::get('states'),
            'crop_category' => Config::get('crop_category'),
            'parcelModel' => $parcel,
        ]);
    }

    public function deleteit(): void
    {
        $r = (int)$this->getRequest()->post('parcel', 0);
        $pid = (int)$r['dp'] ?? 0;
        $uid = User::getInstance()->getId();
        if (!$uid) { $this->redirect('/?q=auth/login'); exit; }
        try  {
            if ($pid < 1) { 
                $this->getRequest()->addError('Parcel ID is required');
                $this->redirectReferer(); 
                exit; 
            }

            $parcel = (new Parcel())->load($pid);

            if (!$parcel->getId()) {
                $this->getRequest()->addError('Parcel not found');
                $this->redirectReferer();
                exit;
            }

            if ($parcel->get('account_id') != $this->getRequest()->session('uid')) {
                $this->getRequest()->addError('Parcel not found !');
                $this->redirectReferer();
                exit;
            }

            
            $parcel->delete($pid);
        } catch (\Throwable $e) {
            $this->getRequest()->addError($e->getMessage());
            $this->redirectReferer();
            exit;
        }
         
        $this->getRequest()->addInfo('Parcel has been deleted');
        $this->redirectReferer();
        exit;

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
