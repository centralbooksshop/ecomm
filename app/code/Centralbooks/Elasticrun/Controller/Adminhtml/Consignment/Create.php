<?php
namespace Centralbooks\Elasticrun\Controller\Adminhtml\Consignment;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Centralbooks\Elasticrun\Helper\Data as ElasticrunHelper;

class Create extends Action
{
    protected $helper;

    public function __construct(
        Action\Context $context,
        ElasticrunHelper $helper
    ) {
        parent::__construct($context);
        $this->helper = $helper;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            // Example payload – replace with dynamic order data
            $payload = [
                "data" => [
                    "consignor_name" => "Elasticrun Banashankari Warehouse",
                    "consignee_name" => "Rahul",
                    "origin_city" => "Bengaluru",
                    "destination_city" => "Bengaluru",
                    "is_first_mile_pickup" => 1,
                    "weight" => 72.5,
                    "payment_method" => "prepay",
                    "order_amount" => 1500,
                    "user_id" => $this->helper->getConfigValue('elasticrun/general/user_id'),
                    "org_id" => $this->helper->getConfigValue('elasticrun/general/org_id')
                ]
            ];

            $response = $this->helper->callElasticrunApi($payload);
            $this->messageManager->addSuccessMessage(__('Elasticrun API Response: ' . json_encode($response)));

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error: ' . $e->getMessage()));
        }

        return $resultRedirect->setPath('adminhtml/system_config/edit/section/elasticrun');
    }
}
