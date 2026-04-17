<?php

namespace Plumrocket\RMA\Controller\Adminhtml\Returns;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Plumrocket\RMA\Model\ReturnsFactory;    
use Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem;
use Zend_Pdf;
use Zend_Pdf_Page;
use Zend_Pdf_Font;
use Zend_Pdf_Image;
use Zend_Pdf_Color_GrayScale;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;

class MassPrint extends Action
{
    protected $filter;
    protected $resultPageFactory;
    protected $collectionFactory;
    protected $ReturnsFactory;
    protected $responseFactory;
    protected $layoutFactory;
    protected $_storeManager;
    protected $_filesystem;
    protected $_logo;
    protected $orderRepository;
    protected $scopeConfig;
    protected $countryFactory;
    protected $_resourceConnection;
    const STORE_NAME = "general/store_information/name";
    const STORE_ADDRESS_ONE = "general/store_information/street_line1";
    const STORE_ADDRESS_TWO = "general/store_information/street_line2";
    const STORE_COUNTRY = "general/store_information/country_id";
    const COPY_RIGHT = "design/footer/copyright";

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filter $filter,
        ResponseFactory $responseFactory,
        ReturnsFactory $returnsModelFactory,
	CollectionFactory $collectionFactory,
	LayoutFactory $layoutFactory,
	StoreManagerInterface $storeManager,
	Filesystem $filesystem,
	\Magento\Theme\Block\Html\Header\Logo $logo,
	\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	\Magento\Directory\Model\CountryFactory $countryFactory,
	ResourceConnection $resourceConnection,
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->responseFactory = $responseFactory;
        $this->returnsModelFactory = $returnsModelFactory;
	$this->collectionFactory = $collectionFactory;
	$this->layoutFactory = $layoutFactory;
	$this->_storeManager = $storeManager;
	$this->_filesystem = $filesystem;
	$this->_logo = $logo;
	$this->orderRepository = $orderRepository;
	$this->scopeConfig = $scopeConfig;
	$this->countryFactory = $countryFactory;
	$this->_resourceConnection = $resourceConnection;
    }

    public function execute()
    {
	    try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $pdf = new Zend_Pdf();

	    foreach ($collection as $item) {

	    if($item['status'] != 'new' && $item['status'] != 'processed_closed' && $item['status'] != 'closed'){
                $pdfPage = $this->generatePdfPage($item['entity_id'], $item['increment_id'], $item['order_id']);
	     	$pdf->pages[] = $pdfPage; 
	    } 
	    }
            return $this->streamPdf($pdf);
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred: %1', $e->getMessage()));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }

    protected function generatePdfPage($entityId, $incrementId, $orderId)
    {
	   $order = $this->getOrderById($orderId);
	   if (!$order) {
        	throw new \Exception("Order not found for ID: " . $orderId);
	   }
	    $connection = $this->_resourceConnection->getConnection();
	    $sql = "SELECT order_item_id, reason_id, condition_id, resolution_id, qty_purchased, qty_requested, qty_authorized, qty_received, qty_approved FROM plumrocket_rma_returns_item WHERE parent_id = :parent_id";
 	    $bind = ['parent_id' => (int)$entityId];
	    $rmaData = $connection->fetchAll($sql, $bind); 
	    $orderIncrementId = $order->getIncrementId(); 
	    $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
    	    $customerEmail = $order->getCustomerEmail();
	    $shippingAddress = $order->getShippingAddress();
	    $schoolName = $order->getSchoolName();
	    $productName = "";
	    if ($order->getId()) {
	        $items = $order->getItems();
	        foreach ($items as $item) {
                if ($item->getProductType() == 'bundle') {
                    $productName = $item->getName();
                }
              }
	    }
	    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
	    $stroe_name = $this->scopeConfig->getValue(self::STORE_NAME, $storeScope);
	    $stroe_address_one = $this->scopeConfig->getValue(self::STORE_ADDRESS_ONE, $storeScope);
	    $store_address_two = $this->scopeConfig->getValue(self::STORE_ADDRESS_TWO, $storeScope);
	    $store_country = $this->scopeConfig->getValue(self::STORE_COUNTRY, $storeScope);
	    $copyRight = $this->scopeConfig->getValue(self::COPY_RIGHT, $storeScope);
	    $street = implode(' ', $shippingAddress->getStreet());
	    $countryId = $shippingAddress->getCountryId();
	    $country = $this->countryFactory->create()->loadByCode($countryId);
	    $countryName = $country->getName();
	    $page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4); 
	    $fontRegular = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
	    $fontBold = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
            $this->drawHeader($page);
	    $page->setFont($fontBold, 16);	
	    $page->drawText("RETURN #".$incrementId, 380, 800);
	    $page->setFont($fontRegular, 10);
	    $yPosition = 770;
	    $section1X = 20;
    	    $section1Y = $yPosition;
	    $sectionWidth = 555;
	    $sectionHeight = 740;
	    $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
	    $page->drawRectangle($section1X, $section1Y - $sectionHeight, $section1X + $sectionWidth, $section1Y);
	    $page->setLineColor(new Zend_Pdf_Color_GrayScale(0));
	    $page->drawRectangle($section1X, $section1Y - $sectionHeight, $section1X + $sectionWidth, $section1Y);
	    $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
	    $page->drawText("ID: ".$incrementId, $section1X + 10, $section1Y - 20);
	    $page->drawText("Order ID: ".$orderIncrementId, $section1X + 10, $section1Y - 35);
   	    $page->drawText("Customer Name: ".$customerName, $section1X + 10, $section1Y - 50);
	    $page->drawText("Email: ".$customerEmail, $section1X + 10, $section1Y - 65);
	    $page->drawText("School Name: ".$schoolName, $section1X + 10, $section1Y - 80); 
	    $page->drawText("Product Purchased: ".$productName, $section1X + 10, $section1Y - 95);
	    $page->setFont($fontBold, 10);
            $yPosition -= 75 + 20;
	    $page->drawText("Return Address:", $section1X + 10, $yPosition - 20);
	    $page->setFont($fontRegular, 10);
	    $page->drawText($stroe_name, $section1X + 10, $yPosition - 35);
	    $page->drawText($stroe_address_one.",", $section1X + 10, $yPosition - 50);
	    $page->drawText($store_address_two, $section1X + 10, $yPosition - 65);
	    $yPosition -= 60 + 10;
	    $page->setFont($fontBold, 10);
	    $page->drawText("Customer Address:", $section1X + 10, $yPosition - 20);
	    $page->setFont($fontRegular, 10);
	    $page->drawText($shippingAddress->getFirstname().' '.$shippingAddress->getLastname(), $section1X + 10, $yPosition - 35);
	    $page->drawText($street, $section1X + 10, $yPosition - 50);
	    $page->drawText($shippingAddress->getCity().", ".$shippingAddress->getRegion().", ".$shippingAddress->getPostcode(), $section1X + 10, $yPosition - 65);
	    $page->drawText($countryName, $section1X + 10, $yPosition - 80);
	    $page->drawText($shippingAddress->getTelephone(), $section1X + 10, $yPosition - 95);
	    $yPosition -= 90 + 10;
	    $page->setFont($fontBold, 10);
	    $page->drawText("ITEMS TO RETURN", $section1X + 10, $yPosition - 20);
	    $yPosition -= 15;
	    $page->setFont($fontBold, 10);
	    $page->drawText("Product Details", $section1X + 10, $yPosition - 20);
	    $page->drawText("RMA Details", $section1X + 300, $yPosition - 20);
	    $page->drawText("Status", $section1X + 500, $yPosition - 20);
	    $yPosition -= 20;
	    $page->setFont($fontRegular, 10);
	    foreach ($rmaData as $item) {
		  /*   if ($yPosition <= 20) {
			     $page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4); 
			     $fontRegular = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            		     $fontBold = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
			     $page->setFont($fontRegular, 10);
	                 $this->drawHeader($page); 
		         $yPosition = 700; 
	    } */
		 $orderItemId = $item['order_item_id'];
		 $reasonId = $item['reason_id'];
		 $conditionId = $item['condition_id'];
		 $resolutionId = $item['resolution_id'];
		 $qtyPurchased = $item['qty_purchased'];
		 $qtyRequested = $item['qty_requested'];
		 $qtyAuthorized = $item['qty_authorized'];
		 $qtyReceived = $item['qty_received'];
		 $qtyApproved = $item['qty_approved'];
		 $status = "";
		 if($qtyPurchased > 0 && $qtyRequested > 0 && $qtyAuthorized == "" && $qtyReceived == "" && $qtyApproved == ""){
			$status = "Pending";
		 }else if ($qtyPurchased > 0 && $qtyRequested > 0 && $qtyAuthorized > 0 && $qtyReceived == "" && $qtyApproved == ""){
		       $status = "Approved";
		 }else if ($qtyPurchased > 0  && $qtyRequested > 0 && $qtyAuthorized > 0 && $qtyReceived > 0 && $qtyApproved == ""){
			$status = "In Transit";
		 }else if ($qtyPurchased > 0 && $qtyRequested > 0 && $qtyAuthorized > 0 && $qtyReceived > 0 && $qtyApproved > 0){
                        $status = "Resolved";
                 }

		 $reasonTitle = $this->getTitleFromTable('plumrocket_rma_reason', $reasonId);
           	 $conditionTitle = $this->getTitleFromTable('plumrocket_rma_condition', $conditionId);
	         $resolutionTitle = $this->getTitleFromTable('plumrocket_rma_resolution', $resolutionId);
		 $sql = "SELECT name, sku FROM sales_order_item WHERE item_id = :order_item_id";
		 $bind = ['order_item_id' => $orderItemId];
		 $result = $connection->fetchRow($sql, $bind);
		 if ($result) {
		    $productName = $result['name'];
		    $words = explode(' ', $productName);
		    $wordCount = count($words);
	            $firstLine = '';
		    $secondLine = '';
		    $thirdLine = '';
                    for ($i = 0; $i < count($words); $i++) {
                    if ($i < 6) {
                    $firstLine .= $words[$i] . ' ';
                    }elseif ($i < 12) {
     			   $secondLine .= $words[$i] . ' '; 
    		    } else {
		        $thirdLine .= $words[$i] . ' ';  
    		    }
                    }
                    $firstLine = rtrim($firstLine);
		    $secondLine = rtrim($secondLine);
		    $thirdLine = rtrim($thirdLine);
		    $productSku = $result['sku'];
		    if($wordCount <= 6 ){
		    $page->setFont($fontBold, 10);
		    $page->drawText($firstLine , $section1X + 10, $yPosition - 20);
		    $page->setFont($fontRegular, 10);
		    $page->drawText("SKU : ".$productSku , $section1X + 10, $yPosition - 35);
		    $page->drawText("Return Reason: ".$reasonTitle, $section1X + 300, $yPosition - 20);
                    $page->drawText("Item Condition: ".$conditionTitle, $section1X + 300, $yPosition - 35);
		    $page->drawText("Resolution: ".$resolutionTitle, $section1X + 300, $yPosition - 50);
                    $page->drawText($status, $section1X + 500, $yPosition - 20);
		    $yPosition -= 60;	
		    }else if($wordCount >= 7 && $wordCount <= 12 ){
		    $page->setFont($fontBold, 10);
		    $page->drawText($firstLine , $section1X + 10, $yPosition - 20);
		    $page->drawText($secondLine , $section1X + 10, $yPosition - 35);
		    $page->setFont($fontRegular, 10);
		    $page->drawText("SKU : ".$productSku , $section1X + 10, $yPosition - 50);
                    $page->drawText("Return Reason: ".$reasonTitle, $section1X + 300, $yPosition - 20);
                    $page->drawText("Item Condition: ".$conditionTitle, $section1X + 300, $yPosition - 35);
                    $page->drawText("Resolution: ".$resolutionTitle, $section1X + 300, $yPosition - 50);
                    $page->drawText($status, $section1X + 500, $yPosition - 20);
                    $yPosition -= 60;   	    
		    }else{
		    $page->setFont($fontBold, 10);
		    $page->drawText($firstLine , $section1X + 10, $yPosition - 20);
                    $page->drawText($secondLine , $section1X + 10, $yPosition - 35);
		    $page->drawText($thirdLine , $section1X + 10, $yPosition - 50);
		    $page->setFont($fontRegular, 10);
		    $page->drawText("SKU : ".$productSku , $section1X + 10, $yPosition - 65);
                    $page->drawText("Return Reason: ".$reasonTitle, $section1X + 300, $yPosition - 20);
                    $page->drawText("Item Condition: ".$conditionTitle, $section1X + 300, $yPosition - 35);
                    $page->drawText("Resolution: ".$resolutionTitle, $section1X + 300, $yPosition - 50);
                    $page->drawText($status, $section1X + 500, $yPosition - 20);
                    $yPosition -= 70;
		    }
		 }
	    }
	    $this->drawFooter($page, $copyRight);
            return $page;
    }

    protected function getTitleFromTable($tableName, $id)
    {
        $connection = $this->_resourceConnection->getConnection();
        $sql = "SELECT title FROM $tableName WHERE id = :id";
        $bind = ['id' => (int)$id];
        $title = $connection->fetchOne($sql, $bind);
        return $title;
    }


    protected function drawHeader(Zend_Pdf_Page $page)
    {
	    $mediaDir = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
	    $logoImagePath = 'ubertheme/ubthemehelper/element/header/logo/src/logo.png';
	    $logoImage = $mediaDir.$logoImagePath;
	    if ($logoImage) {
	    $image = Zend_Pdf_Image::imageWithPath($logoImage);
            $page->drawImage($image, 60, 780, 200, 830);
	}
     }

    protected function drawFooter(Zend_Pdf_Page $page, $copyRight)
    {
	   //  $footerText = "Copyright © 2012-2021 Centralbooksonline.com, All Rights Reserved";
	     $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
	     $page->setFont($font, 10);      
	     $page->drawText($copyRight, 130, 10);
    }

    protected function streamPdf(Zend_Pdf $pdf)
    {
        $response = $this->responseFactory->create();
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Content-Disposition', 'attachment; filename="RMA_Invoice.pdf"');
        $response->setBody($pdf->render());
        return $response;
    }

     protected function getOrderById($orderId)
     {
    	try {
        	$order = $this->orderRepository->get($orderId); 
        	return $order;
	} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        return null;  
         }
     }

    protected function _isAllowed()
    {
        return true;
    }
}
