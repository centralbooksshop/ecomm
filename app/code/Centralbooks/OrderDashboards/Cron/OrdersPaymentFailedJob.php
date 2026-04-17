<?php


namespace Centralbooks\OrderDashboards\Cron;


class OrdersPaymentFailedJob
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
        
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/orderspaymentfailed.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer); 
		$recordsUpdated = [];
		$enable = $this->getConfigData(self::ENABLE_CRON);
        if ($enable) {
            $orders = $this->orderCollection();
            if ($orders) {
                foreach ($orders as $order) {
					                  
					$status = null;
					//$parent_split_order = $order->getParentSplitOrder()
					$splitOrderIds = explode(',', $order->getParentSplitOrder() ?? "");
					if (count($splitOrderIds) > 1 || !$order->getParentSplitOrder()) {
						$methodTitle = $order->getPayment()->getAdditionalInformation("method_title");
						$logger->info('Increment_id '. $order->getIncrementId(). ' methodTitle ' . $methodTitle . ' order_status ' . $order->getStatus());

						if ($methodTitle == 'CCAvenue') {
							$status = $this->ccAvenueStatusApiCall($order->getIncrementId());
						} elseif ($methodTitle == 'PayUMoney') {
							$status = $this->payuStatusApiCall($order->getIncrementId());
						} elseif ($methodTitle == 'Cashfree') {
							$status = $this->cashFreeStatusApiCall($order->getIncrementId());
						}
						$logger->info('methodTitle ' . $methodTitle . ' Api Order Status ' . json_encode($status));
						if (!empty($status) && isset($status['order'])) {
							$recordsUpdated[] = [
								'id' => $order->getIncrementId(),
								'old_status' => $order->getStatus(),
								'new_status' => $status['order']
							];
							$logger->info('recordsUpdated Array '.print_r($recordsUpdated, true));
							/*if (count($splitOrderIds) > 1) {
								foreach ($splitOrderIds as $value) {
									$splitOrder = $this->orderFactory->create()->loadByIncrementId($value);
									if ($status['order'] != $splitOrder->getStatus()) {
										$splitOrder->setStatus($status['order'])->setState($status['order']);
									}
									$splitOrder->setPaymentStatus($status['payment']);
									$splitOrder->save();
								}
							}*/
							$logger->info('Order Status ' . $order->getStatus());
							 if ($status['order'] != $order->getStatus()) {
								if($status['order'] == 'processing') {
									if ($order->isCanceled()) {
										$comment = 'Automatically remove cancelled';
										$order->setState("processing");
										$order->setStatus("processing");
										$order->setSubtotalCanceled(0);
										$order->setBaseSubtotalCanceled(0);
										$order->setTaxCanceled(0);
										$order->setBaseTaxCanceled(0);
										$order->setShippingCanceled(0);
										$order->setBaseShippingCanceled(0);
										$order->setDiscountCanceled(0);
										$order->setBaseDiscountCanceled(0);
										$order->setTotalCanceled(0);
										$order->setBaseTotalCanceled(0);
										if (!empty($comment)) {
											$order->addStatusHistoryComment($comment, false);
										}
										$order->save();
										$productStockQty = [];
										foreach ($order->getAllVisibleItems() as $item) {
											$productStockQty[$item->getProductId()] = $item->getQtyCanceled();
											foreach ($item->getChildrenItems() as $child) {
												$productStockQty[$child->getProductId()] = $item->getQtyCanceled();
												$child->setQtyCanceled(0);
												$child->setTaxCanceled(0);
												$child->setDiscountTaxCompensationCanceled(0);
											}
											$item->setQtyCanceled(0);
											$item->setTaxCanceled(0);
											$item->setDiscountTaxCompensationCanceled(0);
											$item->save();
										}
										/*$orderItems = $order->getAllItems();
										foreach ($orderItems as $item) {
											$item->setData("qty_canceled",0)->save();
										}*/
									}

									$this->_createInvoice($order->getId());	
								}
								$order->setTotalPaid($order->getGrandTotal());
								$order->setBaseTotalPaid($order->getBaseGrandTotal());
								$order->setStatus($status['order'])->setState($status['order']);
								$order->setPaymentStatus($status['payment']);
								$order->save();
							}
						}
					}
                }
            }
            if (count($recordsUpdated)) {
                $this->reportsFactory->setData([
                    'content' => $this->json->serialize($recordsUpdated),
                    'created_at' => date("Y-m-d H:i:s")
                ])->save();
            }
        }
    }

	
    private function orderCollection()
    {
        $date1 = (new \DateTime())->modify($this->getConfigData(self::ORDERS_CRON_START));
        $date2 = (new \DateTime())->modify($this->getConfigData(self::ORDERS_CRON_BEFORE));
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/orderspaymentfailed.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$logger->info('Order date1 ' . $date1->format('Y-m-d h:i:s') . ' and date2 ' . $date2->format('Y-m-d h:i:s'));
        $orders = $this->orderFactory->create()->getCollection()
            ->addFieldToFilter('status',['in' => ['pending','pending_payment']])
            ->addFieldToFilter('created_at', [
                'gteq' => $date1->format('Y-m-d h:i:s'),
                'lt' => $date2->format('Y-m-d h:i:s')
            ])
            ->addFieldToFilter('payment_status',['null' => true]);
        return $orders;
    }

    private function cashFreeStatusApiCall($orderId)
    {
        
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/orderspaymentfailed.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
        $logger->info('cashFreeStatusApiCall ' . $orderId);
		
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
		$logger->info("api response : ". print_r($return,true));
        return $return;
    }

    public function ccAvenueStatusApiCall($orderId)
	{
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/orderspaymentfailed.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);

		$logger->info('ccAvenueStatusApiCall orderId ' . $orderId);

		$endpoint = $this->getConfigData(self::CCAVENUE_ENDPOINT);
		$accessCode = $this->getConfigData(self::CCAVENUE_ACCESS_CODE);
		$encryptKey = $this->getConfigData(self::CCAVENUE_ENCRYPT_KEY);

		//$logger->info('endpoint ' . $endpoint);
		//$logger->info('accessCode ' . $accessCode);
		//$logger->info('encryptKey ' . $encryptKey);

		try {

			$requestData = json_encode([
				"order_no" => $orderId
			]);

			$encRequest = $this->ccAvenueEncrypt($requestData, $encryptKey);

			$postData = [
				"enc_request"   => $encRequest,
				"access_code"   => $accessCode,
				"command"       => "orderStatusTracker",
				"request_type"  => "JSON",
				"response_type" => "JSON",
				"version"       => "1.2"
			];

			$curl = $this->curl->create();

			$curl->setHeaders([
				"Content-Type" => "application/x-www-form-urlencoded"
			]);

			$curl->setOption(CURLOPT_RETURNTRANSFER, true);
			$curl->setOption(CURLOPT_FOLLOWLOCATION, true);
			$curl->post($endpoint, http_build_query($postData));

			$rawResponse = $curl->getBody();

			$logger->info('CCAvenue Raw Response : ' . $rawResponse);

            $response = [];
 
			foreach (explode('&', $rawResponse) as $pair) {
				$parts = explode('=', $pair, 2);
				if (count($parts) === 2) {
					$response[$parts[0]] = $parts[1];
				}
			}

			if (!isset($response['enc_response'])) {
				$logger->info('Invalid response received');
				return null;
			}

			$encResponse = trim($response['enc_response']);
 
            $decryptResponse = $this->ccAvenueDecrypt($encResponse, $encryptKey);
			if (!$decryptResponse) {
				$logger->info('CCAvenue Decryption Failed');
				return null;
			}

			$logger->info('Decrypted Response : ' . $decryptResponse);

			$data = $this->json->unserialize($decryptResponse);


            $orderStatus = strtolower($data['order_status'] ?? '');

			switch ($orderStatus) {

				case 'shipped':
				case 'success':
					return ['order' => 'processing', 'payment' => 'success'];

				case 'initiated':
					return ['order' => 'pending', 'payment' => 'pending'];

				case 'aborted':
				case 'failure':
					return ['order' => 'canceled', 'payment' => 'failed'];

				case 'unsuccessful':
				case 'awaited':
				case 'timeout':
				case 'invalid':
					return ['order' => 'pending', 'payment' => 'failed'];

				default:
					return null;
			}

		} catch (\Exception $e) {

			$logger->info('CCAvenue API ERROR: ' . $e->getMessage());
		}

		return null;
	}

    public function ccAvenueStatusApiCallold($orderId)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/orderspaymentfailed.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
        $logger->info('ccAvenueStatusApiCall ' . $orderId);

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
		$logger->info("api response : ". print_r($return,true));
        return $return;
    }

    public function payuStatusApiCall($orderId)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/orderspaymentfailed.log');
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
		$logger->info("api response : ". print_r($return,true));
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

    public function ccAvenueEncryptold($plainText, $key) {
        $key = hex2bin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($openMode);
        return $encryptedText;
    }

    public function ccAvenueDecryptold($encryptedText, $key) {
        $key = hex2bin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		if (!ctype_xdigit($encryptedText)) {
			return null;
		}
        $encryptedText = hex2bin($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }

	public function ccAvenueEncrypt($plainText, $key)
	{
		$key = hex2bin(md5($key));

		$initVector = pack(
			"C*",
			0x00,0x01,0x02,0x03,
			0x04,0x05,0x06,0x07,
			0x08,0x09,0x0a,0x0b,
			0x0c,0x0d,0x0e,0x0f
		);

		$encryptedText = openssl_encrypt(
			$plainText,
			'AES-128-CBC',
			$key,
			OPENSSL_RAW_DATA,
			$initVector
		);

		return bin2hex($encryptedText);
	}

	public function ccAvenueDecrypt($encryptedText, $key)
	{
		if (!ctype_xdigit($encryptedText)) {
			return null;
		}

		$key = hex2bin(md5($key));

		$initVector = pack(
			"C*",
			0x00,0x01,0x02,0x03,
			0x04,0x05,0x06,0x07,
			0x08,0x09,0x0a,0x0b,
			0x0c,0x0d,0x0e,0x0f
		);

		$encryptedText = hex2bin($encryptedText);

		return openssl_decrypt(
			$encryptedText,
			'AES-128-CBC',
			$key,
			OPENSSL_RAW_DATA,
			$initVector
		);
	}

}
