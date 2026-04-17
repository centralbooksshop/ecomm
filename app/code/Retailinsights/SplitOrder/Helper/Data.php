<?php

namespace Retailinsights\SplitOrder\Helper;

use Magento\Store\Model\ScopeInterface;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * StoreManager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ProductModel
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * CartRepositoryInterface
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepositoryInterface;

    /**
     * CartManagementInterface
     *
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagementInterface;

    /**
     * CustomerFactory
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * CustomerRepository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * OrderModel
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * EventManager
     *
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;

    /**
     * Resource
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * MessageManager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    private $collectionFactory;
    protected $logger;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $products
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $products,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\DB\Transaction $transaction,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\RequestInterface $request,
        \SchoolZone\Addschool\Model\SimilarproductsattributesFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->storeManager = $storeManager;
        $this->product = $products;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->quoteRepository = $quoteRepository;
        $this->quote = $quote;
        $this->_orderRepository = $orderRepository;
        $this->quoteManagement = $quoteManagement;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->order = $order;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->logger = $logger;
        $this->invoiceSender = $invoiceSender;
        $this->eventManager = $eventManager;
        $this->resource = $resource;
        $this->_request = $request;
        $this->collectionFactory = $collectionFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    public function getSplitItemData($increment_id)
    {
        $order = $this->order->loadByIncrementId($increment_id);
        $orderItems = $order->getAllItems();
        $order_status = '';
        $back_order_data = array();
        $product_skus = array();
        $product_qtys = array();
        foreach ($orderItems as $item) {
            $item_id_qts[] = array($tem->getId() => $item->getQtyOrdered());
        }

        return $item_id_qts;
    }

    public function statusChangeInvoiceGenarate($order_id)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
        try {
            if (!$order->canInvoice()) {
                return null;
            }
            if (!$order->getState() == 'new') {
                return null;
            }
            if (($order->getPayment()->getMethodInstance()->getCode() == 'ccavenue')) {
                if ($this->IsSplitOrder($order_id)) {
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $order->save();
                } else {
                    return null;
                }
            }

            if (($order->getPayment()->getMethodInstance()->getCode() == 'checkmo') ||
                ($order->getStatus() == 'canceled')
            ) {
                return null;
            }
            if (($order->getPayment()->getMethodInstance()->getCode() == 'receivedpaymentcard') ||
                ($order->getPayment()->getMethodInstance()->getCode() == 'receivedpaymentcash')
            ) {
                if ($this->IsSplitOrder($order_id)) {
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $order->save();
                } else {
                    $this->generateInvoice($order);
                }
            } elseif (($order->getState() == 'new') && ($order->getStatus() == 'processing')) {
                if ($this->IsSplitOrder($order_id)) {
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $order->save();
                } else {
                    $this->generateInvoice($order);
                }
            }
        } catch (\Exception $e) {
            $order->addStatusHistoryComment('Exception message: ' . $e->getMessage(), false);
            $order->save();
            return null;
        }
    }


    public function updateBackorder($order_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $backOrderHelper = $objectManager->get('Retailinsights\Backorders\Helper\Data');
        $order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
        $orderItems = $order->getAllItems();
        $order_status = '';
        $back_order_data = array();
        $product_skus = array();
        $product_qtys = array();
        foreach ($orderItems as $item) {
            $productId =  $item->getProductId();
            if ($backOrderHelper->isBackordred($item)) {
                $order_status = 'Yes';
                $product_skus[] = $item->getSku();
                $product_qtys[] = $item->getQtyOrdered();
            }
        }
        if ($order_status == 'Yes') {
            $back_order_data['order_id'] = $order->getIncrementId();
            $back_order_data['item_id']  = $order->getEntityId();
            $back_order_data['sku']  = implode(",", $product_skus);
            $back_order_data['qty_ordered']  = implode(",", $product_qtys);
            $back_order_data['status']  = 'New';
            $backOrderHelper->setBackorderData($back_order_data);
            $order->setIsBackeorderedItems($order_status);
            $order->save();
        }
    }


    
	 public function createInvoiceCron($orderId)
	{
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/split_order_failed.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		
		$order = $this->_orderRepository->get($orderId);
		$message = 'fail';
	    //if (!$order->hasInvoices()) {
		if($order->canInvoice()) {
			//$invoice = $order->prepareInvoice();
			$invoice = $this->invoiceService->prepareInvoice($order);
			$invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
			$invoice->register();
			$invoice->getOrder()->setCustomerNoteNotify(false);
			$invoice->getOrder()->setIsInProcess(true);
			$order->addCommentToStatusHistory(__('Cron Automatically INVOICED'), false);
			$transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
			/*if ($order->getShipmentsCollection()->count()) {
				$order->setState('complete')->setStatus('complete');
				$order->addStatusToHistory($order::STATE_COMPLETE, 'Order has been paid.', true);
			} else {
				$order->setState($order::STATE_PROCESSING)->save();
				$order->setStatus($order::STATE_PROCESSING)->save();
				$order->addStatusToHistory($order::STATE_PROCESSING, 'Order has been paid.', true);
			}
			*/
			$logger->info('Order Final Shipping Amount ' . $order->getShippingAmount());
			//$order->setTotalPaid($order->getGrandTotal());
			//$order->setBaseTotalPaid($order->getBaseGrandTotal());
			//$this->_orderRepository->save($order);
			$message = 'success';
		}
		return $message;
	}

	public function generateInvoice($order)
    {
        $this->logger->info("Currently this working");
        $orderId = $order->getId(); //order id for which want to create invoice
        $return = 'fail';
        $this->logger->info("invoice Order_id" . $orderId);
        $order = $this->_orderRepository->get($orderId);
        if ($order->canInvoice()) {
            $this->logger->info("inside can invoice Order_id" . $orderId);
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register()->pay();
            $invoice->setState(2);
            $invoice->save();
            $transactionSave = $this->transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
            $this->logger->info("inside can invoice After trans save" . $orderId);
            // $this->invoiceSender->send($invoice);
            //send notification code
            $order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId())
            )
                ->setIsCustomerNotified(true)
                ->save();
            $return = 'success';
        }

        return $return;
    }

    public function IsSplitOrder($order_id)
    {
        $result = false;
        $order = $this->order->load($order_id);
        $store = $order->getStore()->getCode();
        $orderItems = $order->getAllVisibleItems();
        $produtcount = count($orderItems);
        if ($produtcount > 1 && $store == 'schools') {
            $result = true;
        }
        return $result;
    }
    /**
     * Create Order On Your Store
     *
     * @param array $orderData orderdata
     *
     * @return array
     */
    public function createMageOrderCron($orderData)
    {
        try {

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/split_order_failedcron.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);

			$store_id = $orderData['store_id'];
            $store = $orderData['store'];
			$customer_id = $orderData['customer_id'];
			$quote_id = $orderData['quote_id'];

			foreach ($orderData['items'] as $item) {
				$productId[] = $item['product_id'];
			}
			$logger->info('product_id ' . $productId[0]);
			$logger->info('store_id ' . $store_id);
            $logger->info('getCustomerId ' . $customer_id);
			$logger->info('getQuoteId ' . $quote_id);
            $customer = $this->customerRepository->getById($customer_id);
            $logger->info('After customer');

            $obj = \Magento\Framework\App\ObjectManager::getInstance();
            $checkoutsession = $obj->get('Magento\Checkout\Model\Session');
            $cartId = $this->cartManagementInterface->createEmptyCart();

            //if ($store_id == 3) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $productFactory = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
                $products = $productFactory->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id', ['eq' => $productId[0]])
                    ->setStore($store);
                $data = $products->getFirstItem();
                $catData = $data->getData();
                // product school id from cart
                $schoolId = $catData['school_name'];
                $product_purchased = $catData['name'];

                $schoolCollection = $this->collectionFactory->create();
                $filter = $schoolCollection->getCollection()
                    ->addFieldToFilter('school_name', $schoolId);
                $schoolFilterData =  $filter->getFirstItem()->getData();
				$studentName = '';
			    $rollNo = '';
                
				$parent_quote_data = $this->quoteRepository->get($quote_id);
				foreach ($parent_quote_data->getAllVisibleItems() as $quoteItem) {
					$logger->info("quote item data : ". $quoteItem->getData('name')." ".$quoteItem->getProduct()->getTypeId()." ".$quoteItem->getData('student_name')." ".$quoteItem->getData('roll_no'));
                    if($quoteItem->getProduct()->getTypeId() == "bundle" && $quoteItem->getData('name') == $product_purchased){
					//if($quoteItem->getProduct()->getTypeId() == "bundle") {
					   $studentName = $quoteItem->getData('student_name');
					   $rollNo = $quoteItem->getData('roll_no');
					   $logger->info("Stundent name and roll no ". $studentName." ".$rollNo);
					}

				}

                $school_id = $schoolFilterData['school_name'];
                $school_name = $schoolFilterData['school_name_text'];
                $school_code = $schoolFilterData['school_code'];
                $location_code = $schoolFilterData['location_code'];

                if (($school_id != '') && ($school_code != '') && ($product_purchased != '')) {
                    //echo $nquoteId = $checkoutsession->getQuote()->getId();
                    $quote = $this->quoteRepository->get($cartId); // Get quote by id
                    $quote->setData('student_name', $studentName);
                    $quote->setData('roll_no', $rollNo);
                    $quote->setData('school_id', $school_id);
                    $quote->setData('school_name', $school_name);
                    $quote->setData('school_code', $school_code);
                    $quote->setData('location_code', $location_code);
                    $quote->setData('customer_is_guest', '0');
                    $quote->setData('product_purchased', $product_purchased);
                    $this->quoteRepository->save($quote);
                }
            //}

            $quote = $this->cartRepositoryInterface->get($cartId);
            $checkoutsession->replaceQuote($quote);
            $quote->setStore($store);
            $quote->setStoreId($store_id);
            // if you have allready buyer id then you can load customer directly
            //$customer= $this->customerRepository->getById($customer->getEntityId());
            $quote->setCurrency();
            $quote->assignCustomer($customer); //Assign quote to customer
            $logger->info('After Assign Customer');

            //add items in quote
            $i = 0;
            $order_items = [];

            foreach ($orderData['items'] as $item) {
                $product = $this->product->create()->load($item['product_id']);
                if (array_key_exists("product_options", $item)) {
                    $requestInfo = new \Magento\Framework\DataObject(
                        [
                            'qty' => (int) $item['qty'],
                            'product' => $item['product_id']
                        ]
                    );
                    if (isset($item['product_options']['super_attribute'])) {
                        $requestInfo['super_attribute'] = $item['product_options']['super_attribute'];
                    }
                    if (isset($item['product_options']['bundle_option'])) {
                        $requestInfo['bundle_option'] = $item['product_options']['bundle_option'];
                    }
                    if (isset($item['product_options']['bundle_option_qty'])) {
                        $requestInfo['bundle_option_qty'] = $item['product_options']['bundle_option_qty'];
                    }
                    if (isset($item['product_options']['bundle_selection_attributes'])) {
                        $requestInfo['bundle_selection_attributes'] = $item['product_options']['bundle_selection_attributes'];
                    }
                    if (isset($item['product_options']['links'])) {
                        $requestInfo['links'] = $item['product_options']['links'];
                    }
                    $quote->addProduct($product, $requestInfo);
                } else {
                    $quote->addProduct($product, (int) $item['qty']);
                }
                $order_items[$item['product_id']]['price'] = $item['price'];
                $order_items[$item['product_id']]['original_price'] = $item['original_price'];
                $order_items[$item['product_id']]['applied_rule_ids'] = $item['applied_rule_ids'];
                $order_items[$item['product_id']]['discount_percent'] = $item['discount_percent'];
                $order_items[$item['product_id']]['discount_amount'] = $item['discount_amount'];
                $order_items[$item['product_id']]['tax_percent'] = $item['tax_percent'];
                $order_items[$item['product_id']]['tax_amount'] = $item['tax_amount'];
                $order_items[$item['product_id']]['optional_selected_items'] = $item['optional_selected_items'];
				$order_items[$item['product_id']]['given_options'] = $item['given_options'];
				$order_items[$item['product_id']]['given_option_updated_at'] = $item['given_option_updated_at'];
				$order_items[$item['product_id']]['given_options_msg'] = $item['given_options_msg'];
                $i++;
            }
            $logger->info('After Added Items');
            //Set Address to quote
            if (isset($orderData['shipping_address'])) {
                $quote->getShippingAddress()->addData($orderData['shipping_address']);
                //Set Shipping Method
                $shippingAddress = $quote->getShippingAddress();
                $shippingAddress->setCollectShippingRates(true)
                    ->collectShippingRates()
                    ->setShippingMethod($orderData['shipping_method']);
            }
            $quote->getBillingAddress()->addData($orderData['billing_address']);
            $logger->info('After billing details');

            // Collect Rates and Set Payment Method
            $quote->setPaymentMethod($orderData['payment_method']); //payment method
            $quote->setInventoryProcessed(false); //not effetc inventory

			$quote->save();

            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $serializer = $om->get(\Magento\Framework\Serialize\Serializer\Json::class);
			$targetProductId = isset($productId[0]) ? (int)$productId[0] : 0;
			foreach ($quote->getAllItems() as $itemvalue) {

				// skip the parent bundle item
				if ($itemvalue->getProductType() === 'bundle') {
					continue;
				}

				// default
				$optionId = null;

				// Try to read bundle_selection_attributes option (safe)
				$bundleAttrOption = $itemvalue->getOptionByCode('bundle_selection_attributes');
				if ($bundleAttrOption && $bundleAttrOption->getValue()) {
					try {
						$attrs = $serializer->unserialize($bundleAttrOption->getValue());
						if (isset($attrs['option_id'])) {
							$optionId = (int) $attrs['option_id'];
						}
					} catch (\Exception $e) {
						// log if you want
						$this->logger->info('Error unserializing bundle_selection_attributes: ' . $e->getMessage());
						$optionId = null;
					}
				}

				// set split parent and bundle option id on the child item
				$itemvalue->setSplitParentItemId($targetProductId);
				$itemvalue->setData('bundle_option_id', $optionId);
				$itemvalue->save();
			}

			

        

            // Set Sales Order Payment
            $quote->getPayment()->importData(['method' => $orderData['payment_method']]);
            $quote->setTotalsCollectedFlag(false)->collectTotals();

			    //will be given code
			$parent_quote_data = $this->quoteRepository->get($quote_id);

			// Create a lookup map for faster access
			$parentItemsMap = [];
			foreach ($parent_quote_data->getAllItems() as $val) {

				// Read bundle_option_id from parent quote item
				$bundleOptionId = (int)$val->getData('bundle_option_id'); // default 0 if null
				//$logger->info('parent bundleOptionId ' . $bundleOptionId);
                //$logger->info('parent getSplitParentItemId ' . $val->getSplitParentItemId());
				$key = $val->getProductId()
					 . '_' . $val->getSplitParentItemId()
					 . '_' . $bundleOptionId;

				$parentItemsMap[$key] = [
					'given_options'     => $val->getData('given_options'),
					'given_options_msg' => $val->getData('given_options_msg'),
				];
			}

			// Single loop through current quote items
			
			foreach ($quote->getAllItems() as $quoteitem) {

				$bundleOptionId = (int)$quoteitem->getData('bundle_option_id'); // must already be set earlier
                //$logger->info('bundleOptionId ' . $bundleOptionId);
				//$logger->info('getSplitParentItemId ' . $quoteitem->getSplitParentItemId());
				$key = $quoteitem->getProductId()
					 . '_' . $quoteitem->getSplitParentItemId()
					 . '_' . $bundleOptionId;

				if (isset($parentItemsMap[$key])) {
					$quoteitem->setData('given_options', $parentItemsMap[$key]['given_options']);
					$quoteitem->setData('given_options_msg', $parentItemsMap[$key]['given_options_msg']);
					//$quoteitem->save();
				}
			}

			$this->quoteRepository->save($quote);

            $ordershippingAmount = $orderData['shipping_amount'];
			$logger->info('OrdershippingAmount ' . $ordershippingAmount);
            $checkoutsession->setSplitShipAmount($orderData['shipping_amount']);
            $quote->setShippingAmount($ordershippingAmount);
            $quote->save();

            $optional_selected_items = '';
            $optional_selected_items = $this->getBundleProductOptionsData($productId[0]);

            //$quoteId = $this->_checkoutSession->getQuote()->getId();
            //$logger->info('quoteId '.$quoteId);
            $logger->info('bundle_optional_selected_items ' . $optional_selected_items);

            //if(!empty($quoteId)) {
            //$quote = $this->quoteRepository->get($quoteId);
				foreach ($quote->getAllVisibleItems() as $itemq) {
					$item_product_id = $itemq->getProductId();
					if ($item_product_id == $productId[0]) {
						$itemq->setOptionalSelectedItems($optional_selected_items);
						$itemq->save();
					}
				}
            //}

            $logger->info('After submit quote ' . 'quote id ' . $quote->getId());
			$logger->info('After get Shipping Amount ' . $quote->getShippingAmount());
            $orderId = $this->cartManagementInterface->placeOrder($quote->getId());
			$logger->info('After order load');
            $logger->info('After submit quote ' . 'orderId ' . $orderId);
            // $order = $this->quoteManagement->submit($quote);
            $order = $this->order->load($orderId);
            $logger->info('After Customer id ' . $order->getCustomerId());
            //$order->setCustomerIsGuest(0);
            $checkoutsession->unsSplitShipAmount();
            $orderDiscount = 0;
            $orderTax = 0;
            $orderSubtotal = 0;

            $orderItems = $order->getAllVisibleItems();
            foreach ($orderItems as $orderItem) {
                $orderItem->setOriginalPrice($order_items[$orderItem->getProductId()]['original_price']);
                $orderItem->setPrice($order_items[$orderItem->getProductId()]['price']);
                $orderItem->setBasePrice($order_items[$orderItem->getProductId()]['price']);
                $orderItem->setRowTotal($order_items[$orderItem->getProductId()]['price'] * $orderItem->getQtyOrdered());
                $orderItem->setBaseRowTotal($order_items[$orderItem->getProductId()]['price'] * $orderItem->getQtyOrdered());
                $orderItem->setDiscountPercent($order_items[$orderItem->getProductId()]['discount_percent']);
                $orderItem->setDiscountAmount($order_items[$orderItem->getProductId()]['discount_amount']);
                $orderItem->setTaxPercent($order_items[$orderItem->getProductId()]['tax_percent']);
                $orderItem->setTaxAmount($order_items[$orderItem->getProductId()]['tax_amount']);
                $bundle_product_id = $orderItem->getProductId();
                $logger->info('orderItems_optional_selected_items ' . $order_items[$orderItem->getProductId()]['optional_selected_items']);
                $final_optional_selected_items = $bundle_product_id . ',' . $order_items[$orderItem->getProductId()]['optional_selected_items'];
                $logger->info('bundle_product_id ' . $bundle_product_id);
                $orderItem->setOptionalSelectedItems($final_optional_selected_items);
                $orderItem->save();
                $orderDiscount -= $order_items[$orderItem->getProductId()]['discount_amount'];
                $orderTax += $order_items[$orderItem->getProductId()]['tax_amount'];
                $orderSubtotal += $orderItem->getRowTotal();

                /*$quoteRepository = $this->quoteRepository;
                $quote = $quoteRepository->get($order->getQuoteId());
				$logger->info('Quote Id '.$order->getQuoteId());
				$allItems = $quote->getAllVisibleItems();
				$product_type = $orderItem->getProductType();
				if($product_type == 'bundle') {
					$bundle_product_id = $orderItem->getProductId();
					$allItemsCount = count($allItems);
					if($allItemsCount > 0) {
						$optional_selected_items = '';
						foreach($allItems as $quote_item) {
							$product_type = $quote_item->getProductType();
							$quote_item_product_id = $quote_item->getProductId();
							if($product_type == 'bundle' && $quote_item_product_id == $bundle_product_id) {
							   $optional_selected_items = $quote_item->getOptionalSelectedItems();
							   $logger->info('optional_selected_items '.$optional_selected_items);
							}
						}
					}
					$final_optional_selected_items = $bundle_product_id.','.$optional_selected_items;
					$orderItem->setOptionalSelectedItems($final_optional_selected_items);
					continue;
				}*/
            }
            //$ordershippingAmount = $orderData['shipping_amount'] * $order->getTotalQtyOrdered();
            $order->setShippingAmount($ordershippingAmount);
            $order->setSubTotal($orderSubtotal);
            $order->setTaxAmount($orderTax);
			$logger->info('Order Shipping Amount ' . $order->getShippingAmount());
            $orderTotal = $order->getSubTotal() + $orderDiscount + $order->getShippingAmount() + $orderTax;
			$logger->info('orderTotal ' . $orderTotal);
            $order->setBaseGrandTotal($orderTotal);
            $order->setGrandTotal($orderTotal);
            $order->setBaseTotalDue($orderTotal);
            $order->setTotalDue($orderTotal);
            $order->setDiscountAmount($orderDiscount);
            $order->setBaseDiscountAmount($orderDiscount);
            $order->setDiscountDescription($orderData['discount_description']);
            $order->setCouponCode($orderData['coupon_code']);
            $order->setCouponRuleName($orderData['coupon_rule_name']);
			$order->setData('dispatch_status', 'not_confirmed');
			$order->setData('delivery_status', 'not_confirmed');
			$quote_data = $this->quoteRepository->get($quote->getId());
			if($quote_data) {
				$order->setData('roll_no', $quote_data->getRollNo());
				$order->setData('student_name', $quote_data->getStudentName());
				$order->setData('school_id', $quote_data->getSchoolId()); 
				$order->setData('school_name', $quote_data->getSchoolName()); 
				$order->setData('school_code', $quote_data->getSchoolCode());
				$order->setData('location_code', $quote_data->getLocationCode()); 
				$order->setData('product_purchased', $quote_data->getProductPurchased());
			}
            $order->save();
            $logger->info('After all setting up datas');
            $logger->info('Invoice before ' . $orderData['order_status'] . ' shipping amount ' . $order->getShippingAmount());
            //if ($orderData['order_status'] == 'processing') {
				//$asso_orderId = $order->getId();
               //$this->createInvoiceCron($asso_orderId);
            //}
			$message = 'fail';
			//if (!$order->hasInvoices()) {
			if($order->canInvoice()) {
				//$invoice = $order->prepareInvoice();
				$invoice = $this->invoiceService->prepareInvoice($order);
				$invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
				$invoice->register();
				$invoice->getOrder()->setCustomerNoteNotify(false);
				$invoice->getOrder()->setIsInProcess(true);
				$order->addCommentToStatusHistory(__('Cron Automatically INVOICED'), false);
				$transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
				$transactionSave->save();
				$logger->info('Order Final Shipping Amount ' . $order->getShippingAmount());
				$message = 'success';
			}

            if ($order->getEntityId()) {
                //$this->updateBackorder($order->getEntityId());
                $result['order_id'] = $order->getEntityId();
                $result['status'] = $order->getStatus();
                $result['increment_id'] = $order->getRealOrderId();
                $result['quote_id'] = $checkoutsession->getQuote()->getId();

                $checkoutsession->clearQuote();
            } else {
                $result = ['error' => 1, 'msg' => 'Can not create order'];
            }
            return $result;
        } catch (\Exception $e) {
            $result = ['error' => 1, 'msg' => 'Can not create order ' . $e->getMessage()];
            return $result;
        }
    }

	/**
     * Create Order On Your Store
     *
     * @param array $orderData orderdata
     *
     * @return array
     */
    public function createMageOrder($orderData)
    {
        try {

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/split_order_final.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);

            $store = $this->storeManager->getStore();
            $store_id = $store->getStoreId();
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $customer = $this->customerRepository->getById($this->customerSession->getCustomer()->getId());
            $logger->info('After customer');

            $obj = \Magento\Framework\App\ObjectManager::getInstance();
            $checkoutsession = $obj->get('Magento\Checkout\Model\Session');
            $cartId = $this->cartManagementInterface->createEmptyCart();

            if ($this->getWebsiteCode() == 'schools') {
                foreach ($orderData['items'] as $item) {
                    $productId[] = $item['product_id'];
                }

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $productFactory = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
                $products = $productFactory->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id', ['eq' => $productId[0]])
                    ->setStore($this->storeManager->getStore()); //categories from current store will be fetched


                $data = $products->getFirstItem();
                $catData = $data->getData();
                // product school id from cart
                $schoolId = $catData['school_name'];
                $product_purchased = $catData['name'];

                $schoolCollection = $this->collectionFactory->create();
                $filter = $schoolCollection->getCollection()
                    ->addFieldToFilter('school_name', $schoolId);
                $schoolFilterData =  $filter->getFirstItem()->getData();

                $student_name = $this->_request->getPost('student_pname');
                $student_number = $this->_request->getPost('student_pid');
                $school_id = $schoolFilterData['school_name'];
                $school_name = $schoolFilterData['school_name_text'];
                $school_code = $schoolFilterData['school_code'];
                $location_code = $schoolFilterData['location_code'];

                if (($school_id != '') && ($school_code != '') && ($product_purchased != '')) {
                    //echo $nquoteId = $checkoutsession->getQuote()->getId();
                    $quote = $this->quoteRepository->get($cartId); // Get quote by id
                    $quote->setData('student_name', $student_name);
                    $quote->setData('roll_no', $student_number);
                    $quote->setData('school_id', $school_id);
                    $quote->setData('school_name', $school_name);
                    $quote->setData('school_code', $school_code);
                    $quote->setData('location_code', $location_code);
                    $quote->setData('customer_is_guest', '0');
                    $quote->setData('product_purchased', $product_purchased);
                    $this->quoteRepository->save($quote);
                }

					$quote_id = $orderData['quote_id'];
					$logger->info('getQuoteId ' . $quote_id);

            }

            $quote = $this->cartRepositoryInterface->get($cartId);
            $checkoutsession->replaceQuote($quote);
            $quote->setStore($store);
            $quote->setStoreId($store_id);
            // if you have allready buyer id then you can load customer directly
            //$customer= $this->customerRepository->getById($customer->getEntityId());
            $quote->setCurrency();
            $quote->assignCustomer($customer); //Assign quote to customer
            $logger->info('After Assign Customer');

            //add items in quote
            $i = 0;
            $order_items = [];

            foreach ($orderData['items'] as $item) {
                $product = $this->product->create()->load($item['product_id']);
                if (array_key_exists("product_options", $item)) {
                    $requestInfo = new \Magento\Framework\DataObject(
                        [
                            'qty' => (int) $item['qty'],
                            'product' => $item['product_id']
                        ]
                    );
                    if (isset($item['product_options']['super_attribute'])) {
                        $requestInfo['super_attribute'] = $item['product_options']['super_attribute'];
                    }
                    if (isset($item['product_options']['bundle_option'])) {
                        $requestInfo['bundle_option'] = $item['product_options']['bundle_option'];
                    }
                    if (isset($item['product_options']['bundle_option_qty'])) {
                        $requestInfo['bundle_option_qty'] = $item['product_options']['bundle_option_qty'];
                    }
                    if (isset($item['product_options']['bundle_selection_attributes'])) {
                        $requestInfo['bundle_selection_attributes'] = $item['product_options']['bundle_selection_attributes'];
                    }
                    if (isset($item['product_options']['links'])) {
                        $requestInfo['links'] = $item['product_options']['links'];
                    }
                    $quote->addProduct($product, $requestInfo);
                } else {
                    $quote->addProduct($product, (int) $item['qty']);
                }
                $order_items[$item['product_id']]['price'] = $item['price'];
                $order_items[$item['product_id']]['original_price'] = $item['original_price'];
                $order_items[$item['product_id']]['applied_rule_ids'] = $item['applied_rule_ids'];
                $order_items[$item['product_id']]['discount_percent'] = $item['discount_percent'];
                $order_items[$item['product_id']]['discount_amount'] = $item['discount_amount'];
                $order_items[$item['product_id']]['tax_percent'] = $item['tax_percent'];
                $order_items[$item['product_id']]['tax_amount'] = $item['tax_amount'];
                $order_items[$item['product_id']]['optional_selected_items'] = $item['optional_selected_items'];
				$order_items[$item['product_id']]['given_options'] = $item['given_options'];
				$order_items[$item['product_id']]['given_option_updated_at'] = $item['given_option_updated_at'];
				$order_items[$item['product_id']]['given_options_msg'] = $item['given_options_msg'];
                $i++;
            }
            $logger->info('After Added Items');
            //Set Address to quote
            if (isset($orderData['shipping_address'])) {
                $checkoutsession->setSplitShipAmount($orderData['shipping_amount']);
                $quote->getShippingAddress()->addData($orderData['shipping_address']);
                //Set Shipping Method
                $shippingAddress = $quote->getShippingAddress();
                $shippingAddress->setCollectShippingRates(true)
                    ->collectShippingRates()
                    ->setShippingMethod($orderData['shipping_method']);
            }
            $quote->getBillingAddress()->addData($orderData['billing_address']);

            $logger->info('After billing details');

            // Collect Rates and Set Payment Method
            $quote->setPaymentMethod($orderData['payment_method']); //payment method
            $quote->setInventoryProcessed(false); //not effetc inventory
            $quote->save();

            $logger->info('product_id ' . $productId[0]);
            $optional_selected_items = '';
            $optional_selected_items = $this->getBundleProductOptionsData($productId[0]);

            //$quoteId = $this->_checkoutSession->getQuote()->getId();
            //$logger->info('quoteId '.$quoteId);
            $logger->info('bundle_optional_selected_items ' . $optional_selected_items);

            $bundle_itemId = null;
			foreach ($quote->getAllVisibleItems() as $itemq) {
				if ($itemq->getProductId() == $productId[0]) {
					$bundle_itemId = $itemq->getItemId(); // parent item_id
					$itemq->setOptionalSelectedItems($optional_selected_items);
					$itemq->save();
				}
			}

            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $serializer = $om->get(\Magento\Framework\Serialize\Serializer\Json::class);
			$targetProductId = isset($productId[0]) ? (int)$productId[0] : 0;
			foreach ($quote->getAllItems() as $itemvalue) {

				// skip the parent bundle item
				if ($itemvalue->getProductType() === 'bundle') {
					continue;
				}

				// default
				$optionId = null;

				// Try to read bundle_selection_attributes option (safe)
				$bundleAttrOption = $itemvalue->getOptionByCode('bundle_selection_attributes');
				if ($bundleAttrOption && $bundleAttrOption->getValue()) {
					try {
						$attrs = $serializer->unserialize($bundleAttrOption->getValue());
						if (isset($attrs['option_id'])) {
							$optionId = (int) $attrs['option_id'];
						}
					} catch (\Exception $e) {
						// log if you want
						$this->logger->info('Error unserializing bundle_selection_attributes: ' . $e->getMessage());
						$optionId = null;
					}
				}

				// set split parent and bundle option id on the child item
				$itemvalue->setSplitParentItemId($targetProductId);
				$itemvalue->setData('bundle_option_id', $optionId);
				$itemvalue->save();
			}

			$this->quoteRepository->save($quote);

			/*$parent_quote_data = $this->quoteRepository->get($quote_id);

			// Create a lookup map for faster access
			$parentItemsMap = [];
			foreach ($parent_quote_data->getAllItems() as $val) {
				$key = $val->getProductId() . '_' . $val->getSplitParentItemId();
				$parentItemsMap[$key] = [
					'given_options' => $val->getData('given_options'),
					'given_options_msg' => $val->getData('given_options_msg')
				];
			}

			// Single loop through current quote items
			foreach ($quote->getAllItems() as $quoteitem) {
				$key = $quoteitem->getProductId() . '_' . $quoteitem->getSplitParentItemId();
				
				if (isset($parentItemsMap[$key])) {
					$quoteitem->setData('given_options', $parentItemsMap[$key]['given_options']);
					$quoteitem->setData('given_options_msg', $parentItemsMap[$key]['given_options_msg']);
					$quoteitem->save();
				}
			}*/

			$parent_quote_data = $this->quoteRepository->get($quote_id);

			// Create a lookup map for faster access
			$parentItemsMap = [];
			foreach ($parent_quote_data->getAllItems() as $val) {

				// Read bundle_option_id from parent quote item
				$bundleOptionId = (int)$val->getData('bundle_option_id'); // default 0 if null

				$key = $val->getProductId()
					 . '_' . $val->getSplitParentItemId()
					 . '_' . $bundleOptionId;

				$parentItemsMap[$key] = [
					'given_options'     => $val->getData('given_options'),
					'given_options_msg' => $val->getData('given_options_msg'),
				];
			}

			// Single loop through current quote items
			foreach ($quote->getAllItems() as $quoteitem) {

				$bundleOptionId = (int)$quoteitem->getData('bundle_option_id'); // must already be set earlier

				$key = $quoteitem->getProductId()
					 . '_' . $quoteitem->getSplitParentItemId()
					 . '_' . $bundleOptionId;

				if (isset($parentItemsMap[$key])) {
					$quoteitem->setData('given_options', $parentItemsMap[$key]['given_options']);
					$quoteitem->setData('given_options_msg', $parentItemsMap[$key]['given_options_msg']);
					$quoteitem->save();
				}
			}
           


            // Set Sales Order Payment
            $quote->getPayment()->importData(['method' => $orderData['payment_method']]);
            $logger->info('After Quote payment');
            $quote->setTotalsCollectedFlag(false)->collectTotals();

            $ordershippingAmount = $orderData['shipping_amount'];
            $checkoutsession->setSplitShipAmount($orderData['shipping_amount']);
            $quote->setShippingAmount($ordershippingAmount);
            $quote->save();
            $logger->info('After get Shipping Amount' . $quote->getShippingAmount());
            $orderId = $this->cartManagementInterface->placeOrder($quote->getId());
            // $order = $this->quoteManagement->submit($quote);

            $logger->info('After submit quote');
            $order = $this->order->load($orderId);
            $logger->info('After Place Order- Shipping Amount' . $order->getShippingAmount());
            $logger->info('After order load');
            $logger->info('After Customer id' . $order->getCustomerId());
            //$order->setCustomerIsGuest(0);
            $checkoutsession->unsSplitShipAmount();
            $orderDiscount = 0;
            $orderTax = 0;
            $orderSubtotal = 0;

            $orderItems = $order->getAllVisibleItems();
            foreach ($orderItems as $orderItem) {
                $orderItem->setOriginalPrice($order_items[$orderItem->getProductId()]['original_price']);
                $orderItem->setPrice($order_items[$orderItem->getProductId()]['price']);
                $orderItem->setBasePrice($order_items[$orderItem->getProductId()]['price']);
                $orderItem->setRowTotal($order_items[$orderItem->getProductId()]['price'] * $orderItem->getQtyOrdered());
                $orderItem->setBaseRowTotal($order_items[$orderItem->getProductId()]['price'] * $orderItem->getQtyOrdered());
                $orderItem->setDiscountPercent($order_items[$orderItem->getProductId()]['discount_percent']);
                $orderItem->setDiscountAmount($order_items[$orderItem->getProductId()]['discount_amount']);
                $orderItem->setTaxPercent($order_items[$orderItem->getProductId()]['tax_percent']);
                $orderItem->setTaxAmount($order_items[$orderItem->getProductId()]['tax_amount']);
                $bundle_product_id = $orderItem->getProductId();
                $logger->info('orderItems_optional_selected_items ' . $order_items[$orderItem->getProductId()]['optional_selected_items']);
                $final_optional_selected_items = $bundle_product_id . ',' . $order_items[$orderItem->getProductId()]['optional_selected_items'];
                $logger->info('bundle_product_id ' . $bundle_product_id);
                $orderItem->setOptionalSelectedItems($final_optional_selected_items);
                $orderItem->save();
                $orderDiscount -= $order_items[$orderItem->getProductId()]['discount_amount'];
                $orderTax += $order_items[$orderItem->getProductId()]['tax_amount'];
                $orderSubtotal += $orderItem->getRowTotal();

                /*$quoteRepository = $this->quoteRepository;
                $quote = $quoteRepository->get($order->getQuoteId());
				$logger->info('Quote Id '.$order->getQuoteId());
				$allItems = $quote->getAllVisibleItems();
				$product_type = $orderItem->getProductType();
				if($product_type == 'bundle') {
					$bundle_product_id = $orderItem->getProductId();
					$allItemsCount = count($allItems);
					if($allItemsCount > 0) {
						$optional_selected_items = '';
						foreach($allItems as $quote_item) {
							$product_type = $quote_item->getProductType();
							$quote_item_product_id = $quote_item->getProductId();
							if($product_type == 'bundle' && $quote_item_product_id == $bundle_product_id) {
							   $optional_selected_items = $quote_item->getOptionalSelectedItems();
							   $logger->info('optional_selected_items '.$optional_selected_items);
							}
						}
					}
					$final_optional_selected_items = $bundle_product_id.','.$optional_selected_items;
					$orderItem->setOptionalSelectedItems($final_optional_selected_items);
					continue;
				}*/
            }
            $ordershippingAmount = $orderData['shipping_amount'] * $order->getTotalQtyOrdered();
            $order->setShippingAmount($ordershippingAmount);
            $order->setSubTotal($orderSubtotal);
            $order->setTaxAmount($orderTax);
            $orderTotal = $order->getSubTotal() + $orderDiscount + $order->getShippingAmount() + $orderTax;
            $order->setBaseGrandTotal($orderTotal);
            $order->setGrandTotal($orderTotal);
            $order->setBaseTotalDue($orderTotal);
            $order->setTotalDue($orderTotal);
            $order->setDiscountAmount($orderDiscount);
            $order->setBaseDiscountAmount($orderDiscount);
            $order->setDiscountDescription($orderData['discount_description']);
            $order->setCouponCode($orderData['coupon_code']);
            $order->setCouponRuleName($orderData['coupon_rule_name']);
			$order->setData('dispatch_status', 'not_confirmed');
			$order->setData('delivery_status', 'not_confirmed');
            $order->save();
            $logger->info('After all setting up datas');
            $logger->info('Invoice before' . $orderData['order_status'] . ' shipping amount' . $order->getShippingAmount());
            if ($orderData['order_status'] == 'processing') {
                $this->generateInvoice($order);
            }

            // $order->setEmailSent(0);
            // $ordershippingAmount = $orderData['shipping_amount'] * $order->getTotalQtyOrdered();
            // $order->setShippingAmount($ordershippingAmount);
            // $order->save();
            $order->getRealOrderId();
            if ($order->getEntityId()) {
                $this->updateBackorder($order->getEntityId());
                $result['order_id'] = $order->getEntityId();
                $result['status'] = $order->getStatus();
                $result['increment_id'] = $order->getRealOrderId();
                $result['quote_id'] = $checkoutsession->getQuote()->getId();

                $checkoutsession->clearQuote();
            } else {
                $result = ['error' => 1, 'msg' => 'Can not create order'];
            }
            return $result;
        } catch (\Exception $e) {
            $result = ['error' => 1, 'msg' => 'Can not create order ' . $e->getMessage()];
            return $result;
        }
    }


    public function getWebsiteCode()
    {
        return $this->storeManager->getWebsite()->getCode();
    }

	public function getBundleProductGivenData($parent_product_id)
	{
		$selection_result = [];

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->create('Magento\Catalog\Model\Product')->load($parent_product_id);

		$selectionCollection = $product->getTypeInstance(true)
			->getSelectionsCollection(
				$product->getTypeInstance(true)->getOptionsIds($product),
				$product
			);

		foreach ($selectionCollection as $proselection) {
			$selection_result[] = [
				'selection_product_id' => $proselection->getProductId(),
				'custom_field'         => $proselection->getCustomField() ?? 0,
				'updated_at'           => $proselection->getUpdatedAt(),
				'option_id'            => $proselection->getOptionId(),
				'option_title'         => $proselection->getData('default_title'),
			];
		}

		return $selection_result;
	}

    public function getBundleProductOptionsData($parent_product_id)
    {
        
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/split_order_failed.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		//$logger->info('Session Array Log'.print_r($session->getData(), true));
		$logger->info('getBundleProductOptionsData start parent_product_id '. $parent_product_id);
 
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $catalog_product_bundle_option_table = $resource->getTableName('catalog_product_bundle_option');
        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($parent_product_id);
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        foreach ($selectionCollection as $proselection) {
            $selection_product_id = $proselection->getProductId();
			//$logger->info('selection_product_id '. $selection_product_id);
            $selection_option_id = $proselection->getOptionId();
            $selection_is_default = $proselection->getIsDefault();
            $bundle_option_sql = "select required from " . $catalog_product_bundle_option_table . " where parent_id = " . $parent_product_id . " AND option_id = " . $selection_option_id;
			//$logger->info('bundle_option_sql '. $bundle_option_sql);
            $bundle_option_result = $connection->fetchRow($bundle_option_sql);
            $option_required = $bundle_option_result['required'];
            if ($selection_is_default == true && $option_required == true) {
                $selection_product_ids[] = $selection_product_id;
            }
        }
		if(!empty($selection_product_ids)) {
            $optional_selected_items = implode(",", $selection_product_ids);
		} else {
			 $optional_selected_items = '';
		}

        return $optional_selected_items;
    }
}
