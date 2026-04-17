<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Retailinsights\SplitOrder\Controller\Onepage;


class Success extends \Magento\Checkout\Controller\Onepage\Success
{
    /**
     * Order success action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$this->_objectManager->get(\Magento\Checkout\Model\Session\SuccessValidator::class)->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

		$session->clearQuote();
		//@todo: Refactor it to match CQRS
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/split_order_final.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$logger->info("Currently this working");
		$orderManagement = $objectManager->get('Magento\Sales\Api\OrderManagementInterface');
		$storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$storeID       = $storeManager->getStore()->getStoreId(); 
		$storeCode       = $storeManager->getStore()->getCode(); 
		$storeName     = $storeManager->getStore()->getName();
		$logger->info("Current Store :".$storeName."_storeCode_:".$storeCode."_StoreId :".$storeID);
        try {  
			 
			//echo "<pre>";print_r ($session->getData()); die;
			$logger->info('Session Array Log'.print_r($session->getData(), true));
			$main_order_id = $session->getLastOrderId();
			$logger->info("main_order_id ".$main_order_id);
			$lastrealOrderId = $session->getLastRealOrderId();
			$helperData = $objectManager->get('Retailinsights\SplitOrder\Helper\Data');
			$helperData->statusChangeInvoiceGenarate($main_order_id);
			// $main_order_id = $orderIds[0];
			// $logger->info("Currently id".json_encode($orderIds));
			$order = $objectManager->create('Magento\Sales\Model\Order')->load($main_order_id);
			$orderItems = $order->getAllVisibleItems();
			$produtcount = count($orderItems);
			$product_ids=array();
			$increment_ids=array();
			$order_ids=array();
			$last_quote_id = array();
			$last_order_id = array();
			$last_increment_id =array();
			$last_status = array();
			foreach ($orderItems as $item) {
			    $productId =  $item->getId();
			    $product_ids[] = array($productId => $item->getQtyOrdered());
			}
		    $status = 'fail';
			if($produtcount > 1 && $storeCode == 'schools' ) {
			$logger->info('Product ids'.json_encode($product_ids));
			foreach ($product_ids as $value) {
				$logger->info('Product pass'.json_encode($value));
				$order_details1 = $this->getAllDetailsOne($main_order_id, $value);
				$order1 = $helperData->createMageOrder($order_details1);
				if (isset($order1['error'])) {
							throw new \Exception($order1['msg']);
							 $logger->info('Order error'.$order1['msg']);
				} else {
					$status = 'success';
					// $increment_ids[]=$order1['increment_id'];
					$order_ids[$order1['order_id']]= $order1['increment_id'];
					$last_quote_id[] = $order1['quote_id'];
					$last_order_id[] = $order1['order_id'];
					$last_status[] = $order1['status'];
					$last_increment_id[] = $order1['increment_id'];
					$logger->info('Success');
				}
				//exit();
			} 
		        if($status == 'success') {
					// $orderManagement->cancel($main_order_id);
					// $order->setStatus("order_split");
					$order->setState("processing")->setStatus("order_split");
					$splitorder_ids = implode(" , ",$order_ids);
					$order->setParentSplitOrder($splitorder_ids);
					$order->save();
					foreach ($order_ids as $key => $value) {
						$order = $objectManager->create('Magento\Sales\Model\Order')->load($key);
						if($order->hasInvoices()) {
						$order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
						$order->setParentSplitOrder($lastrealOrderId);
						$order->save();
						}
					}

					$checkoutsession = $objectManager->get('Magento\Checkout\Model\Session');
					$checkoutsession->setLastQuoteId(end($last_quote_id));
					$checkoutsession->setLastSuccessQuoteId(end($last_quote_id));
					$checkoutsession->setLastOrderId(end($last_order_id));
					$checkoutsession->setLastRealOrderId(end($last_increment_id));
					$checkoutsession->setLastOrderStatus(end($last_status));
					$checkoutsession->setOrderIds($order_ids);
					$logger->info("Last Order Id - ".$last_increment_id[0]);
		        } 
		    }
		} catch (\Exception $e) {
		   $logger->info($e->getMessage());
		}

        $resultPage = $this->resultPageFactory->create();
        if($status == 'success') {
                $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            ['order_ids' => $last_order_id]
        );
        } else {
            $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            ['order_ids' => [$session->getLastOrderId()]]
        );
          }
      
        return $resultPage;
    }

     /**
     * Returns details of order.
     *
     * @param int   $order_id order_id
     * @param array $array    order_qty_detail_array
     *
     * @return array
     */
    public function getAllDetailsOne($order_id, $array)
    {

	    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/split_order_final.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$logger->info('getPerticularDetails'); // Simple Text Log

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('sales_order_item');

		$order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);

		$orderDetails = [];
		$orderDetails['currency_code'] = $order->getOrderCurrencyCode();
		$orderDetails['order_id'] = $order_id;
		$orderDetails['order_status'] = $order->getStatus();
		$orderDetails['store_id'] = $order->getStoreId();
		$orderDetails['email'] = $order->getCustomerEmail();
		$orderDetails['quote_id'] = $order->getQuoteId();
		$firstname= $order->getShippingAddress()->getFirstname();
		$lastname= $order->getShippingAddress()->getLastname();
		$street = $order->getShippingAddress()->getStreet();
		$city = $order->getShippingAddress()->getCity();
		$region = $order->getShippingAddress()->getRegion();
		$postcode = $order->getShippingAddress()->getPostcode();
		$telephone = $order->getShippingAddress()->getTelephone();
		$orderDetails['billing_address'] = $order->getBillingAddress()->getData();
		$orderDetails['shipping_address'] = $order->getShippingAddress() ? $order->getShippingAddress()->getData() : null;
		$logger->info('shipping address'.json_encode($orderDetails['shipping_address']));
		$orderDetails['shipping_method'] = $order->getShipping_method() ? $order->getShipping_method() : null;
		$logger->info('shipping method'.$orderDetails['shipping_method']);
		$orderDetails['shipping_amount'] = $order->getShippingAmount() ? $order->getShippingAmount() / $order->getTotalQtyOrdered() : null;
		$orderDetails['payment_method'] = $order->getPayment()->getMethod();
		$orderDetails['discount_description'] = $order->getDiscountDescription() ? $order->getDiscountDescription() : null;
		$orderDetails['coupon_code'] = $order->getCouponCode() ? $order->getCouponCode() : null;
		$orderDetails['coupon_rule_name'] = $order->getCouponRuleName() ? $order->getCouponRuleName() : null;
		$orderDetails['order_increment_id'] = $order->getIncrementId();
		$orderDetails['remote_ip'] = $order->getRemote_ip();

        //get all items of order
        $orderItems = $order->getAllVisibleItems();
        $i = 0;
        $logger->info('orderItems start');
        foreach ($orderItems as $item) {
            if (isset($array[$item->getItem_id()])) {
                //get product data
				$logger->info('parent_item_id '.$item->getItem_id());
                $sales_order_item_sql =  $connection->select()->from(['main_table' => $tableName])->where('main_table.parent_item_id = ?', $item->getItem_id())
					->where('main_table.order_id = ?', $order_id);
				$logger->info('sales_order_item_sql '.$sales_order_item_sql);
                $result1 = $connection->fetchAll($sales_order_item_sql);
                if (!empty($result1)) {
                    //for configurable/bundle products
					$logger->info('for configurable and bundle products');
                    $option_arr = json_decode($result1[0]['product_options'], true);
                    //$logger->info('option_arr '. print_r($option_arr ,true));
                    if (isset($option_arr['info_buyRequest']['product'])) {
                        $orderDetails['items'][$i]['product_id'] = $option_arr['info_buyRequest']['product'];
                    } else {
                        $logger->info('option_arr else start');
						$sql12 = $connection->select()->from(['main_table' => $tableName], ['product_id'])->where('main_table.item_id = ?', $result1[0]['parent_item_id'])->where('main_table.order_id = ?', $order_id);
                        $result12 = $connection->fetchAll($sql12);
                        $orderDetails['items'][$i]['product_id'] = $result12[0]['product_id'];
                    }
                    if (isset($option_arr['info_buyRequest']['super_attribute'])) {
                        $orderDetails['items'][$i]['product_options']['super_attribute'] = $option_arr['info_buyRequest']['super_attribute'];
                    }
                    if (isset($option_arr['info_buyRequest']['bundle_option'])) {
                        $orderDetails['items'][$i]['product_options']['bundle_option'] = $option_arr['info_buyRequest']['bundle_option'];
                    }
                    if (isset($option_arr['info_buyRequest']['bundle_option_qty'])) {
                        $orderDetails['items'][$i]['product_options']['bundle_option_qty'] = $option_arr['info_buyRequest']['bundle_option_qty'];
                    }
                    if (isset($option_arr['bundle_selection_attributes'])) {
                        $orderDetails['items'][$i]['product_options']['bundle_selection_attributes'] = $option_arr['bundle_selection_attributes'];
                    }
                } else {
                    $sales_order_sql =  $connection->select()->from(['main_table' => $tableName])->where('main_table.item_id = ?', $item->getItem_id())->where('main_table.order_id = ?', $order_id);
                    $result2 = $connection->fetchAll($sales_order_sql);
                    if (!empty($result2)) {
                        //for downloadable products
						$logger->info('for downloadable products');
                        $option_arr = json_decode($result2[0]['product_options'], true);
                        if (isset($option_arr['links'])) {
                            $orderDetails['items'][$i]['product_options']['links'] = $option_arr['links'];
                        }
                    }
                    $orderDetails['items'][$i]['product_id'] = $item->getProduct_id();
                }
                $orderDetails['items'][$i]['price'] = $item->getPrice();
                $orderDetails['items'][$i]['original_price'] = $item->getOriginalPrice();
                $orderDetails['items'][$i]['qty'] = (int)$array[$item->getItem_id()];
                $orderDetails['items'][$i]['applied_rule_ids'] = $item->getAppliedRuleIds();
                $orderDetails['items'][$i]['discount_percent'] = $item->getDiscountPercent();
                $orderDetails['items'][$i]['discount_amount'] = ($item->getDiscountAmount() / $item->getQtyOrdered()) * (int)$array[$item->getItem_id()];
                $orderDetails['items'][$i]['tax_percent'] = $item->getTaxPercent();
                $orderDetails['items'][$i]['tax_amount'] = ($item->getTaxAmount() / $item->getQtyOrdered()) * (int)$array[$item->getItem_id()];
				$logger->info('item OptionalSelectedItems ' . $item->getOptionalSelectedItems());
				$orderDetails['items'][$i]['optional_selected_items'] = $item->getOptionalSelectedItems();
				$orderDetails['items'][$i]['given_options'] = $item->getGivenOptions();
				$orderDetails['items'][$i]['given_option_updated_at'] = $item->getGivenOptionUpdatedAt();
				$orderDetails['items'][$i]['given_options_msg'] = $item->getGivenOptionsMsg();
                $i++;
            }
        }
		$logger->info('orderItems end');
        return $orderDetails;
    }
}
