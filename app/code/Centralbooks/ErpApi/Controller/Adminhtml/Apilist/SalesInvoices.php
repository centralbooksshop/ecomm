<?php
declare(strict_types=1);

namespace Centralbooks\ErpApi\Controller\Adminhtml\Apilist;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Centralbooks\ErpApi\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class SalesInvoices implements HttpGetActionInterface
{
    const ERP_INVOICE_PREVIOUS_DATE = 'erp/erpapicredential/invoicepreviousdate';

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var Json */
    protected $serializer;

    /** @var LoggerInterface */
    protected $logger;

    /** @var Http */
    protected $http;

    /** @var Data */
    protected $helper;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var ResourceConnection */
    private $resource;

    /** @var ManagerInterface */
    private $_messageManager;

    /** @var OrderCollectionFactory */
    protected $orderCollectionFactory;

    /** @var TimezoneInterface */
    protected $timezoneInterface;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var RedirectFactory */
    protected $resultRedirectFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ManagerInterface $messageManager
     * @param Json $json
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param Data $data
     * @param StoreManagerInterface $storeManager
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param TimezoneInterface $timezoneInterface
     * @param ProductRepositoryInterface $productRepository
     * @param ResourceConnection $resource
     * @param Http $http
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ManagerInterface $messageManager,
        Json $json,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        Data $data,
        StoreManagerInterface $storeManager,
        OrderCollectionFactory $orderCollectionFactory,
        TimezoneInterface $timezoneInterface,
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resource,
        Http $http
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_messageManager = $messageManager;
        $this->serializer = $json;
        $this->logger = $logger;
        $this->helper = $data;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->productRepository = $productRepository;
        $this->resource = $resource;
        $this->http = $http;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            if (! $this->helper->erpEnable()) {
                $this->_messageManager->addNoticeMessage('ERP integration is disabled.');
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setRefererOrBaseUrl();
            }

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            $ordersCollection = $this->getOrderCollectionByDateRange();

            $recordsItem = [];
            $incrementId = null;
            $sourceCode = '';

            if ($ordersCollection->getSize() > 0) {
                foreach ($ordersCollection as $order) {
                $incrementId = $order->getIncrementId();
				$paymentMethodCode = '';
				$payment = $order->getPayment();
				if ($payment) {
					$methodInstance = $payment->getMethodInstance();
					$paymentMethodCode = $methodInstance ? $methodInstance->getCode() : (string)$payment->getMethod();
				}

				$webInvoiceDate = $this->timezoneInterface->date(new \DateTime($order->getCreatedAt()))->format('Y-m-d');
				$baseGrandTotal = (float) $order->getBaseGrandTotal();

				// discount fallback
				$baseGrandtotalDiscount = 0.0;
				if ($order->getBaseDiscountAmount() !== null) {
					$baseGrandtotalDiscount = (float) $order->getBaseDiscountAmount();
				} elseif (isset($order['discount_amount'])) {
					$baseGrandtotalDiscount = (float) $order['discount_amount'];
				}

				// Reset per-order containers
				$recordsItem = [];           // items for this order only
				$orderTotalQty = 0.0;        // accumulate total quantity for this order

				foreach ($order->getAllItems() as $item) {
					if ($item->getProductType() === 'bundle') {
						continue;
					}

					$productSku = (string) $item->getSku();
					$productDetails = $this->getProductBySku($productSku);
					if (! $productDetails) {
						$this->logger->warning("Product not found for SKU: {$productSku}");
						continue;
					}

					$baseOriginalPrice = (float)$item->getBaseOriginalPrice();   // MRP
					$basePriceInclTax  = (float)$item->getBasePriceInclTax();    // Discounted price incl tax
					$itemQty = (float)$item->getQtyOrdered();

					// discount amount per item (total)
					$itemDiscountAmount = ($baseOriginalPrice - $basePriceInclTax) * $itemQty;

					// discount percentage
					$itemDiscountPercentage = 0;
					if ($baseOriginalPrice > 0) {
						$itemDiscountPercentage = (($baseOriginalPrice - $basePriceInclTax) / $baseOriginalPrice) * 100;
					}

					// safe attribute getters (may return null)
					$gstRate = (float) ($item->getTaxPercent() ?? 0);
					$gstRateFormatted = number_format($gstRate, 0, '', '');
					$gstGroupCode = 'GST' . $gstRateFormatted;

					$itemIsbn = $productDetails->getIsbn() ?: '';
					$erpItemNumber = $productDetails->getNavisionItemNumber() ?: '';
					$given_options = (string)(int)($item->getGivenOptions() ?: 0);

					$itemMrp = (float) $item->getBaseOriginalPrice();
					$DiscountPercentage = (float) $item->getDiscountPercent();
					$posDiscountItem = (float) $item->getPosDiscount();
					//$cgstAmount = (float) $item->getBaseCgstAmount();
					//$sgstAmount = (float) $item->getBaseSgstAmount();
					$netAmount = (float) $item->getBasePriceInclTax() * $itemQty;
					$taxAmount = (float) $item->getTaxAmount();

					$shippingAddress = $order->getShippingAddress();
					$region = $shippingAddress ? (string) $shippingAddress->getRegion() : '';

					if (strtolower(trim($region)) === 'telangana') {
						// IGST applicable
						//$igstAmount = $taxAmount;
						//$cgstAmount = 0.0;
						//$sgstAmount = 0.0;
						$cgstAmount = $taxAmount / 2;
						$sgstAmount = $taxAmount / 2;
					} else {
						// Split equally into CGST and SGST
						//$igstAmount = 0.0;
						$cgstAmount = $taxAmount / 2;
						$sgstAmount = $taxAmount / 2;
					}


					// accumulate per-order totals
					$orderTotalQty += $itemQty;

					$itemDetails = [
						"itemNo" => $erpItemNumber,
						"isbn" => $itemIsbn,
						"description" => $item->getName(),
						"quantity" => $itemQty,
						"mrp" => $itemMrp,
						"itemDiscountPercentage" => $itemDiscountPercentage,
						"itemDiscountAmount" => $itemDiscountAmount,
						"netAmount" => $netAmount,
						"gstGroupCode" => $gstGroupCode,
						"cgstAmount" => $cgstAmount,
						"sgstAmount" => $sgstAmount,
						"orderDiscountAmt" => $baseGrandtotalDiscount,
						"totolOrderAmount" => $baseGrandTotal,
						"willBeSchoolGiven" => $given_options
					];

					$recordsItem[] = $itemDetails;
				}

				// Build payload for this order (use per-order totals)
				if (! empty($recordsItem)) {
					$payload = [
						"webSourceType" => "ECOMM",
						"webDocumentType" => "Invoice",
						"WebSourceSubType" => $sourceCode ?: '',
						"locationCode" => $order->getLocationCode() ?: '',
						"webOrderNo" => $incrementId,
						"schoolCode" => $order->getSchoolCode() ?? '',
						"storecode" => $order->getStore() ? $order->getStore()->getCode() : '',
						"webInvoiceDate" => $webInvoiceDate,
						"totalQuantity" => (int)$orderTotalQty,                // total quantity for THIS order
						"totalInvoiceAmount" => (float)$baseGrandTotal,        // use order base grand total
						"totalDiscountAmount" => (float)$baseGrandtotalDiscount,
						"shippingCharges" => (float)$order->getBaseShippingAmount(),
						"ecomSalesInvoiceLines" => $recordsItem
					];
					//echo '<pre>';print_r($payload);die;

					$jsonPayload = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
					$this->customLog('ERP Payload JSON: ' . $jsonPayload);

					//echo '<pre>';
					//print_r($payload);
					//die('--- END OF ERP API RESPONSE ---');

					// API call
					$salesinvoiceApiKey = 'ecomsalesInvoice'; // consider moving to config
					$erpBaseApiUrl = $this->helper->getInvoiceErpApiURL();
					$salesinvoiceApiUrl = rtrim($erpBaseApiUrl, '/') . '/' . $salesinvoiceApiKey;
					$expand = 'ecomSalesInvoiceLines';
					$urlWithExpand = $salesinvoiceApiUrl . '?$expand=' . $expand;

					$apiResData = $this->helper->apiInvoiceCall($salesinvoiceApiKey, $urlWithExpand, $payload);

					if ($apiResData) {
						$salesInvoiceArrays = json_decode($apiResData, true);
						if (empty($salesInvoiceArrays)) {
							$apiErrorMsg = $salesinvoiceApiKey . ' api executed failed or returned empty result';
							echo $this->customLog($apiErrorMsg);
							$this->_messageManager->addErrorMessage($apiErrorMsg);
						} else {
							$webInvoiceNumber = $salesInvoiceArrays['webInvoiceNumber'] ?? null;
							if ($webInvoiceNumber) {
								echo $successMessage = 'API executed successfully and web Invoice Number is ' . $webInvoiceNumber;
								$this->customLog($successMessage);
								$this->_messageManager->addSuccessMessage($successMessage);

								// Save webInvoiceNumber in sales_order if blank
								try {
									$existingInvoiceNo = $order->getData('web_invoice_number');
									if (empty($existingInvoiceNo)) {
										$order->setData('web_invoice_number', $webInvoiceNumber);
										$order->save(); // Save only if not already set
										$this->customLog("Saved webInvoiceNumber '{$webInvoiceNumber}' to order #{$order->getIncrementId()}");
									} else {
										$this->customLog("webInvoiceNumber already exists ({$existingInvoiceNo}) for order #{$order->getIncrementId()}");
									}
								} catch (\Exception $e) {
									$this->customLog('Failed to save webInvoiceNumber: ' . $e->getMessage());
									$this->_messageManager->addErrorMessage('Failed to save webInvoiceNumber: ' . $e->getMessage());
								}
							} else {
								$apiErrorMsg = $salesinvoiceApiKey . ' api executed but did not return webInvoiceNumber';
								echo $apiErrorMsg;
								$this->customLog($apiErrorMsg);
								$this->_messageManager->addErrorMessage($apiErrorMsg);
							}
						}
					} else {
						$this->customLog('ERP API returned empty response');
						$this->_messageManager->addErrorMessage('ERP API returned empty response');
					}
				} else {
					$this->_messageManager->addNoticeMessage('No invoice lines found for order ' . $incrementId);
				}

                }
            } else {
				$this->_messageManager->addErrorMessage('No Order found!');
		    }

            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setRefererOrBaseUrl();
        } catch (\Exception $e) {
            $this->customLog($e->getMessage());
            $this->_messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setRefererOrBaseUrl();
        }
    }

    /**
     * Build order collection for single day ($prevDate) in 'Y-m-d' format
     *
     * @param string|null $prevDate
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
	public function getOrderCollectionByDateRange()
	{
		$currentDate = $this->timezoneInterface->date()->format('Y-m-d');
		$nextDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
		//$prevDate = date('Y-m-d', strtotime($currentDate . ' -1 days'));
		$prevDate = '2025-11-13';
		$start = $prevDate . ' 00:00:00';
		$end = $nextDate . ' 23:59:59';

		$ordersCollection = $this->orderCollectionFactory->create()
			//->addAttributeToFilter('status', ['in' => ['assigned_to_picker']])
			->addAttributeToFilter('status', ['in' => ['dispatched_to_courier','order_delivered']])
			->addAttributeToFilter('web_invoice_number', ['null' => true])
			->addAttributeToFilter('created_at', ['gteq' => $start])
			->addAttributeToFilter('created_at', ['lteq' => $end]);
			/*->addFieldToFilter(
				['web_invoice_number', 'web_invoice_number'],
				[
					['notnull' => true],
					['neq' => '']
				]
			);*/

		$ordersCollection->setOrder('created_at', 'desc');

		return $ordersCollection;
	}


    /**
     * Product lookup by SKU using repository
     *
     * @param string $productSku
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    public function getProductBySku(string $productSku)
    {
        try {
            return $this->productRepository->get($productSku);
        } catch (\Exception $e) {
            $this->logger->warning("getProductBySku failed for SKU {$productSku}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Logging helper
     *
     * @param mixed $log
     * @return void
     */
	  public function customLog($log)
	{
		try {
			// Define your log file path inside var/log
			$file = BP . '/var/log/erpsalesinvoiceapi.log';
			$writer = new \Zend_Log_Writer_Stream($file);
			$logger = new \Zend_Log();
			$logger->addWriter($writer);

			if (is_array($log) || is_object($log)) {
				$logger->info(print_r($log, true));
			} else {
				$logger->info($log);
			}
		} catch (\Exception $e) {
			// fallback: log to system logger
			if (isset($this->logger)) {
				$this->logger->error('customLog failed: ' . $e->getMessage());
			}
		}
	}

}
