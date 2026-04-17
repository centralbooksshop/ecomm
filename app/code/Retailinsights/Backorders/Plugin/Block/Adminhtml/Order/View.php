<?php
namespace Retailinsights\Backorders\Plugin\Block\Adminhtml\Order;

class View
{

  public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view)
{
    $message ='Are you sure you want to do this?';
    $url = $view->getEditUrl();
    $order = $view->getOrder();
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $order = $objectManager->create('Magento\Sales\Model\Order')->load($order->getEntityId());
    
    $urlBuilder = $objectManager->get('\Magento\Backend\Model\UrlInterface');

    $retailHelper = $objectManager->get('\Retailinsights\Backorders\Helper\Data');

    $increment_id = $order->getIncrementId(); //loadByIncrementId($reservedOrderId)
    $backorder_items = $retailHelper->getBackOrderItemData($increment_id);

    $redirecturl = $urlBuilder->getRouteUrl('retailinsights_backorders/orders/createbackorder',[ 'order_id'=> $order->getEntityId()]);
        $order_check = explode("-",$increment_id);
        if(count($order_check) <= 1){
              if($order->getIsBackeorderedItems() == 'Yes') {
                $backorder_id = $retailHelper->isBackOrderCreated($increment_id);
        if($backorder_id) {
            $orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($backorder_id);
$orderId = $orderInfo->getEntityId();
$orderurl = $urlBuilder->getRouteUrl('sales/order/view',[ 'order_id'=> $orderId,'key'=>$urlBuilder->getSecretKey('sales','order/','view')]);
               $view->addButton(
        'order_myaction',
        [
            'label' => __('View BackOrder'),
            'class' => 'edit primary',
            'onclick' => "setLocation('" .$orderurl. "')"
        ]
    );
        } else if(count($backorder_items) > 0) {
            $view->addButton(
        'order_myaction',
        [
            'label' => __('Create Back Order'),
            'class' => 'edit primary',
            'onclick' => "confirmSetLocation('{$message}', '{$redirecturl}')"
        ]
    );
        }
     

    }
        } else {
              $increment_id = $order_check[0];
              $orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($increment_id);
$orderId = $orderInfo->getEntityId();
$orderurl = $urlBuilder->getRouteUrl('sales/order/view',['order_id'=> $orderId,'key'=>$urlBuilder->getSecretKey('sales','order/','view')]);

              $view->addButton(
        'order_myaction',
        [
            'label' => __('View Main Order'),
            'class' => 'edit primary',
            'onclick' => "setLocation('" .$orderurl. "')"
        ]
    );

        }

  


}

}