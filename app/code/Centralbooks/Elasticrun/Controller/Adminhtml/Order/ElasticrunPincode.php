<?php
namespace Centralbooks\Elasticrun\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class ElasticrunPincode extends Action
{
    protected $cookieManager;
    protected $scopeConfig;
    protected $urlInterface;
    protected $resource;
    protected $logger;
    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        CookieManagerInterface $cookieManager,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlInterface,
        ResourceConnection $resource,
        LoggerInterface $logger,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);

        $this->cookieManager = $cookieManager;
        $this->scopeConfig   = $scopeConfig;
        $this->urlInterface  = $urlInterface;
        $this->resource      = $resource;
        $this->logger        = $logger;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        $logWriter = new \Zend_Log_Writer_Stream(BP . '/var/log/elasticrun_pincode.log');
        $log = new \Zend_Log();
        $log->addWriter($logWriter);
        $log->info('ElasticRun Pincode API Started');

        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            // Fetch org_id from admin config
            $orgId = $this->scopeConfig->getValue(
                'elasticrun_configuration/general/org_id',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

			$pincode_api_url = $this->scopeConfig->getValue(
                'elasticrun_configuration/general/pincode_api_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

			$apiToken = trim($this->scopeConfig->getValue('elasticrun_configuration/general/api_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
			

            if (!$orgId) {
                $this->messageManager->addErrorMessage(__('ElasticRun org_id is not configured.'));
                return $resultRedirect->setRefererOrBaseUrl();
            }

            $apiUrl = $pincode_api_url . "?org_id={$orgId}";

            $log->info("Calling API: " . $apiUrl);
			
			$headers = [
                "Authorization: {$apiToken}",
                "Content-Type: application/json"
            ];

            // cURL request
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

			//echo '<pre>';print_r($response);die;

            if ($error) {
                $log->err("cURL Error: " . $error);
                $this->messageManager->addErrorMessage(__('ElasticRun API Error: ' . $error));
                return $resultRedirect->setRefererOrBaseUrl();
            }

            $data = json_decode($response, true);
            $log->info("API Response: " . print_r($data, true));

            // Validate response
            if (!isset($data['data']) || !is_array($data['data'])) {
                $this->messageManager->addErrorMessage(__('Invalid response from ElasticRun API.'));
                return $resultRedirect->setRefererOrBaseUrl();
            }

            $pincodes = $data['data'];

            // DB operations
            $connection = $this->resource->getConnection();
            $table = $connection->getTableName('retailinsights_courieravailability_courier');

            // Remove previous Elasticrun pincodes
            $connection->delete($table, ['courier_name = ?' => 'Elasticrun']);

            $insertData = [];
            $now = date('Y-m-d H:i:s');

            foreach ($pincodes as $pincode) {
                $insertData[] = [
                    'courier_name' => 'Elasticrun',
                    'pincode'      => $pincode,
                    'is_available' => 1,
                    'created_at'   => $now,
                    'updated_at'   => $now
                ];
            }

            if (!empty($insertData)) {
                $connection->insertMultiple($table, $insertData);
                $this->messageManager->addSuccessMessage(
                    __('ElasticRun pincodes saved: %1', count($insertData))
                );
            } else {
                $this->messageManager->addWarningMessage(__('No ElasticRun pincodes returned from API.'));
            }

            return $resultRedirect->setRefererOrBaseUrl();

        } catch (\Exception $e) {
            $this->logger->error('ElasticRun Error: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('ElasticRun Error: ' . $e->getMessage()));
            return $resultRedirect->setRefererOrBaseUrl();
        }
    }
}
