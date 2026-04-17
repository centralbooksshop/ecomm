<?php
declare(strict_types=1);

namespace Centralbooks\ErpApi\Controller\Adminhtml\Apilist;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Centralbooks\ErpApi\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Message\ManagerInterface;

class SchoolBranches implements HttpGetActionInterface
{
    protected $resultPageFactory;
    protected $serializer;
    protected $logger;
    protected $http;
    protected $helper;
    protected $scopeConfig;
    protected $storeManager;
    protected $priceHelper;
    protected $sourceRepository;
    protected $resource;
    protected $resultRedirectFactory;
    protected $_messageManager;

    public function __construct(
        PageFactory $resultPageFactory,
        ManagerInterface $messageManager,
        Json $json,
        LoggerInterface $logger,
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Data $data,
        StoreManagerInterface $storeManager,
        PriceHelper $priceHelper,
        SourceRepositoryInterface $sourceRepository,
        ResourceConnection $resource,
        Http $http
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_messageManager = $messageManager;
        $this->serializer = $json;
        $this->logger = $logger;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->helper = $data;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        $this->sourceRepository = $sourceRepository;
        $this->resource = $resource;
        $this->http = $http;
    }

    public function execute()
    {
        try {
            if (!$this->helper->erpEnable()) {
                throw new LocalizedException(__('ERP Integration is disabled.'));
            }

            $school_api_key = 'schoolBranchesEcomm';
            $erp_base_apiUrl = $this->helper->getErpApiURL();
            $apiUrl = $erp_base_apiUrl . $school_api_key;

            // Fetch data from ERP API
            $apiresdata = $this->helper->apiCall($school_api_key, $apiUrl);

            // Uncomment to debug API response
            // echo '<pre>'; print_r($apiresdata); die('--- END OF ERP API RESPONSE ---');

            if (empty($apiresdata)) {
                $this->_messageManager->addErrorMessage(__('Empty response from ERP API.'));
                return $this->resultRedirectFactory->create()->setRefererOrBaseUrl();
            }

            $school_arrays = json_decode($apiresdata, true);

            if (empty($school_arrays['value'])) {
                $this->_messageManager->addErrorMessage(__('No data found in ERP API response.'));
                return $this->resultRedirectFactory->create()->setRefererOrBaseUrl();
            }

            $school_records = $school_arrays['value'];

            // Get DB connection
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('centralbooks_schoolcode_schoolcode');

            // Truncate table before inserting
            $connection->truncateTable($tableName);

            $count = 0;

            foreach ($school_records as $record) {
                $blocked = 1;
                $school_code = $record['branchCode'] ?? '';
                $school_name = $record['branchName'] ?? '';
                $customer_no = $record['customerNo'] ?? '';
                $modified_date = $record['lastModifiedDateTime'] ?? date('Y-m-d H:i:s');

                $data = [
                    'school_code'   => $school_code,
                    'customer_no'   => $customer_no,
                    'school_name'   => $school_name,
                    'school_status' => $blocked,
                    'created_at'    => $modified_date
                ];

                // Insert each record
                if (!empty($school_code)) {
                    $connection->insert($tableName, $data);
                    $count++;
                }
            }

            $this->_messageManager->addSuccessMessage(__('%1 records inserted successfully!', $count));

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->_messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setRefererOrBaseUrl();
    }
}
