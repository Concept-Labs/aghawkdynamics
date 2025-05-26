<?php
namespace App\Controller;

use App\Core\Controller;
use App\Core\Model\Collection;
use App\Model\Account\User;
use App\Model\Parcel;
use App\Model\ServiceRequest;

class ServiceController extends Controller
{

    /**
     * ServiceController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        //$this->setTitle('Service Requests');
    }

    /**
     * Display the list of service requests for the logged-in user.
     *
     * @return void
     */
    public function index(): void
    {
        $uid = User::getInstance()->getId();
        $RequestCollection = (new ServiceRequest())->getCollection();
        $RequestCollection->setItemMode(Collection::ITEM_MODE_OBJECT);
        $RequestCollection->addFilter(
            [
                'account_id' => $uid,
            ]
        );
        $RequestCollection->sort('created_at', 'DESC');

        $this->render('service/list', ['requestCollection' => $RequestCollection]);
    }

    /**
     * Add a new service request.
     *
     * @return void
     */
    public function request(): void
    {
        try {
            $parcels = (new Parcel())
                ->getCollection()
                ->setItemMode(Collection::ITEM_MODE_OBJECT)
                //->join('block', 'block.parcel_id = main.id')
                ->addFilter(
                    [
                        'main.account_id' => User::getInstance()->getId(),
                        
                    ]
                )
                //->groupBy('main.id')
                ->sort('created_at', 'DESC');

                $currentParcelId = $this->getRequest()->request('parcel', null);
                if ($currentParcelId) {
                    $parcels->addFilter(['main.id' => $currentParcelId]);
                    $blocks = (new Parcel())
                        ->load($currentParcelId)
                        ->getBlocks()
                        ->setItemMode(Collection::ITEM_MODE_ARRAY)
                        ->sort('name', 'ASC');
                }

        } catch (\Throwable $e) {
            $this->getRequest()->addError(
                'An error occurred while processing your request: ' . $e->getMessage()
            );
            $this->redirectReferer();
            return;
        }

        $this->render('service/request', 
            [
                'parcels' => $parcels,
                'blocks' => $blocks ?? [],
                'parcel_id' => $this->getRequest()->request('parcel', null),
                'block_id' => $this->getRequest()->request('block', null),
            ]
        );
    }

    /**
     * Create a new service request.
     *
     * @return void
     */
    public function create(): void
    {
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getPost('service');

                $data['account_id'] = User::getInstance()->getId();
                $data['status'] = ServiceRequest::STATUS_PENDING;


                $this->validateServiceData($data);

                $data['adds'] = json_encode($data['adds'] ?? []);

                (new ServiceRequest())
                    ->create($data);

                $this->getRequest()->addMessage('Your service request has been submitted successfully.');
                $this->redirect('/?q=service/index');
                return;

            } catch (\Throwable $e) {
                $this->getRequest()->addError(
                    'An error occurred while processing your request: ' . $e->getMessage()
                );
                $this->redirectReferer();
            }

        } 

        $this->getRequest()->addError('Invalid request method. Please submit the form.');
        $this->redirectReferer();
    }

    public function details()
    {
        $requestId = (int)$this->getRequest()->request('id', 0);
        if (!$requestId) {
            $this->getRequest()->addError('Invalid service request ID.');
            $this->redirectReferer();
            return;
        }

        $serviceRequest = (new ServiceRequest())->load($requestId);
        if (!$serviceRequest->getId()) {
            $this->getRequest()->addError('Service request not found.');
            $this->redirectReferer();
            return;
        }

        if ($serviceRequest->getAccount()->getId() !== User::getInstance()->getId()) {
            $this->getRequest()->addError('You do not have permission to view this service request.');
            $this->redirectReferer();
            return;
        }

        $this->render('service/details', ['requestModel' => $serviceRequest]);
    }

    /**
     * Validate service request data.
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    protected function validateServiceData(array $data): void
    {
        //@todo validation
    }
    

}
