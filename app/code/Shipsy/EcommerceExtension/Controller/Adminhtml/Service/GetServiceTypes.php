<?php
declare(strict_types=1);

namespace Shipsy\EcommerceExtension\Controller\Adminhtml\Service;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Shipsy\EcommerceExtension\Helper\Data;

class GetServiceTypes extends Action
{
    const ADMIN_RESOURCE = 'Shipsy_EcommerceExtension::config'; // adjust ACL if needed

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    public function __construct(
        Context $context,
        Data $helper,
        ResourceConnection $resource,
        CookieManagerInterface $cookieManager,
        ScopeConfigInterface $scopeConfig,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->resource = $resource;
        $this->cookieManager = $cookieManager;
        $this->scopeConfig = $scopeConfig;
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();

        try {
            // Build request headers (same as your block)
            $organisationId = $this->scopeConfig->getValue('configuration/services/organisation_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $baseUrl = $this->helper->getBaseUrl($this->scopeConfig, $organisationId);

            $headers = [
                'Content-Type:application/json',
                'organisation-id:' . $organisationId,
                'shop-origin:magento',
                'shop-url:' . $this->_url->getBaseUrl(),
                'customer-id:' . $this->cookieManager->getCookie('customer-id'),
                'access-token:' . $this->cookieManager->getCookie('access-token-shipsy')
            ];

            // call getshopdata endpoint
            $ch = curl_init($baseUrl . '/api/ecommerce/getshopdata');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, []);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $responseRaw = curl_exec($ch);
            $curlErr = curl_error($ch);
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($curlErr) {
                throw new LocalizedException(__('cURL error: %1', $curlErr));
            }

            $response = json_decode($responseRaw, true);
            if (!is_array($response)) {
                throw new LocalizedException(__('Invalid JSON from remote API. HTTP status: %1', $httpStatus));
            }

            if (!isset($response['data']) || !isset($response['data']['serviceTypes'])) {
                $err = $response['error']['message'] ?? 'No serviceTypes in response';
                throw new LocalizedException(__($err));
            }

            $serviceTypes = $response['data']['serviceTypes'];
            if (!is_array($serviceTypes) || empty($serviceTypes)) {
                throw new LocalizedException(__('No service types returned'));
            }

            // prepare DB
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('service_types');

			$rowsInserted = 0;
			$now = (new \DateTime())->format('Y-m-d H:i:s');

			$insertData = [];
			foreach ($serviceTypes as $st) {
				$serviceCode = isset($st['id']) ? (string)$st['id'] : (string)($st['code'] ?? '');
				$serviceName = isset($st['name']) ? (string)$st['name'] : (string)($st['title'] ?? $serviceCode);

				if ($serviceCode === '') {
					continue;
				}

				$insertData[] = [
					'service_code' => $serviceCode,
					'name' => $serviceName,
					'active' => isset($st['active']) ? (int)$st['active'] : 0,
					'is_default' => isset($st['default']) ? (int)$st['default'] : 0,
					'created_at' => $now,
					'updated_at' => $now
				];
			}

			if (empty($insertData)) {
				throw new LocalizedException(__('No valid service types to insert'));
			}

			// Truncate the table safely before inserting
			$connection->truncateTable($tableName);

			//Now insert all rows
			$connection->insertMultiple($tableName, $insertData);

			$rowsInserted = count($insertData);


            if (empty($insertData)) {
                throw new LocalizedException(__('No valid service types to insert'));
            }

            // Use insertOnDuplicate to upsert rows (update name, active, is_default, updated_at)
            $updateCols = ['name', 'active', 'is_default', 'updated_at'];
            $connection->insertOnDuplicate($tableName, $insertData, $updateCols);
            $rowsInserted = count($insertData);

            return $resultJson->setData([
                'success' => true,
                'message' => __('%1 service types inserted/updated', $rowsInserted),
                'inserted' => $rowsInserted,
                'data' => $insertData
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage(),
                'raw' => $responseRaw ?? null
            ]);
        }
    }
}
