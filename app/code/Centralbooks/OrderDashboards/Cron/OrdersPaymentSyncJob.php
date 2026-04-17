<?php


namespace Centralbooks\OrderDashboards\Cron;


class OrdersPaymentSyncJob
{
    private const ENABLE_CRON = 'cbo/payments/orders_payment_sync_enable';
    private const ORDERS_CRON_START = 'cbo/payments/orders_cron_start';
    private const ORDERS_CRON_BEFORE = 'cbo/payments/orders_cron_before';
    private const CASHFREE_ENDPOINT = 'cbo/payments/cashfree_endpoint';
    private const CASHFREE_APP_ID = 'payment/cashfree/app_id';
    private const CASHFREE_SECRET_KEY = 'payment/cashfree/secret_key';
    private const PAYU_ENDPOINT = 'cbo/payments/payu_endpoint';
    private const PAYU_KEY = 'payment/payu/merchant_key';
    private const PAYU_SALT = 'payment/payu/salt';
    private const CCAVENUE_ENDPOINT = 'cbo/payments/ccavenue_endpoint';
    private const CCAVENUE_ACCESS_CODE = 'payment/ccavenue/access_code';
    private const CCAVENUE_ENCRYPT_KEY = 'payment/ccavenue/encryption_key';


	 /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */

		/**
		* @var InvoiceService
		*/
		protected $invoiceService;

		/**
		* @var TransactionFactory
		*/
		protected $transactionFactory;
		
		/**
		* @var \Magento\Framework\DB\Transaction
		*/
		protected $_transaction;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\Magento\Sales\Model\Service\InvoiceService $invoiceService,
	    \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\HTTP\Client\CurlFactory $curl,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Centralbooks\OrderDashboards\Model\CronReportsFactory $reportsFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->orderFactory = $orderFactory;
		$this->_orderRepository = $orderRepository;
		$this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->_transaction = $transaction;
        $this->curl = $curl;
        $this->json = $json;
        $this->reportsFactory = $reportsFactory->create();
    }
    /**
     * Cron execute function
     *
     * @return void
     */
    public function execute()
    {
        $recordsUpdated = [];
		$enable = $this->getConfigData(self::ENABLE_CRON);
        if ($enable) {
            $orders = $this->orderCollection();
            if ($orders) {
                foreach ($orders as $order) {
						$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/orderspayment.log');
						$logger = new \Zend_Log();
						$logger->addWriter($writer);
						//$logger->info('Orders IncrementId ' . $order->getIncrementId());
						//try {                    
                        $status = null;
						//$parent_split_order = $order->getParentSplitOrder()
                        $splitOrderIds = explode(',', $order->getParentSplitOrder() ?? "");
						if (count($splitOrderIds) > 1) {
						    //$logger->info('splitOrderIds Array '.print_r($splitOrderIds, true));
						    $logger->info('splitOrderIds count ' . count($splitOrderIds));
							$logger->info('orders status before update ' . $order->getStatus());
							if($order->getStatus() == 'processing') {
							   $order->setState("processing")->setStatus("order_split");
							   $order->save();
							}
							$logger->info('orders status after update ' . $order->getStatus());
						}

                        if (count($splitOrderIds) > 1 || !$order->getParentSplitOrder()) {
                            $methodTitle = $order->getPayment()->getAdditionalInformation("method_title");
							$logger->info('Increment_id '. $order->getIncrementId(). ' methodTitle ' . $methodTitle . ' order_status ' . $order->getStatus());

							if($order->getStatus() == 'processing') {
								$lastrealOrderId = $order->getIncrementId();
								$orderItems = $order->getAllVisibleItems();
								$productItemcount = 0;
								$productItemcount = count($orderItems);
								$main_order_id = $order->getId();
								$logger->info('productItemcount ' . $productItemcount . ' main_order_id ' . $main_order_id);
								$order_item_ids = array();
								$order_ids = array();
								$storeId = $order->getStoreId();
								if($productItemcount > 1 && $storeId == 3 ) {
									foreach ($orderItems as $item) {
										$orderitemId =  $item->getId();
										$order_item_ids[] = array($orderitemId => $item->getQtyOrdered());
									}
									$logger->info('Order item ids '.json_encode($order_item_ids) . 'storeId ' . $storeId);
									$order_status = 'fail';

									$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
								    $helperData = $objectManager->get('Retailinsights\SplitOrder\Helper\Data');
			                        $helperData->statusChangeInvoiceGenarate($main_order_id);

									foreach ($order_item_ids as $itemvalue) {
										$logger->info('Item Data '.print_r($itemvalue , true));
										$order_details = $this->getAllDetailsOne($main_order_id, $itemvalue);
										$order1 = $helperData->createMageOrderCron($order_details);
										if (isset($order1['error'])) {
													throw new \Exception($order1['msg']);
													 $logger->info('Order error'.$order1['msg']);
										} else {
											$order_status = 'success';
											$order_ids[$order1['order_id']] = $order1['increment_id'];
										}
									}
									
									if($order_status == 'success') {
										$order->setState("processing")->setStatus("order_split");
										$splitorder_ids = implode(" , ",$order_ids);
										$order->setParentSplitOrder($splitorder_ids);
										$order->save();
										foreach ($order_ids as $key => $value) {
											$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
											$order = $objectManager->create('Magento\Sales\Model\Order')->load($key);
											if($order->hasInvoices()) {
											$order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
											$order->setParentSplitOrder($lastrealOrderId);
											$order->setPaymentStatus('success');
											$order->save();
											}
										}
									} 
								}
							}
                        }
                   // }
                }
            }
            
        }
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

	    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/split_order_failed.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$logger->info('getAllDetailsOne start');

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('sales_order_item');

		$order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
        //$logger->info('Order Array Log'.print_r($order->getData(), true));
		$orderDetails = [];
		$orderDetails['currency_code'] = $order->getOrderCurrencyCode();
		$orderDetails['order_id'] = $order_id;
		$orderDetails['order_status'] = $order->getStatus();
		$orderDetails['store_id'] = $order->getStoreId();
		$orderDetails['email'] = $order->getCustomerEmail();
		$orderDetails['customer_id'] = $order->getCustomerId();
		$orderDetails['store'] = $order->getStore();
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
		$logger->info('getAllDetailsOne end');
        return $orderDetails;
    }

    private function orderCollection()
    {
        $date1 = (new \DateTime())->modify($this->getConfigData(self::ORDERS_CRON_START));
        $date2 = (new \DateTime())->modify($this->getConfigData(self::ORDERS_CRON_BEFORE));
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/orderspayment.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$logger->info('Order date1 ' . $date1->format('Y-m-d') . ' and date2 ' . $date2->format('Y-m-d'));
        $orders = $this->orderFactory->create()->getCollection()
            ->addFieldToFilter('status',['nin' => ['assigned_to_picker','complete', 'dispatched_to_courier', 'order_delivered', 'order_split', 'canceled','pending']]);
        return $orders;
    }

    private function cashFreeStatusApiCall($orderId)
    {
        $endpoint = $this->getConfigData(self::CASHFREE_ENDPOINT);
        $url = str_replace('{order_id}', $orderId, $endpoint);
        $headers = [
            'Cache-Control' => 'no-cache',
            'X-Client-Id' => $this->getConfigData(self::CASHFREE_APP_ID),
            'X-Client-Secret' => $this->getConfigData(self::CASHFREE_SECRET_KEY),
            'Content-Type' => 'application/json'
        ];
        $curlF = $this->curl->create(); 
        $curlF->setHeaders($headers);
        $curlF->setOption(CURLOPT_RETURNTRANSFER, true);
        $curlF->setOption(CURLOPT_FOLLOWLOCATION, true);
        $curlF->get($url);
        $response = $this->json->unserialize($curlF->getBody());
        $return = null;
		if ($response['status']) {
		   if ($response['status'] != 'ERROR') {
			 if(isset($response['txStatus'])) {
				if($response['txStatus'] == 'SUCCESS') {
					$return = ['order' => 'processing', 'payment' => 'success'];
				} elseif ($response['txStatus'] == 'PENDING' || $response['txStatus'] == 'USER_DROPPED') {
					$return = ['order' => 'holded', 'payment' => 'pending'];
				} elseif ($response['txStatus'] == 'FAILED') {
					$return = ['order' => 'canceled', 'payment' => 'failed'];
				}
			 }
		  }
		}
        return $return;
    }

    public function ccAvenueStatusApiCall($orderId)
    {
        $response = [];
        $endpoint = $this->getConfigData(self::CCAVENUE_ENDPOINT);
        $accessCode = $this->getConfigData(self::CCAVENUE_ACCESS_CODE);
        $encryptKey = $this->getConfigData(self::CCAVENUE_ENCRYPT_KEY);
        $enc_request = $this->ccAvenueEncrypt(json_encode(['order_no' => $orderId]), $encryptKey);
        $params = [
            'enc_request' => $enc_request,
            'access_code' => $accessCode,
            'command' => 'orderStatusTracker',
            'request_type' => 'JSON',
            'response_type' => 'JSON',
            'version' => '1.2'
        ];
        $url = $endpoint."?".http_build_query($params);
        $curlF = $this->curl->create();
        $curlF->setOption(CURLOPT_RETURNTRANSFER, true);
        $curlF->setOption(CURLOPT_FOLLOWLOCATION, true);
        $curlF->setOption(CURLOPT_POST, true);
        $curlF->get($url);
        parse_str(urldecode($curlF->getBody()), $response);
        $return = null;
        if(isset($response['status'])) {
            //$decryptResponse = $this->ccAvenueDecrypt($response['enc_response'], $encryptKey);
            //$response = $this->json->unserialize($curlF->getBody());
			if(isset($response['order_status'])) {
				if ($response['order_status'] == 'shipped') {
					$return = ['order' => 'processing', 'payment' => 'shipped'];
				} elseif ($response['order_status'] == 'initiated') {
					$return = ['order' => 'holded', 'payment' => 'initiated'];
				} elseif ($response['order_status'] == 'aborted') {
					$return = ['order' => 'canceled', 'payment' => 'aborted'];
				}
			}
        }
        return $return;
    }

    public function payuStatusApiCall($orderId)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/orderspayment.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
        $logger->info('payuStatusApiCall ' . $orderId);
		$endpoint = $this->getConfigData(self::PAYU_ENDPOINT);
        $key = $this->getConfigData(self::PAYU_KEY);
        $salt = $this->getConfigData(self::PAYU_SALT);
        $hash = hash('sha512', $key.'|verify_payment|'.$orderId.'|'.$salt);
        $post = http_build_query([
            'key' => $key,
            'command' => 'verify_payment',
            'var1' => $orderId,
            'hash' => $hash
        ]);
        $curlF = $this->curl->create();
        $curlF->setHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]);
        $curlF->setOption(CURLOPT_RETURNTRANSFER, true);
        $curlF->setOption(CURLOPT_FOLLOWLOCATION, true);
        $curlF->setOption(CURLOPT_POSTFIELDS, $post);
        $curlF->get($endpoint);
        $response = $this->json->unserialize($curlF->getBody());
        $return = null;
        if($response['status']) {
            $status = $response['transaction_details'][$orderId]['unmappedstatus'];
            if ($status == 'captured') {
                $return = ['order' => 'processing', 'payment' => 'captured'];
            } elseif ($status == 'initiated') {
                $return = ['order' => 'holded', 'payment' => 'initiated'];
            } elseif ($status == 'failed') {
                $return = ['order' => 'canceled', 'payment' => 'failed'];
            } elseif ($status == 'dropped') {
                $return = ['order' => 'canceled', 'payment' => 'dropped'];
            }elseif ($status == 'bounced') {
                $return = ['order' => 'canceled', 'payment' => 'bounced'];
            }elseif ($status == 'userCancelled') {
                $return = ['order' => 'canceled', 'payment' => 'userCancelled'];
            }
        }
        return $return;
    }

	/*public function _createInvoice($orderId)
    {
        $order = $this->_orderRepository->get($orderId);
        if($order->canInvoice()) {
          $invoice = $this->invoiceService->prepareInvoice($order);
		  $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
		  $invoice->register();
		  $invoice->getOrder()->setCustomerNoteNotify(false);
		  $invoice->getOrder()->setIsInProcess(true);
		  $order->addCommentToStatusHistory(__('Automatically INVOICED'), false);
		  $transactionSave = $this->transactionFactory->create();
		  $transactionSave->addObject($invoice)->addObject($invoice->getOrder());
		  $transactionSave->save();
        }
    }*/
    public function _createInvoice($orderId)
	{
	    $order = $this->_orderRepository->get($orderId);
	    if (!$order->hasInvoices()) {
			//$invoice = $order->prepareInvoice();
			$invoice = $this->invoiceService->prepareInvoice($order);
			$invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
			$invoice->register();
			$invoice->getOrder()->setCustomerNoteNotify(false);
			$invoice->getOrder()->setIsInProcess(true);
			$order->addCommentToStatusHistory(__('Automatically INVOICED'), false);
			$transactionSave = $this->transactionFactory->create();
			$transactionSave->addObject($invoice)->addObject($invoice->getOrder());
			$transactionSave->save();
			if ($order->getShipmentsCollection()->count()) {
				$order->setState('complete')->setStatus('complete');
				$order->addStatusToHistory($order::STATE_COMPLETE, 'Order has been paid.', true);
			} else {
			$order->setState($order::STATE_PROCESSING)->save();
			$order->setStatus($order::STATE_PROCESSING)->save();
			$order->addStatusToHistory($order::STATE_PROCESSING, 'Order has been paid.', true);
			}
			$order->setTotalPaid($order->getGrandTotal());
			$order->setBaseTotalPaid($order->getBaseGrandTotal());
			$this->_orderRepository->save($order);
		}
	}

    private function getConfigData($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function ccAvenueEncrypt($plainText, $key) {
        $key = hex2bin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($openMode);
        return $encryptedText;
    }

    public function ccAvenueDecrypt($encryptedText, $key) {
        $key = hex2bin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = hex2bin($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }

}