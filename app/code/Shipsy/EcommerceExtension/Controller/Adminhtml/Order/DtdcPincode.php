<?php
namespace Shipsy\EcommerceExtension\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class DtdcPincode extends \Magento\Backend\App\Action
{
    protected $cookieManager;
    protected $scopeConfig;
    protected $urlInterface;
    protected $resource;
    protected $logger;

    public function __construct(
        Context $context,
        CookieManagerInterface $cookieManager,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlInterface,
        ResourceConnection $resource,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->cookieManager = $cookieManager;
        $this->scopeConfig = $scopeConfig;
        $this->urlInterface = $urlInterface;
        $this->resource = $resource;
        $this->logger = $logger;
    }

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/dtdc_pincode.log');
        $log = new \Zend_Log();
        $log->addWriter($writer);
        $log->info('DTDC Pincode API Started');

        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            // Get configuration values
            $customerCode = $this->scopeConfig->getValue(
                'configuration/services/customer_code',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $authToken = $this->scopeConfig->getValue(
                'configuration/services/auth_token',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

			$pincode_api_url = $this->scopeConfig->getValue(
                'configuration/services/pincode_api_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if (!$customerCode || !$authToken) {
                $this->messageManager->addErrorMessage(__('DTDC API credentials not configured.'));
                return $resultRedirect->setRefererOrBaseUrl();
            }

            $apiUrl = $pincode_api_url . "={$customerCode}";

            $headers = [
                "Authorization: {$authToken}",
                "Content-Type: application/json"
            ];

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                $log->err("cURL Error: " . $error);
                $this->messageManager->addErrorMessage(__('Error calling DTDC API: ' . $error));
                return $resultRedirect->setRefererOrBaseUrl();
            }

            $data = json_decode($response, true);
            $log->info('API Response: ' . print_r($data, true));

            if (!isset($data['postal_codes']) || !is_array($data['postal_codes'])) {
                $this->messageManager->addErrorMessage(__('Invalid response from DTDC API.'));
                return $resultRedirect->setRefererOrBaseUrl();
            }

            // --- Insert into Database ---
            $connection = $this->resource->getConnection();
            $tableName = $connection->getTableName('retailinsights_courieravailability_courier');

            try {
                // Clear existing DTDC records
                $connection->delete($tableName, ['courier_name = ?' => 'DTDC']);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error clearing DTDC pincodes: %1', $e->getMessage()));
                return $resultRedirect->setRefererOrBaseUrl();
            }

            $insertData = [];
			$now = date('Y-m-d H:i:s');

			foreach ($data['postal_codes'] as $item) {
				// Insert only when area_serviceable == 'Y'
				if (isset($item['area_serviceable']) && strtoupper($item['area_serviceable']) === 'Y') {
					$insertData[] = [
						'courier_name' => 'DTDC',
						'pincode'      => $item['pincode'],
						'is_available' => 1,
						'created_at'   => $now,
						'updated_at'   => $now
					];
				}
			}

			// Bulk insert if data found
			if (!empty($insertData)) {
				$connection->insertMultiple($tableName, $insertData);
				$this->messageManager->addSuccessMessage(__('DTDC pincodes inserted: %1', count($insertData)));
			} else {
				$this->messageManager->addWarningMessage(__('No serviceable DTDC pincodes found (area_serviceable = Y).'));
			}


            return $resultRedirect->setRefererOrBaseUrl();

        } catch (\Exception $e) {
            $this->logger->error('DTDC Pincode Error: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('Error fetching DTDC pincodes: ' . $e->getMessage()));
            return $resultRedirect->setRefererOrBaseUrl();
        }
    }
}
