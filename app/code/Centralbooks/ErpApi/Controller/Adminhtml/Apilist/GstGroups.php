<?php
declare(strict_types=1);

namespace Centralbooks\ErpApi\Controller\Adminhtml\Apilist;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Centralbooks\ErpApi\Helper\Data;
use Centralbooks\ErpApi\Model\GstFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\App\Action\Context;

class GstGroups implements HttpGetActionInterface
{
    protected $resultPageFactory;
    protected $serializer;
    protected $logger;
    protected $http;
    protected $helper;
    protected $scopeConfig;
    protected $storeManager;
    protected $priceHelper;
    protected $gstFactory;
    protected $resultRedirectFactory;
    protected $_messageManager;

    public function __construct(
        PageFactory $resultPageFactory,
        Json $json,
        LoggerInterface $logger,
        Context $context,
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        Data $data,
        StoreManagerInterface $storeManager,
        PriceHelper $priceHelper,
        GstFactory $gstFactory,
        Http $http
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->serializer = $json;
        $this->logger = $logger;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->_messageManager = $messageManager;
        $this->helper = $data;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        $this->gstFactory = $gstFactory;
        $this->http = $http;
    }

    public function execute()
    {
        try {
            if (!$this->helper->erpEnable()) {
                throw new LocalizedException(__('ERP integration is disabled.'));
            }

            $gstGroups_api_key = 'gstGroups';
            $erp_base_apiUrl = $this->helper->getErpApiURL();
            $apiUrl = $erp_base_apiUrl . $gstGroups_api_key;

            // Call ERP API
            $apiresdata = $this->helper->apiCall($gstGroups_api_key, $apiUrl);

            // Print API response for debug
            //echo '<pre>';
            //print_r($apiresdata);
            //die('--- END OF ERP API RESPONSE ---');

            if (!$apiresdata) {
                $this->_messageManager->addErrorMessage(__('No response from ERP API.'));
                return $this->resultRedirectFactory->create()->setRefererOrBaseUrl();
            }

            $erp_gstarrays = json_decode($apiresdata, true);

            if (empty($erp_gstarrays['value'])) {
                $this->_messageManager->addErrorMessage(__('No GST data found in ERP API response.'));
                return $this->resultRedirectFactory->create()->setRefererOrBaseUrl();
            }

            $gst_records = $erp_gstarrays['value'];

            // Get DB connection
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $erp_gst_table = $resource->getTableName('erp_gst');

            // Truncate table before insert
            $connection->truncateTable($erp_gst_table);

            $count = 0;

            foreach ($gst_records as $record) {
                $api_gst_id = $record['id'] ?? '';
                $gst_code_value = $record['code'] ?? '';

                $gst_code = ($gst_code_value === 'EXEMPTED') ? 'GST0' : $gst_code_value;
                $res_gst_code = ltrim($gst_code, 'GST');

                $recordsUpdated = [
                    'apiid' => $api_gst_id,
                    'code' => $res_gst_code,
                    'display_name' => $record['displayName'] ?? '',
                    'gst_group_type' => $record['gstGroupType'] ?? '',
                    'last_modified_datetime' => $record['lastModifiedDateTime'] ?? '',
                ];

                if (!empty($recordsUpdated)) {
                    $gstModel = $this->gstFactory->create();
                    $gstModel->setData($recordsUpdated);
                    $gstModel->save();
                    $count++;
                }
            }

            $this->_messageManager->addSuccessMessage(__('%1 GST records inserted successfully.', $count));

        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            $this->_messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setRefererOrBaseUrl();
    }
}
