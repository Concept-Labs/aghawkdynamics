<?php
namespace App\Controller;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Model\Collection;
use App\Model\Account\User;
use App\Model\Block;
use App\Model\Parcel;

class BlockController extends Controller
{

    public function index(): void
    {
        $blockCollection = (new Block())->getCollection();

        $blockCollection->addFilter(
            [
                'account_id' => User::getInstance()->getId(),
            ]
        );

        $blockCollection->setItemMode(Collection::ITEM_MODE_OBJECT);
        $blockCollection->sort('name', 'ASC');

        $this->render('block/index', [
            'blocks' => $blockCollection,
        ]);
    }
   

    public function add(): void
    {

        if (!$this->getRequest()->isPost()) {
            $parcels = (new Parcel())->getCollection()
                ->setItemMode(Collection::ITEM_MODE_ARRAY)
                ->addFilter(
                    [
                        'account_id' => User::getInstance()->getId(),
                    ]
                )
                ->sort('name', 'ASC');

            $this->render('block/block', [
                'parcels' => $parcels,
            ]);

            return;
        }

        
        try {
            $blockData = $this->getRequest()->post('block');

            $blockData['account_id'] = User::getInstance()->getId();

            $blockData = $this->validateBlockData($blockData);

            $parcel = (new Parcel())->load($blockData['parcel_id']);

            if (!$parcel->getId()) {
                throw new \InvalidArgumentException('Parcel not found');
            }

            if ($parcel->isBlockLimitReached()) {
                throw new \InvalidArgumentException('Blocks limit for the parcel is reached');
            }
        
            (new Block())
                //->setData($blockData)
                //add account_id to block data (temporary solution)
                ->create(['account_id' => $parcel->getAccountId()] + $blockData);

            $this->getRequest()->addInfo(
                'Block '.$blockData['name'].' has been created'
            );

        
        } catch (\Throwable $e) {
            $this->getRequest()->addError($e->getMessage());
            $this->redirectReferer();
            return;
        }

        $this->redirectReferer();
    }

    public function edit(): void
    {

        try {
            $pid = (int)$this->getRequest('id', 0);
        
            $block = (new Block())->load($pid);

            if (!$block->getId()) {
                throw new \Exception('Block not found');
            }

            $parcel = $block->getParcel();

            if (!$parcel->getId()) {
                throw new \Exception('Parcel not found');
            }

            if (User::getInstance()->getId() !== $parcel->getAccountId()) {
                throw new \Exception('You do not have permission to edit this block');
            }

            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->post('block', []);

                $block
                    ->setData($data)
                    ->save();

                $this->getRequest()->addInfo('The Block has been updated');
                $this->redirectReferer();
                exit;
            }

            
        } catch (\Throwable $e) {
            $this->getRequest()->addError($e->getMessage());
            $this->redirectReferer();
            exit;
        }

        $this->render('block/block', [
            'blockModel' => $block,
        ]);
    }


    public function options(): void
    {
       
    }

    protected function validateBlockData(array $data): array
    {
        if (empty($data['parcel_id'])) {
            throw new \InvalidArgumentException('Parcel ID is required');
        }

        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Block name is required');
        }

        if (empty($data['acres'])) {
            throw new \InvalidArgumentException('Block acres is required');
        }

        if (!is_numeric($data['acres'])) {
            throw new \InvalidArgumentException('Block acres must be a number');
        }

        if ($data['acres'] <= 0) {
            throw new \InvalidArgumentException('Block acres must be greater than zero');
        }

        $data['longitude'] = (float)($data['longitude'] ?? 0);
        $data['latitude'] = (float)($data['latitude'] ?? 0);

        return $data;
    }


}
