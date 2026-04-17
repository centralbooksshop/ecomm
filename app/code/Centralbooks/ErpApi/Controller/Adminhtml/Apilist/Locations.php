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
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Message\ManagerInterface;

class Locations implements HttpGetActionInterface
{
    protected $resultPageFactory;
    protected $serializer;
    protected $logger;
    protected $http;
    protected $helper;
    protected $scopeConfig;
    protected $storeManager;
    protected $priceHelper;
    private $sourceRepository;
    private $resource;
    protected $_messageManager;
    protected $resultRedirectFactory;

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

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            if ($this->helper->erpEnable()) {
                $locations_api_key = 'LocationsEcomm';
                $erp_base_apiUrl = $this->helper->getErpApiURL();
                $apiUrl = $erp_base_apiUrl . $locations_api_key;
                $apiresdata = $this->helper->apiCall($locations_api_key, $apiUrl);
				//echo '<pre>';
				//print_r($apiresdata);
				//die('--- END OF ERP API RESPONSE ---');

                if (isset($apiresdata)) {
                    $locations_arrays = json_decode($apiresdata, true);

                    if (empty($locations_arrays)) {
                        $this->_messageManager->addErrorMessage($locations_api_key . ' API returned empty response');
                        $resultRedirect = $this->resultRedirectFactory->create();
                        return $resultRedirect->setRefererOrBaseUrl();
                    }

                    $locations_records = $locations_arrays['value'] ?? [];

                    if (!empty($locations_records)) {
                        $connection = $this->resource->getConnection();
                        $tableName = $this->resource->getTableName('centralbooks_locationcode_locationcode');

                        // Step 1: Truncate table before inserting new data
                        try {
                            $connection->truncateTable($tableName);
                            $this->logger->info('Table truncated: ' . $tableName);
                        } catch (\Exception $e) {
                            $this->logger->error('Error truncating table: ' . $e->getMessage());
                            $this->_messageManager->addErrorMessage('Error truncating table before inserting new records.');
                        }

                        // Step 2: Insert new data from API
                        $count = 0;
                        foreach ($locations_records as $locations_records_value) {
                            $blocked = $locations_records_value['blocked'] ?? 0;
                            $locations_code = $locations_records_value['code'] ?? '';
                            $locations_displayName = $locations_records_value['displayName'] ?? '';
                            $locationType = $locations_records_value['cbsLocationType'] ?? '';
                            $locations_ModifiedDate = $locations_records_value['lastModifiedDateTime'] ?? date('Y-m-d H:i:s');

                            $locations_recordsUpdated = [
                                'location_code' => $locations_code,
                                'location_name' => $locations_displayName,
                                'location_status' => $blocked,
                                'created_at' => $locations_ModifiedDate
                            ];

                            $connection->insert($tableName, $locations_recordsUpdated);
                            $count++;
                        }

                        $successMessage = $count . ' record(s) inserted successfully after truncating the table!';
                        $this->_messageManager->addSuccessMessage($successMessage);
                    } else {
                        $this->_messageManager->addErrorMessage('No records found in API response.');
                    }
                } else {
                    $this->_messageManager->addErrorMessage('Failed to fetch data from ERP API.');
                }
            } else {
                $this->_messageManager->addErrorMessage('ERP API is disabled.');
            }

            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setRefererOrBaseUrl();

        } catch (\Exception $e) {
            $this->logger->error('Error in Locations API sync: ' . $e->getMessage());
            $this->_messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setRefererOrBaseUrl();
        }
    }
}
