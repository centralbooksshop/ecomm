<?php

namespace Shipsy\EcommerceExtension\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Bulklabel extends \Magento\Framework\App\Action\Action
{
   
    protected $cookieManager;
    protected $orderRepository;
    protected $urlInterface;
    protected $resourceConnection;
    protected $scopeConfig;
    protected $searchCriteriaBuilder;
    protected $dataHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Shipsy\EcommerceExtension\Helper\Data $dataHelper,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        parent::__construct($context);
        $this->cookieManager = $cookieManager;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->urlInterface = $urlInterface;
        $this->resourceConnection = $resourceConnection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dataHelper = $dataHelper;
        $this->resultFactory = $resultFactory;
    }

    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected');
        $no_of_selection = count($selected);
        $consignmentReferenceNumberArray = [];
        if ($no_of_selection > 0) {
            foreach ($selected as $orderID) {
                $connection = $this->resourceConnection->getConnection();
                $table = $connection->getTableName('sales_order');
                $result = $connection->fetchAll("SELECT shipsy_reference_numbers, increment_id FROM `".$table."` WHERE entity_id =". $orderID);
                $storedReferenceNumber = $result[0]['shipsy_reference_numbers'];
                if (isset($storedReferenceNumber) && !empty($storedReferenceNumber)) {
                    array_push($consignmentReferenceNumberArray, $storedReferenceNumber);
                }
                else {
                    $this->messageManager->addErrorMessage('Cannot generate label for unsynced order - ' . $result[0]['increment_id']);
                }
            }
        }
        $dataToSendArray = ['consignmentIds' => $consignmentReferenceNumberArray, 'isReferenceNumber' => true];
        $dataToSendJson = json_encode($dataToSendArray);
        $base_url = $this->scopeConfig->getValue(
            'configuration/services/shipsy_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $organisation_id = $this->scopeConfig->getValue(
            'configuration/services/organisation_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $headers = [
            'Content-Type:application/json',
            'organisation-id:'. $organisation_id,
            'shop-origin:magento',
            'shop-url:'. $this->urlInterface->getBaseUrl(),
            'customer-id:'.$this->cookieManager->getCookie('customer-id'),
            'access-token:'.$this->cookieManager->getCookie('access-token-shipsy')
        ];
        $ch = curl_init($base_url . '/api/ecommerce/generateconsignmentlabelStream');

        curl_setopt($ch, CURLOPT_POST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataToSendJson);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);

        header('Content-Type:application/pdf');
        header('Content-Disposition:attachment;filename=bulklabels.pdf');
        file_put_contents("bulklabels.pdf", $result);
        readfile('bulklabels.pdf');
        
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirect->setPath('sales/*/');

        return $redirect;
    }
}
