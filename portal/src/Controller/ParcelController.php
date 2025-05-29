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

        if (!User::isAdmin() && $parcel->get('account_id') != User::getInstance()->getId()) {
            return null;
        }

        return $this->parcel = $parcel;
    }

    /** List with filters, sort, pagination, page size */
    public function index(): void
    {
        $collection = (new Parcel())->getCollection()
            ->setItemMode(Collection::ITEM_MODE_OBJECT)
            ->sort('name', 'ASC');

        if (!User::isAdmin()) {
            $collection->addFilter(['account_id' => User::uid()]);
        }

        $this->render('parcel/index', [
            'parcels'     => $collection->fetch()
        ]);
    }

    public function add(): void
    {

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->post('parcel', []);

            $data['account_id'] ??= User::getInstance()->getId();

            $parcelModel = (new Parcel())->create($data);

            $this->redirect('/?q=parcel/edit&id=' . $parcelModel->getId());
            exit;
        }

        $this->render(
            'parcel/parcel', 
            ['parcelModel' => new Parcel()]
        );
    }

    public function edit(): void
    {

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
            $this->getRequest()->addWarning('Parcel must have at least one block to be able to create a service request.');
        }

        $this->render(
            'parcel/parcel',
            ['parcelModel' => $parcel]
        );
    }

    /** CSV export */
    public function export(): void
    {
        $uid = User::getInstance()->getId();
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
