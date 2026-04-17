<?php
namespace Retailinsights\CourierAvailability\Controller\Adminhtml\Courier;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Retailinsights\CourierAvailability\Helper\Data as CourierHelper;
use Retailinsights\CourierAvailability\Model\CourierFactory;

class DelhiveryPincode extends Action
{
    const ADMIN_RESOURCE = 'Retailinsights_CourierAvailability::courier';

    /**
     * @var CourierHelper
     */
    protected $helper;

    /**
     * @var CourierFactory
     */
    protected $courierFactory;

    public function __construct(
        Action\Context $context,
        CourierHelper $helper,
        CourierFactory $courierFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->courierFactory = $courierFactory;
    }

    public function execute()
    {
        try {
            $apiUrl = $this->helper->getApiUrl('fetchPIN');
            $token  = trim($this->helper->getScopeConfig('delhivery_lastmile/general/license_key'));

            if (!$apiUrl || !$token) {
                $this->messageManager->addErrorMessage(__('Delhivery API URL or Token missing.'));
                return $this->_redirect('*/*/index');
            }

            $endpoint = $apiUrl . 'json/?token=' . $token . '&pre-paid=Y';
            $response = $this->helper->executeCurl($endpoint, '', '');
            $codes = json_decode($response);

            if (!$codes || !isset($codes->delivery_codes)) {
                $this->messageManager->addErrorMessage(__('Invalid or empty API response.'));
                return $this->_redirect('*/*/index');
            }

            // Get DB connection
            $model = $this->courierFactory->create();
            $connection = $model->getResource()->getConnection();
            $tableName  = $model->getResource()->getMainTable();

            // Clear table before inserting
            try {
                $connection->truncateTable($tableName);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error clearing table: %1', $e->getMessage()));
                return $this->_redirect('*/*/index');
            }

            $insertData = [];
            foreach ($codes->delivery_codes as $item) {
                if (!isset($item->postal_code->pin)) {
                    continue;
                }

                $insertData[] = [
                    'courier_name' => 'Delhivery',
                    'pincode'      => $item->postal_code->pin,
                    'is_available' => ($item->postal_code->pre_paid == "Y") ? 1 : 0,
                    'created_at'   => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s')
                ];
            }

            if (!empty($insertData)) {
                $connection->insertMultiple($tableName, $insertData);
                $this->messageManager->addSuccessMessage(__('Delhivery Pincodes downloaded successfully (%1 rows).', count($insertData)));
            } else {
                $this->messageManager->addWarningMessage(__('No pincodes found to import.'));
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error while fetching pincodes: %1', $e->getMessage()));
        }

        return $this->_redirect('*/*/index');
    }
}
