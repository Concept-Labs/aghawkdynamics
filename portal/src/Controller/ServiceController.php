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
     * View a specific service request in read-only mode.
     *
     * @return void
     */
    public function view()
    {
        Registry::set('service_request_readonly', true);

        $this->forward('service','request');
            
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
            $serviceModel = (new ServiceRequest())
                ->load((int)$requestId);

            if (!$serviceModel->getId()) {
                throw new \InvalidArgumentException('Service request not found.');
            }

            if (!User::isAdmin() && $serviceModel->getAccountId() !== User::uid()) {
                throw new \InvalidArgumentException('You do not have permission to access this service request.');
            }

            $this->render(
                'service/request', 
                [
                    'serviceModel' => $serviceModel
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

        $this->render('service/details', ['serviceModel' => $serviceRequest]);
        //$this->render('service/request', ['serviceModel' => $serviceRequest]);
    }

    /*
     * @return void
     */
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

            $this->render('service/complete_details', ['serviceModel' => $serviceRequest]);

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
     * Uncomplete a completed service request.
     *
     * @return void
     */
    public function uncomplete(): void
    {
        try {
            if (!User::isAdmin()) {
                throw new \InvalidArgumentException('You do not have permission to uncomplete this service request.');
            }

            $requestId = (int)$this->getRequest()->request('id', 0);

            if (!$requestId) {
                throw new \InvalidArgumentException('Invalid service request ID.');
            }

            $serviceRequest = (new ServiceRequest())->load($requestId);

            if (!$serviceRequest->getId()) {
                throw new \InvalidArgumentException('Service request not found.');
            }

            if ($serviceRequest->isCompleted()) {
                $serviceRequest->setStatus(ServiceRequest::STATUS_PENDING);
                $serviceRequest->setCompleteData([]);
                $serviceRequest->save();

                $this->getRequest()->addMessage('Service request has been restored to pending status successfully.');
            } else {
                throw new \InvalidArgumentException('This service request is not completed.');
            }
        } catch (\Throwable $e) {
            $this->getRequest()->addError(
                'An error occurred while processing your request: ' . $e->getMessage()
            );
        }

        $this->redirectReferer();
    }

    /**
     * Upload an attachment to a service request.
     *
     * @return void
     */
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
     * Export the list of parcels as a CSV file.
     *
     * @return void
     */
     public function export(): void
    {
        $activityCollection = (new ServiceRequest())->getCollection();

        $rawSql = sprintf(
            'SELECT activity.*, 
                a.name AS account_name,
                p.id AS parcel_id,
                p.name AS parcel_name,
                b.name AS block_name
                

            FROM %s activity
            LEFT JOIN %s a ON activity.account_id = a.id
            LEFT JOIN %s b ON activity.block_id = b.id
            LEFT JOIN %s p ON activity.parcel_id = p.id
            ',
            (new ServiceRequest())->getTable(),
            (new Account())->getTable(),
            (new Block())->getTable(),
            (new Parcel())->getTable()
        );

        if (!User::isAdmin()) {
            // If the user is not an admin, filter blocks by account ID
            $rawSql .= ' WHERE p.account_id = ' . User::uid();
        }

        $activityCollection->setRawSql($rawSql);
        $activityCollection->setItemMode(Collection::ITEM_MODE_ARRAY);
        $activityCollection->sort('created_at', 'DESC');


        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="parcels.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $output = fopen('php://output', 'w');
        $separator = ';';
        fputcsv($output, [
            'Activity ID', 
            'Status', 
            'Kind', 
            'Crop Type',
            'Account', 
            'Parcel UID', 
            'Parcel Nickname', 
            'Block Nickname', 
            'Reason', 
            'Is Urgent', 
            'Need by Date',
            'Products',
            "Supplier",
            "Application rate"
        ], $separator);

        foreach ($activityCollection as $activity) {
            $adds = json_decode($activity['adds'], true);

            $applicationRate = $adds['application']['volume'] ? "{$adds['application']['volume']} ({$adds['application']['unit']})" : '';

            $products = [];
            foreach ($adds['products'] ?? [] as $product) {
                if (empty($product['type']) || empty($product['name'])) {
                    continue; // Skip products with missing type or name
                }
                $productType = $product['type'] ?? '';
                $productName = $product['name'] ?? '';
                $productVolume = $product['volume'] ?? '';
                $productUnit = $product['unit'] ?? '';
                $products[] = "[$productType] $productName ($productVolume $productUnit)";
            }

            $productsStr = implode(', ', $products ?? []);

            $supplierName = $adds['supplier'] ?? '';
            $supplierPhone = $adds['supplier_phone'] ?? '';
            $supplierContact = $adds['supplier_name'] ?? '';

            $supplierStr = $supplierName ? "$supplierName ($supplierContact, $supplierPhone)" : '';

            fputcsv($output, [
                $activity['id'],
                $activity['status'],
                $activity['kind'],
                $activity['type'],
                $activity['account_name'],
                $activity['parcel_id'],
                $activity['parcel_name'],
                $activity['block_name'],
                $activity['reason'] ?? '',
                $activity['urgent'] ? 'Yes' : 'No',
                $activity['date'] ? (new \DateTimeImmutable($activity['date']))->format('m/d/Y') : '',
                $productsStr,
                $supplierStr,
                $applicationRate

            ], $separator);
        }
        fclose($output);
        exit;

    }

}
