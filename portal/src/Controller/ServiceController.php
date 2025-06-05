<?php
namespace App\Controller;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Model\Collection;
use App\Core\Registry;
use App\Model\Account;
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
    
        $filters = $this->getRequest()->request('filters', []);

        $requestCollection = (new ServiceRequest())->getCollection()
            ->setItemMode(Collection::ITEM_MODE_OBJECT)
            ->join(
                Parcel::TABLE,
                sprintf(
                    'main.parcel_id = %s.id',
                    Parcel::TABLE
                )
            )
            ->join(
                Block::TABLE,
                sprintf(
                    'main.block_id = %s.id',
                    Block::TABLE
                )
            )
            ->join(
                Account::TABLE,
                sprintf(
                    'main.account_id = %s.id',
                    Account::TABLE
                )
            )
            ->sort('created_at', 'DESC');
        
        if (!User::isAdmin()) {
            $requestCollection->addFilter(['main.account_id' => User::uid()]);
        }

        $requestCollection->applyPostFilters(
            $filters
        );

        $requestCollection->setPage((int)$this->getRequest('page', 1));

        $this->render('service/list', 
            [
                'requestCollection' => $requestCollection,
                'filters' => $filters,
            ]
        );
    }

    public function view()
    {
        Registry::set('service_request_readonly', true);

        $this->forward('service','request');
            
    }

    public function selftrack(): void
    {
        Registry::set('service_kind', ServiceRequest::KIND_SELF_TRACKING);

        $this->forward('service', 'request');
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
                    'readonly' => Registry::get('service_request_readonly', false),
                    'kind' => Registry::get('service_kind') ?? $this->getRequest('kind', ServiceRequest::KIND_REQUEST),
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

    /**
     * Display the edit form for a service request.
     *
     * @return void
     */
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

    public function complete_details()
    {
        $requestId = (int)$this->getRequest()->request('id', 0);

        try {
            $serviceRequest = (new ServiceRequest())->load($requestId);

            if (!$serviceRequest->getId()) {
                throw new \InvalidArgumentException('Service request not found.');
            }

            if (!User::isAdmin() && $serviceRequest->getAccount()->getId() !== User::getInstance()->getId()) {
                throw new \InvalidArgumentException('You do not have permission to view this service request.');
            }

            $this->render('service/complete_details', ['requestModel' => $serviceRequest]);

        } catch (\Throwable $e) {
            $this->getRequest()->addError(
                'An error occurred while processing your request: ' . $e->getMessage()
            );
        }

        $this->redirectReferer();
    }

    /**
     * View details of a specific service request.
     *
     * @return void
     */
    public function cancel(): void
    {
        $requestId = (int)$this->getRequest()->request('id', 0);
        $reason = $this->getRequest()->request('reason', '');

        

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

    public function uploadAttachment(): void
    {
        try {
            // if (!User::isAdmin() ){
            //     throw new \RuntimeException('Access denied. Only admins can upload attachments.');
            // }

            $serviceId = (int)$this->getRequest()->request('service_id', 0);
            $service = (new ServiceRequest())->load($serviceId);

            $comment = $this->getRequest()->request('comment', '');

            if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
                throw new \RuntimeException('File upload error: ' . $_FILES['attachment']['error']);
            }

            $file = $_FILES['attachment'];

            if (!$service->getId()) {
                throw new \RuntimeException('Service not found');
            }

            if (!$this->getRequest()->isPost()) {
                throw new \RuntimeException('Invalid request method. Please use POST to upload files.');
            }
            
            if (empty($file['name'])) {
                throw new \RuntimeException('No file uploaded');
            }
            $allowedTypes = Config::get('upload_types');
            if (!in_array($file['type'], $allowedTypes)) {
                throw new \RuntimeException('Invalid file type. Allowed types: ' . implode(', ', $allowedTypes));
            }
            if ($file['size'] > Config::get('max_upload_size')) {
                throw new \RuntimeException('File size exceeds the maximum limit');
            }


            $uploadRelDir = 
                 Config::get('upload_dir') 
                . DIRECTORY_SEPARATOR
                .'attachments' 
                . DIRECTORY_SEPARATOR 
                . 'service_' . $service->getId();

            if (!is_dir($uploadRelDir) && !mkdir($uploadRelDir, 0755, true)) {
                throw new \RuntimeException('Failed to create upload directory: ' . $uploadRelDir);
            }

            $filePath = 
                getcwd() . DIRECTORY_SEPARATOR . $uploadRelDir . DIRECTORY_SEPARATOR . basename($file['name']);

            $relFilePath = 
                $uploadRelDir . DIRECTORY_SEPARATOR . basename($file['name']);

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new \RuntimeException('Failed to move uploaded file to ' . $filePath);
            }
            // Save the attachment information to the block
            $attachmentData = [
                'path' => $relFilePath,
                'comment' => $comment
            ];


            $service->addAttachment($attachmentData);

            $this->getRequest()->addMessage(
                'File uploaded successfully: ' . basename($file['name'])
            );

            echo json_encode([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'data' => $attachmentData
            ]);
            
        } catch (\Throwable $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
            return;
        }
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

        if ((int)$data['account_id'] !== (int)User::uid() && !User::isAdmin()) {
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
