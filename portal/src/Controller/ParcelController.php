<?php
namespace App\Controller;

use App\Core\Config;
use App\Core\Controller;
use App\Model\Parcel;
use App\Core\Database;
use App\Core\Model\Collection;
use App\Model\Account;
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

    /**
     * Export the list of parcels as a CSV file.
     *
     * @return void
     */
     public function exportAll(): void
    {
        $parcelCollection = (new Parcel())->getCollection();

        $rawSql = sprintf(
            'SELECT p.*, a.name AS account_name FROM %s p LEFT JOIN %s a ON p.account_id = a.id',
            (new Parcel())->getTable(),
            (new Account())->getTable()
        );

        if (!User::isAdmin()) {
            // If the user is not an admin, filter blocks by account ID
            $parcelCollection->addFilter(
                [
                    'account_id' => User::getInstance()->getId(),
                ]
            );
        }

        $parcelCollection->setRawSql($rawSql);
        $parcelCollection->setItemMode(Collection::ITEM_MODE_ARRAY);
        $parcelCollection->sort('created_at', 'DESC');


        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="parcels.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $output = fopen('php://output', 'w');
        $separator = ';';
        fputcsv($output, ['Parcel UID', 'Parcel Nickname', 'Business Name', 'Parcel Address', 'City', 'State', 'ZIP', 'Acres'], $separator);

        foreach ($parcelCollection as $parcel) {
            fputcsv($output, [
                $parcel['id'],
                $parcel['name'],
                $parcel['account_name'],
                $parcel['street'],
                $parcel['city'],
                $parcel['state'],
                $parcel['zip'],
                number_format($parcel['estimated_acres'], 3)
            ], $separator);
        }
        fclose($output);
        exit;

    }
}
