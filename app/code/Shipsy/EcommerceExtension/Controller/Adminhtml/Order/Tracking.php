<?php

namespace Shipsy\EcommerceExtension\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Tracking extends \Magento\Framework\App\Action\Action
{
   
    protected $cookieManager;
    protected $_orderRepository;
 
    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $_convertOrder;
 
    /**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $_shipmentNotifier;
    protected $_shipmentRepository;
    protected $_trackFactory;
    protected $urlInterace;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlInterface,
        \Shipsy\EcommerceExtension\Helper\Data $dataHelper
    ) {
        parent::__construct($context);
        $this->cookieManager = $cookieManager;
        $this->_orderRepository = $orderRepository;
        $this->_convertOrder = $convertOrder;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_trackFactory = $trackFactory;
        $this->scopeConfig = $scopeConfig;
        $this->urlInterface = $urlInterface;
        $this->dataHelper = $dataHelper;
    }

    public function generateTrackingURL($crn, $orderID)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $sales_order = $resource->getTableName('sales_order');
        $organisation_id = $this->scopeConfig->getValue('configuration/services/organisation_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $base_url = $this->dataHelper->getBaseUrl($this->scopeConfig, $organisation_id);
        
        $dataToSendArray = ['customerReferenceNumberList' => [$crn]];
        $dataToSendInParameters = http_build_query($dataToSendArray);
        try {
            $headers = [
                'Content-Type:application/json',
                'organisation-id:'.$organisation_id,
                'shop-origin:magento',
                'shop-url:'. $this->urlInterface->getBaseUrl(),
                'customer-id:'.$this->cookieManager->getCookie('customer-id'),
                'access-token:'.$this->cookieManager->getCookie('access-token-shipsy')
            ];
            $ch = curl_init();
            $getUrl = $base_url . '/api/ecommerce/tracking?' . $dataToSendInParameters;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $getUrl);
            $result = curl_exec($ch);
            curl_close($ch);
            $resultdata = json_decode($result, true);
            if (array_key_exists('data', $resultdata)) {
                if ($resultdata['data'][0]['success'] && $resultdata['data'][0]['customerReferenceNumber'] == $crn) {
                    $trackingURL = $resultdata['data'][0]['trackingUrl'];
                    $sqlq = "Update " . $sales_order . " Set `shipsy_tracking_url` = '$trackingURL' Where `entity_id` = $orderID";
                    $uquery = $connection->query($sqlq);
                    return ['success' => $trackingURL];
                } else {
                    return ['failure' => 'Cannot find any Reference Number for ' . $crn];
                }
            } else {
                return ['failure' => $resultdata['error']['message']];
            }
        } catch (\Exception $e) {
            return ['failure' => $e->getMessage()];
        }
    }

    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected');
        $no_of_selection = count($selected);

        if ($no_of_selection > 0) {
            foreach ($selected as $orderID) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $sales_order = $resource->getTableName('sales_order');
                $newsql = "SELECT `main_table`.* FROM " . $sales_order . " AS `main_table`  WHERE main_table.entity_id = $orderID";
                $queryResult = $connection->fetchRow($newsql);
                
                $crn = $queryResult['increment_id'];
                $order = $this->_orderRepository->get($orderID);
                if (!empty($queryResult['shipsy_tracking_url'])) {
                    $errorexclude = 'Tracking URL already generated for ' . $crn;
                    $this->messageManager->addError(__($errorexclude));
                } else {
                    $response = $this->generateTrackingURL($crn, $orderID);
                    if (array_key_exists('success', $response)) {
                        $this->messageManager->addSuccessMessage('Successfully added tracking URL for order '. $crn);
                    } elseif (array_key_exists('failure', $response)) {
                        $this->messageManager->addErrorMessage('Failed to add tracking URL - ' . $response['failure']);
                    } else {
                        $this->messageManager->addErrorMessage('Failed to add tracking URL');
                    }
                }
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('sales/*/');
        }
        $errorexclude = 'No order selected';
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->messageManager->addError(__('Warning Reason: '. $errorexclude));
        return $resultRedirect->setRefererOrBaseUrl();
    }
}
