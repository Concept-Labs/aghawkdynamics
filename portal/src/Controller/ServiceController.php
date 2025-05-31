<?php
namespace App\Controller;

use App\Core\Controller;
use App\Core\Model\Collection;
use App\Model\Account\User;
use App\Model\Block;
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
        
        if (!User::isAdmin()) {
            $RequestCollection->addFilter(['account_id' => $uid]);
        }
        
        $RequestCollection->sort('created_at', 'DESC');

        $this->render('service/list', ['requestCollection' => $RequestCollection]);
    }

    /**
     * Handle the service request form submission.
     *
     * @return void
     */
    public function request(): void
    {
        if ($this->getRequest()->isPost()) {
            $this->handleRequestPost();
            return;
        }

        try {

            if ($this->getRequest()->request('id')) {
                $this->editForm();
                return;
            }

            $forParcelId = $this->getRequest('parcel', null);
            $forBlockId = $this->getRequest('block', null);

            if ($forBlockId) {
                //exactly for block (so for parcel as well)
                $blockModel = (new Block())->load((int)$forBlockId);
                if (!$blockModel->getId() || 
                    (!User::isAdmin() && $blockModel->getAccountId() !== User::uid())) {
                    throw new \InvalidArgumentException('Block not found or you do not have permission to access it.');
                }

                $parcelModel = $blockModel->getParcel();
            } elseif ($forParcelId) {
                //exactly for parcel
                $parcelModel = (new Parcel())->load((int)$forParcelId);
                if (!$parcelModel->getId() || 
                    (!User::isAdmin() && $parcelModel->getAccountId() !== User::uid())) {
                    throw new \InvalidArgumentException('Parcel not found or you do not have permission to access it.');
                }
                $blockCollection = $parcelModel->getBlocks();
                
            } else {
                // No specific parcel or block provided, show all parcels for the user
                $parcelCollection = (new Parcel())
                    ->getCollection()
                    ->setItemMode(Collection::ITEM_MODE_OBJECT)
                    ->sort('created_at', 'DESC');

                if (!User::isAdmin()) {
                    // If the user is not an admin, filter parcels by account ID
                    $parcelCollection->addFilter(['main.account_id' => User::uid()]);
                } elseif ($this->getRequest()->request('account_id')) {
                    // If an account ID is provided, filter by that
                    $parcelCollection->addFilter(['main.account_id' => (int)$this->getRequest()->request('account_id')]);
                }

                $parcelCollection->sort('name', 'ASC');
            }

            $this->render(
                'service/request', 
                [
                    'parcelModel' => $parcelModel ?? null,
                    'blockModel' => $blockModel ?? null,
                    'parcelCollection' => $parcelCollection,
                    'blockCollection' => $blockCollection ?? null,
                ]
            );

        } catch (\Throwable $e) {
            $this->getRequest()->addError(
                'An error occurred while processing your request: ' . $e->getMessage()
            );
            $this->redirectReferer();
        }

        
    }

    protected function editForm()
    {
        try {
            $requestId = $this->getRequest()->request('id') ?? 0;
            $requestModel = (new ServiceRequest())
                ->load((int)$requestId);

            if (!$requestModel->getId()) {
                throw new \InvalidArgumentException('Service request not found.');
            }

            if (!User::isAdmin() && $requestModel->getAccountId() !== User::uid()) {
                throw new \InvalidArgumentException('You do not have permission to access this service request.');
            }

            $this->render(
                'service/request', 
                [
                    'requestModel' => $requestModel
                ]
            );
        } catch (\Throwable $e) {
            $this->getRequest()->addError(
                'An error occurred while processing your request: ' . $e->getMessage()
            );
            
        } finally {
            $this->redirectReferer();
        }
    }

    /**
     * Create a new service request.
     *
     * @return void
     */
    public function handleRequestPost(): void
    {
        if (!$this->getRequest()->isPost()) {
            $this->getRequest()->addError('Invalid request method. Please submit the form.');
            $this->redirectReferer();
            return;
        }

        try {
            $data = $this->getRequest()->getPost('service');

            $data['account_id'] ??= User::getInstance()->getId();
            $data['status'] ??= ServiceRequest::STATUS_PENDING;
            $data['adds'] = json_encode($data['adds'] ?? []);

            $this->validateServiceData($data);

            $serviceRequest = (new ServiceRequest());
            $serviceRequest
                ->setData($data)
                ->save();

            $this->getRequest()->addMessage('Service Request has been submitted successfully.');
            $this->redirect('/?q=service/index');
            return;

        } catch (\Throwable $e) {
            $this->getRequest()->addError(
                'An error occurred while processing your request: ' . $e->getMessage()
            );
            $this->redirectReferer();
        }
        
    }
    

    

    /**
     * View details of a specific service request.
     *
     * @return void
     */
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

        if (!User::isAdmin() && $serviceRequest->getAccount()->getId() !== User::getInstance()->getId()) {
            $this->getRequest()->addError('You do not have permission to view this service request.');
            $this->redirectReferer();
            return;
        }

        $this->render('service/details', ['requestModel' => $serviceRequest]);
        //$this->render('service/request', ['requestModel' => $serviceRequest]);
    }

    /**
     * View details of a specific service request.
     *
     * @return void
     */
    public function cancel(): void
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

        if (!User::isAdmin() && $serviceRequest->getAccount()->getId() !== User::getInstance()->getId()) {
            $this->getRequest()->addError('You do not have permission to cancel this service request.');
            $this->redirectReferer();
            return;
        }

        if ($serviceRequest->canCancel()) {
            $serviceRequest->setStatus(ServiceRequest::STATUS_CANCELLED);
            $serviceRequest->save();
            $this->getRequest()->addMessage('Service request has been cancelled successfully.');
        } else {
            $this->getRequest()->addError('This service request cannot be cancelled.');
        }

        $this->redirectReferer();
    }

    /**
     * Uncancel a cancelled service request.
     *
     * @return void
     */
    public function uncancel(): void
    {
        try {
            $requestId = (int)$this->getRequest()->request('id', 0);
            if (!$requestId) {
                throw new \InvalidArgumentException('Invalid service request ID.');
            }

            $serviceRequest = (new ServiceRequest())->load($requestId);
            if (!$serviceRequest->getId()) {
                throw new \InvalidArgumentException('Service request not found.');
            }

            if (!User::isAdmin() && $serviceRequest->getAccount()->getId() !== User::getInstance()->getId()) {
                throw new \InvalidArgumentException('You do not have permission to uncancel this service request.');
            }

            if ($serviceRequest->isCancelled()) {

                $serviceRequest->setStatus(ServiceRequest::STATUS_PENDING);
                $serviceRequest->save();
                $this->getRequest()->addMessage('Service request has been restored successfully.');

            } else {
                throw new \InvalidArgumentException('This service request is not cancelled.');
            }
        } catch (\Throwable $e) {
            $this->getRequest()->addError(
                'An error occurred while processing your request: ' . $e->getMessage()
            );
        }

        $this->redirectReferer();
    }

    

    /**
     * Complete a service request.
     *
     * @return void
     */
    public function complete(): void
    {
        try {
            $requestId = (int)$this->getRequest()->request('id', 0);
            $completeData = $this->getRequest()->post('complete', []);
            if (!$requestId) {
                throw new \InvalidArgumentException('Invalid service request ID.');
            }

            $serviceRequest = (new ServiceRequest())->load($requestId);
            if (!$serviceRequest->getId()) {
                throw new \InvalidArgumentException('Service request not found.');
            }

            if (!User::isAdmin() && $serviceRequest->getAccount()->getId() !== User::getInstance()->getId()) {
                throw new \InvalidArgumentException('You do not have permission to complete this service request.');
            }

            if ($serviceRequest->canComplete()) {
                $serviceRequest->complete($completeData);

                $this->getRequest()->addMessage('Service request has been marked as completed successfully.');
            } else {
                throw new \InvalidArgumentException('This service request cannot be completed at this time.');
            }
        } catch (\Throwable $e) {
            $this->getRequest()->addError(
                'An error occurred while processing your request: ' . $e->getMessage()
            );
            $this->redirectReferer();
            return;
        }

        $this->redirectReferer();
    }


    /**
     * Validate service request data.
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    protected function validateServiceData(array $data): void
    {
        if (empty($data['account_id'])) {
            throw new \InvalidArgumentException('Account ID is required.');
        }

        if ($data['account_id'] !== User::getInstance()->getId() && !User::isAdmin()) {
            throw new \InvalidArgumentException('Permissions issue');
        }

        if (empty($data['parcel_id'])) {
            throw new \InvalidArgumentException('Parcel ID is required.');
        }
        if (empty($data['block_id'])) {
            throw new \InvalidArgumentException('Block ID is required.');
        }
        if (empty($data['account_id'])) {
            throw new \InvalidArgumentException('Account ID is required.');
        }
        if (empty($data['type'])) {
            throw new \InvalidArgumentException('Service type is required.');
        }

    }

}
