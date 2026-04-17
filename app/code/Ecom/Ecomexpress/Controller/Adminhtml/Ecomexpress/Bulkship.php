<?php 
namespace Ecom\Ecomexpress\Controller\Adminhtml\Ecomexpress;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class MassDelete
 */
class Bulkship extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;
    protected $collectionFactory;
    protected $resultRedirectFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeconfig,
        OrderManagementInterface $orderManagement
    ) {
        parent::__construct($context, $filter);
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->_scopeConfig = $scopeconfig;
    }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {

        $countShipments = 0;
        $resultRedirect = $this->resultRedirectFactory->create();
        
        //$model = $this->_objectManager->create('Magento\Sales\Model\Order');
        foreach ($collection->getItems() as $order) 
        {  
            if (!$order->getEntityId() || $order->hasShipments() || !$order->canShip()) {
                continue;
            }
            $address = $order->getShippingAddress();
            $checkAvailability = $this->_objectManager->create('Ecom\Ecomexpress\Model\Pincode')->load($address['postcode'],'pincode');
            if(!count($checkAvailability->getData())){
                continue;
            }
            $pay_type = 'PPD';
            $payment = $order->getPayment()->getMethodInstance()->getCode();        
            if($payment == 'cashondelivery' || $payment == 'phoenix_cashondelivery' || $payment == 'mst_cashondelivery'){
                $pay_type = 'COD';
            }
            $awbnos = $this->_objectManager->create ( 'Ecom\Ecomexpress\Model\Awb' )->getCollection()
                        ->addFieldToFilter('state',0)->addFieldToFilter('awb_type',$pay_type);
            $awbno = 0;
            if(!count($awbnos->getData())){
                $this->_messageManager->addErrorMessage(__('All AWB has been used. Download fresh AWB first.'));
                return $resultRedirect->setPath('sales/order/');
            }else{
                $awbno = $awbnos->getFirstItem()->getAwb();
            }
            // Initialize the order shipment object
            $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
            $shipment = $convertOrder->toShipment($order);
            $item = [];
            $item['qty'] = 0;
            $item['name'] = '';
            $item['weight'] = 0;
            $item['products'] = []; 
            foreach ($order->getAllItems() AS $orderItem) 
            { 
                // Check if order item is virtual or has quantity to ship
                if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual())
                    continue;  
                $qtyShipped = $orderItem->getQtyToShip();
                // Create shipment item with qty
                $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);         
                // Add shipment item to shipment
                $shipment->addItem($shipmentItem);
                $item['qty'] += $qtyShipped;
                $item['name'] .= $orderItem->getName ();
                $item['weight'] += $orderItem->getWeight ();
                $item['products'][] = $orderItem->getProductId();
            }
            $menifestation = $this->_submitDataToAwb($order,$item,$awbno,$pay_type);
            if($menifestation){
                // Register shipment
                $shipment->register();
                 
                $data = array(  'carrier_code' => 'ecomexpress',
                                'title' => 'EcomExpress',
                                'number' => $awbno, // Replace with your tracking number
                        );
                 
                $shipment->getOrder()->setIsInProcess(true);
                 
                try {
                    // Save created shipment and order
                    $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($data);
                    $shipment->addTrack($track)->save();
                    $shipment->save();
                    $shipment->getOrder()->save();
                     
                    // Send email
                    $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')->notify($shipment);
                     
                    $shipment->save();
                }catch (\Exception $e) { //echo $e->getMessage();die('in catch');
                    throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                }
                $countShipments++;
            }
        }
        $countFailedShipments = $collection->count() - $countShipments;

        if ($countFailedShipments && $countShipments) {
            $this->messageManager->addErrorMessage(__('%1 order(s) were not shipped through ECOM.', $countFailedShipments));
        } elseif ($countFailedShipments) {
            $this->messageManager->addErrorMessage(__('No order(s) were shipped through ECOM.'));
        }

        if ($countShipments) {
            $this->messageManager->addSuccessMessage(__('You have shipped through ECOM %1 order(s).', $countShipments));
        }

        return $resultRedirect->setPath('sales/order/');
    }

    public function _submitDataToAwb($order,$item,$awbno,$pay_type)
    {
        $params = [];
        $type = 'post';
        $address = $order->getShippingAddress();
        $resultRedirect = $this->resultRedirectFactory->create(); 
        $params ['username'] = $this->_scopeConfig->getValue ( 'carriers/ecomexpress/username' );
        $params ['password'] = $this->_scopeConfig->getValue ( 'carriers/ecomexpress/password' );
        $params ['json_input'] ['AWB_NUMBER'] = $awbno;
        $params ['json_input'] ['ORDER_NUMBER'] = $order ['increment_id'];
        $params ['json_input'] ['PRODUCT'] = $pay_type;
        
        $params ['json_input'] ['CONSIGNEE'] = $address ['firstname'];
        $params ['json_input'] ['CONSIGNEE_ADDRESS1'] = $address ['street'];
        $params ['json_input'] ['CONSIGNEE_ADDRESS2'] = $address ['postcode'];
        $params ['json_input'] ['CONSIGNEE_ADDRESS3'] = $address ['city'];
        $params ['json_input'] ['DESTINATION_CITY'] = $address ['city'];
        $params ['json_input'] ['PINCODE'] = $address ['postcode'];
        $params ['json_input'] ['STATE'] = $address ['region'];
        $params ['json_input'] ['MOBILE'] = $address ['telephone'];
        $params ['json_input'] ['TELEPHONE'] = $address ['telephone'];
        $params ['json_input'] ['ITEM_DESCRIPTION'] = $item['name'];
        $params ['json_input'] ['ACTUAL_WEIGHT'] = $item['weight'] ? $item['weight'] : 1;
        $params ['json_input'] ['PIECES'] = $item['qty'];
        $params ['json_input'] ['COLLECTABLE_VALUE'] = 0;

        if($pay_type!= "PPD")
            $params ['json_input'] ['COLLECTABLE_VALUE'] = $order ['grand_total'];

        $params ['json_input'] ['DECLARED_VALUE'] = $order ['grand_total'];
        $params ['json_input'] ['VOLUMETRIC_WEIGHT'] = 0;

        $items = $this->_objectManager->create('Magento\Catalog\Model\Product')->getCollection()
        ->addAttributeToSelect('*')->addAttributeToFilter('sku', array('in' => $item['products']));
        
        $params['json_input']['LENGTH']  = 10;
        $params['json_input']['BREADTH'] =  10;
        $params['json_input']['HEIGHT'] = 10;
        if(count($items->getData())){
            foreach($items as $packge_dimension){
                $params['json_input']['LENGTH']  += $packge_dimension->getEcomLength();             
                $params['json_input']['BREADTH'] +=  $packge_dimension->getEcomBreadth();
                $params['json_input']['HEIGHT'] += $packge_dimension->getEcomHeight();
            } 
        }

        $params ['json_input'] ['PICKUP_NAME'] = $this->_scopeConfig->getValue ( 'general/store_information/name' );
        $params ['json_input'] ['PICKUP_ADDRESS_LINE1'] = $this->_scopeConfig->getValue ( 'shipping/origin/street_line1' );
        $params ['json_input'] ['PICKUP_ADDRESS_LINE2'] = $this->_scopeConfig->getValue('shipping/origin/street_line2') ? $this->_scopeConfig->getValue('shipping/origin/street_line2') : $this->_scopeConfig->getValue('shipping/origin/street_line1');
        $params ['json_input'] ['PICKUP_PINCODE'] = $this->_scopeConfig->getValue ( 'shipping/origin/postcode' );
        $params ['json_input'] ['PICKUP_PHONE'] = $this->_scopeConfig->getValue ( 'general/store_information/phone' );
        $params ['json_input'] ['PICKUP_MOBILE'] = $this->_scopeConfig->getValue ( 'general/store_information/phone' );
        $params ['json_input'] ['RETURN_PINCODE'] = $this->_scopeConfig->getValue('shipping/origin/postcode');
        $params ['json_input'] ['RETURN_NAME'] = $this->_scopeConfig->getValue('general/store_information/name');
        $params ['json_input'] ['RETURN_ADDRESS_LINE1'] = $this->_scopeConfig->getValue('shipping/origin/street_line1');
        $params ['json_input'] ['RETURN_ADDRESS_LINE2'] = $this->_scopeConfig->getValue('shipping/origin/street_line2') ? $this->_scopeConfig->getValue('shipping/origin/street_line2') : $this->_scopeConfig->getValue('shipping/origin/street_line1');
        $params ['json_input'] ['RETURN_PHONE'] = $this->_scopeConfig->getValue ( 'general/store_information/phone' );
        $params ['json_input'] ['RETURN_MOBILE'] = $this->_scopeConfig->getValue ( 'general/store_information/phone' );
        
        if(!$params['json_input']['PICKUP_NAME'] || !$params['json_input']['PICKUP_PHONE']){ 
            $this->messageManager->addErrorMessage(__('Kindly fill the General Store Information.'));
            return $resultRedirect->setPath('sales/order/');
        }
        if(!$params['json_input']['PICKUP_PINCODE'] || !$params['json_input']['PICKUP_ADDRESS_LINE1']){
            $this->messageManager->addErrorMessage(__('Fill the Shipping Setting details first'));
            return $resultRedirect->setPath('sales/order/');
        }
        $url = 'https://api.ecomexpress.in/apiv3/manifest_awb/';
        if($this->_scopeConfig->getValue('carriers/ecomexpress/sanbox'))
            $url = 'https://clbeta.ecomexpress.in/apiv2/manifest_awb/';
        $params ['json_input'] = json_encode ( $params ['json_input'], true );
        $params ['json_input'] = "[ " . $params ['json_input'] . "]";
        
        $response = $this->_objectManager->get('Ecom\Ecomexpress\Helper\Data')->execute_curl($url,$type,$params);
        $response = json_decode( $response,'true' );
        //print_r($response);die;
        $flag = false;
        if($response['shipments'][0]['success']){
            $flag = true;
        }
        return $flag;
    }
}