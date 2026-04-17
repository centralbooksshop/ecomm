<?php

namespace Retailinsights\CentralbooksShipping\Model\Order\Pdf;

use Magento\Sales\Model\Order\Pdf\Invoice as BaseInvoice;
use \Laminas\Barcode\Barcode;
use Laminas\Pdf\Color\GrayScale;
use Zend_Pdf_Color_GrayScale;
use Zend_Pdf_Resource_Image_Png;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Sales\Model\RtlTextHandler;
use Magento\Framework\App\ObjectManager;

class Invoice extends BaseInvoice
{

 /**
  * @var RtlTextHandler
  */
protected $rtlTextHandler;
	/**
 * @var \Magento\Store\Model\App\Emulation
 */
protected $appEmulation;


public function __construct(
    \Magento\Payment\Helper\Data $paymentData,
	\Delhivery\Lastmile\Model\ResourceModel\Awb\CollectionFactory $collectionFactory,
	\Ecom\Ecomexpress\Model\ResourceModel\Awb\CollectionFactory $ecomcollectionFactory,
    \Ecom\Ecomexpress\Model\ResourceModel\Pincode\CollectionFactory $ecompincodecollectionFactory,
    \Magento\Framework\Stdlib\StringUtils $string,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Framework\Filesystem $filesystem,
    \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
    \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
    \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
    \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
    \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
	 Database $fileStorageDatabase = null,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Store\Model\App\Emulation $appEmulation,
	RtlTextHandler $rtlTextHandler = null,
    array $data = []
) {
    $this->collectionFactory = $collectionFactory;
	$this->ecomcollectionFactory = $ecomcollectionFactory;
	$this->ecompincodecollectionFactory = $ecompincodecollectionFactory;
	$this->rtlTextHandler = $rtlTextHandler ?: ObjectManager::getInstance()->get(RtlTextHandler::class);
	$this->appEmulation = $appEmulation;
	$this->string = $string;
    $this->fileStorageDatabase = $fileStorageDatabase ?: ObjectManager::getInstance()->get(Database::class);
    parent::__construct(
        $paymentData,
        $string,
        $scopeConfig,
        $filesystem,
        $pdfConfig,
        $pdfTotalFactory,
        $pdfItemsFactory,
        $localeDate,
        $inlineTranslation,
        $addressRenderer,
        $storeManager,
        $appEmulation,
        $data
    );
}
    /**
     * We only need to override the getPdf of Invoice,
     *  most of this method is copied directly from parent class
     *
     * @param array $invoices
     * @return \Zend_Pdf
     */

    public function getPdf($invoices = []) {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
           /* if ($invoice->getStoreId()) {
                $this->appEmulation->emulate($invoice->getStoreId());
                $this->_storeManager->setCurrentStore($invoice->getStoreId());
            }*/

            if ($invoice->getStoreId()) {
                $this->appEmulation->startEnvironmentEmulation(
                    $invoice->getStoreId(),
                    \Magento\Framework\App\Area::AREA_FRONTEND,
                    true
                );
                $this->_storeManager->setCurrentStore($invoice->getStoreId());
            }

            $page = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Invoice # ') . $invoice->getIncrementId());
            /* Add table */
            $this->insertFexexLabel($page, $order);
			//$this->insertDelhiveryLabel($page, $order);
			$this->insertDtdcLabel($page, $order);
			$this->insertEcomLabel($page, $order);
			 $currentWebsiteCodehead = $order->getStore()->getWebsite()->getCode(); 
				  if($currentWebsiteCodehead == 'schools'){
					$this->_drawHeadernew($page);
				  } else {
				       $this->_drawHeader($page);
				  }
            
            /* Add body */
           
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                 $currentWebsiteCode = $order->getStore()->getWebsite()->getCode(); 
				  if($currentWebsiteCode == 'schools'){
					$this->drawNotice($page,$item, $order);
				  } else {
				  $this->_drawItem($item, $page, $order);
				  }
               
                // $this->drawNot($page,$item, $order);
                $page = end($pdf->pages);
                break;
            }
            /* Add totals */
            $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                //$this->appEmulation->revert();
				$this->appEmulation->stopEnvironmentEmulation();
            }
            // draw custom notice
        }
        $this->_afterGetPdf();
        return $pdf;
    }



    /**
     * Draw header for item table
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeadernew(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 35];

        // $lines[0][] = ['text' => __('SKU'), 'feed' => 290, 'align' => 'right'];

        // custom column
        // $lines[0][] = ['text' => __('HSN'), 'feed' => 290, 'align' => 'right'];

        //$lines[0][] = ['text' => __('Qty'), 'feed' => 435, 'align' => 'right'];

        //$lines[0][] = ['text' => __('Price'), 'feed' => 360, 'align' => 'right'];

        //$lines[0][] = ['text' => __('Tax'), 'feed' => 495, 'align' => 'right'];

        $lines[0][] = ['text' => __('Subtotal'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

	 protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 35];

         $lines[0][] = ['text' => __('SKU'), 'feed' => 260, 'align' => 'right'];

        // custom column
        // $lines[0][] = ['text' => __('HSN'), 'feed' => 290, 'align' => 'right'];

        $lines[0][] = ['text' => __('Price'), 'feed' => 375, 'align' => 'right'];
		 $lines[0][] = ['text' => __('Tax'), 'feed' => 445, 'align' => 'right'];

        $lines[0][] = ['text' => __('Qty'), 'feed' => 505, 'align' => 'right'];

        $lines[0][] = ['text' => __('Subtotal'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }
     /**
     * draw notice below content
     *
     * @param \Zend_Pdf_Page $page
     */
    protected function drawNotice(\Zend_Pdf_Page $page,$item, $order) {
        $iFontSize = 10;     // font size
        $iColumnWidth = 200; // whole page width
        $iWidthBorder = 300; // half page width
        //$sNotice = $item;   //"NOTE: This is not a GST invoice. This is a packing slip only."; // your message
		$sNotice = '';
        $iXCoordinateText = 1;
        $sEncoding = 'UTF-8';
        //$this->y -= 10; // move down on page
          
        try {
            $oFont = $this->_setFontRegular($page, $iFontSize);
            $iXCoordinateText = $this->getAlignCenter($sNotice, $iXCoordinateText, $iColumnWidth, $oFont, $iFontSize);  // center text coordinate
            // $page->setLineColor(new \Zend_Pdf_Color_Rgb(1, 0, 0));                                             // red lines
            $iXCoordinateBorder = $iXCoordinateText - 100;                                                               // border is wider than text
            // draw top border
            // draw text
            //$this->y -= 15;                                                                                             // 
            // $amount = number_format($item->getOrderItem()->getPrice(),2);

            // $page->drawText($item->getOrderItem()->getName(), 5, $this->y, $sEncoding);
            // $page->drawText('₹'.$amount, 310, $this->y, $sEncoding);
            // $page->drawText(1, 400, $this->y, $sEncoding);
            // $page->drawText('₹'.$amount, 510, $this->y, $sEncoding);

            // $this->y -= 10;
            
            $data =$item->getOrderItem()->getProductOptions();
            $orderId = $order->getId();

             $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('quote_item'); 

            $sql = "Select * FROM " . $tableName." Where quote_id =".$order->getQuoteId();
            $result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
            $optionalItems='';
            $optItems= array();
            $optItemsIds= array();
           
                foreach ($result as $key => $value) {
                    if($value['optional_items'] !=''){
                        $optionalItems = explode(',', $value['optional_items']);
                    }
                    if(is_array($optionalItems) || is_object($optionalItems)){
                        foreach ($optionalItems as $key => $item) {
                            if($item == $value['product_id']){
                                $optItems[]=$value['name'];
                                $optItemsIds[]=$value['product_id'];
                            }
                        }
                    }
                }
                $optItems = array_unique($optItems);
                $optItemsIds = array_unique($optItemsIds);
            $start = microtime(true);
			$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
            $collection = $productCollection->addAttributeToSelect(['name','entity_id'])->load();
			//$collection = $productCollection->addAttributeToSelect('*')->load();
			$time_elapsed_secs = microtime(true) - $start;
			//$this->logger->info('time elapsed secs ' .$time_elapsed_secs);

          
            $orderItems = $order->getAllItems();
			$this->_setFontBold($page, 10);

             if(isset($data['bundle_options'])){
                foreach ($orderItems as $item) {
                    $page->drawText($item->getName(), 30, $this->y, $sEncoding);
                    $this->y -= 20;
                   break;
                 }
             }else{
                foreach ($orderItems as $item) {
                    $page->drawText($item->getName(), 30, $this->y, $sEncoding);
                    $this->y -= 20;
                }
             }
			 $this->_setFontRegular($page, 10);

             if(isset($data['bundle_options'])){
                $i=1;
                foreach ($data['bundle_options'] as $key => $value) {
                    $page->drawText(($i)." ) ".$value['label'], 30, $this->y, $sEncoding);
                    $this->y -= 20;
                    $i++;
                    
                    foreach ($value['value'] as $valueSingle) {
                        foreach ($collection as $product){
                            if($product->getName() == $valueSingle['title']){
                                foreach ($optItemsIds as $keyId => $valueOpt) {
                                    if($valueOpt == $product->getEntityId()){
                                         $page->drawText(("->")."  ".$product->getName(), 40, $this->y, $sEncoding);
                                            $this->y -= 20;
                                    }
                                }
                            }
                        } 
                    }
                 } 
            }else{
                return;
            }
        } catch (\Exception $exception) {
            // handle
        }
    }
    /**
     * draw notice below content
     *
     * @param \Zend_Pdf_Page $page
     */
    protected function drawNot(\Zend_Pdf_Page $page,$item, $order) {
        $iFontSize = 10;     // font size
        $iColumnWidth = 200; // whole page width
        $iWidthBorder = 300; // half page width
        $sNotice = $item;//"NOTE: This is not a GST invoice. This is a packing slip only."; // your message
        $iXCoordinateText = 1;
        $sEncoding = 'UTF-8';
        $this->y -= 10; // move down on page
        try {
            $oFont = $this->_setFontRegular($page, $iFontSize);
            $iXCoordinateText = $this->getAlignCenter($sNotice, $iXCoordinateText, $iColumnWidth, $oFont, $iFontSize);  // center text coordinate
            // $page->setLineColor(new \Zend_Pdf_Color_Rgb(1, 0, 0));                                             // red lines
            $iXCoordinateBorder = $iXCoordinateText - 100;                                                               // border is wider than text
            // draw top border
            // draw text
            $this->y -= 15;                                                                                             // 
            // $amount = number_format($item->getOrderItem()->getPrice(),2);

            // $page->drawText($item->getOrderItem()->getName(), 5, $this->y, $sEncoding);
            // $page->drawText('₹'.$amount, 310, $this->y, $sEncoding);
            // $page->drawText(1, 400, $this->y, $sEncoding);
            // $page->drawText('₹'.$amount, 510, $this->y, $sEncoding);

            // $this->y -= 10;
            
            $data =$item->getOrderItem()->getProductOptions();
            $orderId = $order->getId();

            // $bundleProductId='';
            // if($data){
            //     foreach ($data as $key => $value) {
            //         $bundleProductId = $value['product']; 
            //     }
            // }
        
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('quote_item'); 
            
            $sql = "Select * FROM " . $tableName." Where quote_id =".$order->getQuoteId();
            $result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
            $optionalItems='';
            $optItems= array();
           
                foreach ($result as $key => $value) {
                    if($value['optional_items'] !=''){
                        $optionalItems = explode(',', $value['optional_items']);
                    }
                    foreach ($optionalItems as $key => $item) {
                        if($item == $value['product_id']){

                            $optItems[]=$value['name'];
                            
                        }
                    }
                }
                $optItems = array_unique($optItems);
               
               //  j=1;
               // foreach ($optItems as $key => $itemOpt) {
               //  $page->drawText($itemOpt, 30, $this->y, $sEncoding);
               //  $this->y -= 20;
               //  $j++;
               // }
            $i=1;
            $page->drawText('Optionally Sellected Items', 30, $this->y, $sEncoding);
                $this->y -= 10;

            foreach ($optItems as $key => $value) {
                $page->drawText(($i)." ) ".$value, 30, $this->y, $sEncoding);
                $this->y -= 20;
                $i++;
             } 
                
        } catch (\Exception $exception) {
            // handle
        }
     }

      protected function insertEcomLabel($page, $order) {
      $orderId = $order->getId();
	  $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $orderObj = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');
      $order = $orderObj->get($orderId);
      //echo $orderIncrementId = $order->getIncrementId();
	  $orderEntityId = $order->getEntityId();
	  $postCode = $order->getShippingAddress()->getPostcode();
	  
      // get ecom 

	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$readConnection = $resource->getConnection();
		$query = "SELECT * FROM " . $resource->getTableName('ecomexpress_pincode')." WHERE pincode = $postCode";
		$pincodedata = $readConnection->fetchAll($query);

	   //echo '<pre>';print_r($pincodedata);die;

	   $ecomCollection = $this->ecomcollectionFactory->create()
		->addFieldToSelect('*')
		//->addFieldToFilter('order_increment_id', $orderIncrementId)
	   ->addFieldToFilter('orderid', $orderEntityId)
		->addFieldToFilter('state', '1');
        
        $ecomCollection->setOrder('awb_id', 'DESC');
        $ecomCollection->getSelect()->limit(1);

		if($ecomCollection->getFirstItem()->getData('awb_id')){
            if($pincodedata){
				$ecomexpresspincode = $pincodedata['0']['dccode'].'/'.$pincodedata['0']['state_code'];   
			}
		    $this->y = $this->y ? $this->y : 100;
            $top = $this->y;

            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
            $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
            $this->_setFontRegular($page, 10);

            $this->y = $this->y - 100;

			 foreach($ecomCollection as $ecomval){
                $ecomexpressawb = $ecomval['awb'];
             }
            $text = $ecomexpressawb;
            $config = new \Zend_Config([
                'barcode' => 'code128',
                'barcodeParams' => [
                    'text' => $text,
                    'drawText' => true
                ],
                'renderer' => 'image',
                'rendererParams' => ['imageType' => 'png']
            ]);
            
			$barcodeResource = Barcode::factory($config)->draw();
             ob_start();
            imagepng($barcodeResource);
            $barcodeImage = ob_get_clean();
            $imageEcom = new \Zend_Pdf_Resource_Image_Png('data:image/png;base64,'.base64_encode($barcodeImage));


            $width = $imageEcom->getPixelWidth();
            $height = $imageEcom->getPixelHeight();
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));

            // first line
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $this->y = $top;// - 40;
            //$delhivery = $this->getFedexInstructions($order);
			$this->_setFontBold($page, 10);
            $page->drawText(strip_tags(ltrim('Ecomexpress Shipping Details: ')), 35, $this->y, 'UTF-8');
            $this->y -= 15;
			//echo '<pre>';print_r($ecomCollection->getData());
			$this->_setFontRegular($page, 10);
            foreach($ecomCollection as $awbvalue){
             $text = 'Tracking Number. : '.$awbvalue['awb'];
		     $page->drawText(strip_tags(ltrim($text)), 35, $this->y, 'UTF-8');
			 $this->y -= 15;
			 $this->_setFontBold($page, 10);
			 
			 $page->drawText(strip_tags(ltrim('Return Address: ')), 35, $this->y, 'UTF-8');
			 //$text = $awbvalue['return_address'];
			 $this->y -= 15;
			 $this->_setFontRegular($page, 10);
			 $address = $this->getScopeConfig('delhivery_lastmile/general/return_address');
			 
			 $ecomaddressvalue = wordwrap($address, 50, "\n");
				foreach(explode("\n", $ecomaddressvalue) as $textLine){
				  if ($textLine!=='') {
					$page->drawText(strip_tags(ltrim($textLine)), 35, $this->y, 'UTF-8');
				 }
				   $this->y -= 15;
				}
		    }

			 $this->_setFontBold($page, 15);
			 $page->drawImage($imageEcom, 300, $top -$height, $top + 50, $top);
			 $this->y -= 15;
			 $text = $ecomexpresspincode;
			
		     $page->drawText(strip_tags(ltrim($text)), 35, $this->y, 'UTF-8');
             $this->y -= 15;
			 $text = 'Pre-Paid';
			  $this->y -= 15;
		     $page->drawText(strip_tags(ltrim($text)), 35, $this->y, 'UTF-8');
			 $this->y -= 15;

		  }
	 }

	  protected function insertDelhiveryLabel($page, $order) {
      $orderId = $order->getId();
	  $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $orderObj = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');
      $order = $orderObj->get($orderId);
      $orderIncrementId = $order->getIncrementId();
      // get delhivery 
	  
	   $delhiveryCollection = $this->collectionFactory->create()
		->addFieldToSelect('*')
		->addFieldToFilter('order_increment_id', $orderIncrementId)
		->addFieldToFilter('state', '1');
        
        $delhiveryCollection->setOrder('awb_id', 'DESC');
        $delhiveryCollection->getSelect()->limit(1);

		if($delhiveryCollection->getFirstItem()->getData('awb_id')){
		    $this->y = $this->y ? $this->y : 100;
            $top = $this->y;

            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
            $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
            $this->_setFontRegular($page, 10);

            $this->y = $this->y - 100;

			 foreach($delhiveryCollection as $delhiveryval){
                $delhiveryawb = $delhiveryval['awb'];
             }
            $text = $delhiveryawb;
            // Generate barcode with Laminas
			$barcodeOptions = [
				'text' => $delhiveryawb,
				'drawText' => true,
			];
			$rendererOptions = [
				'imageType' => 'png',
			];

			$barcodeResource = Barcode::factory('code128', 'image', $barcodeOptions, $rendererOptions)->draw();

			// Capture PNG output
			ob_start();
			imagepng($barcodeResource);
			$barcodeImage = ob_get_clean();
			imagedestroy($barcodeResource);

			// Save barcode image in pub/media/barcodes/
			$barcodePath = $this->saveBarcodeToMedia($barcodeImage, $delhiveryawb);

			// Create Zend_Pdf image
			$imageDelhivery = new Zend_Pdf_Resource_Image_Png($barcodePath);
			$width = $imageDelhivery->getPixelWidth();
			$height = $imageDelhivery->getPixelHeight();
			
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));

            // first line
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $this->y = $top;// - 40;
            //$delhivery = $this->getFedexInstructions($order);
			$this->_setFontBold($page, 10);
            $page->drawText(strip_tags(ltrim('Delhivery Shipping Details: ')), 35, $this->y, 'UTF-8');
            $this->y -= 15;
			//echo '<pre>';print_r($delhiveryCollection->getData());
			$this->_setFontRegular($page, 10);
            foreach($delhiveryCollection as $awbvalue){
             $text = 'Tracking Number. : '.$awbvalue['awb'];
		     $page->drawText(strip_tags(ltrim($text)), 35, $this->y, 'UTF-8');
			 $this->y -= 15;
			 $this->_setFontBold($page, 10);
			 
			 $page->drawText(strip_tags(ltrim('Return Address: ')), 35, $this->y, 'UTF-8');
			 //$text = $awbvalue['return_address'];
			 $this->y -= 15;
			 $this->_setFontRegular($page, 10);
			 $address = $this->getScopeConfig('delhivery_lastmile/general/return_address');
			 
			 $addressvalue = wordwrap($address, 50, "\n");
				foreach(explode("\n", $addressvalue) as $textLine){
				  if ($textLine!=='') {
					$page->drawText(strip_tags(ltrim($textLine)), 35, $this->y, 'UTF-8');
				 }
				   $this->y -= 15;
				}
		    }

			 $this->_setFontBold($page, 15);
			 $page->drawImage($imageDelhivery, 300, $top -$height, $top + 50, $top);
             $this->y -= 15;
			 $text = 'Pre-Paid';
		     $page->drawText(strip_tags(ltrim($text)), 35, $this->y, 'UTF-8');
			 $this->y -= 15;

		  }
	 }

	 protected function insertDtdcLabel($page, $order)
	{
		if (empty($order->getCboReferenceNumber())) {
			return;
		}

		$dtdcawbnumber = trim($order->getCboReferenceNumber());
		$courier_name  = $order->getCboCourierName();

		$shippingAddress = $order->getShippingAddress()->getData();
		$origin_state = $shippingAddress['region'] ?? '';

		$this->y = $this->y ?: 700;
		$top = $this->y;

		// Colors & font
		$page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
		$this->_setFontRegular($page, 10);

		// ↓ Slightly higher margin for neatness
		$this->y -= 10;

		/** Generate barcode */
		$barcodeOptions = [
			'text' => $dtdcawbnumber,
			'drawText' => true,
		];
		$rendererOptions = ['imageType' => 'png'];
		$barcodeResource = \Laminas\Barcode\Barcode::factory('code128', 'image', $barcodeOptions, $rendererOptions)->draw();

		ob_start();
		imagepng($barcodeResource);
		$barcodeImage = ob_get_clean();
		imagedestroy($barcodeResource);

		$barcodePath = $this->saveBarcodeToMedia($barcodeImage, $dtdcawbnumber);
		$imageDelhivery = new \Zend_Pdf_Resource_Image_Png($barcodePath);
		$width = $imageDelhivery->getPixelWidth();
		$height = $imageDelhivery->getPixelHeight();

		/** LEFT: Text Section */
		$page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
		$this->_setFontBold($page, 10);
		$page->drawText((string)($courier_name . ' Shipping Details:'), 35, $this->y, 'UTF-8');
		$this->y -= 15;

		$this->_setFontRegular($page, 10);
		$page->drawText((string)('Tracking Number: ' . $dtdcawbnumber), 35, $this->y, 'UTF-8');
		$this->y -= 15;

		$this->_setFontBold($page, 10);
		$page->drawText('Return Address:', 35, $this->y, 'UTF-8');
		$this->y -= 15;
		$this->_setFontRegular($page, 10);

		// Select return address
		if (!empty($courier_name)) {
			if ($origin_state === 'Telangana') {
				$address = $this->getScopeConfig('clickpost/clickpostservices/clickpost_return_address');
			} elseif ($origin_state === 'Maharashtra') {
				$address = $this->getScopeConfig('clickpost/clickpostservices/clickpost_return_address_mh');
			} else {
				$address = $this->getScopeConfig('configuration/services/return_address');
			}
		} else {
			$address = $this->getScopeConfig('configuration/services/return_address');
		}

		$address = (string)$address;
		$addressLines = explode("\n", wordwrap(strip_tags($address), 50, "\n"));
		foreach ($addressLines as $line) {
			if (trim($line) !== '') {
				$page->drawText($line, 35, $this->y, 'UTF-8');
			}
			$this->y -= 12;
		}

		/** RIGHT: Barcode Section */
		$barcodeX1 = 400;
		$barcodeY1 = $top - $height;
		$barcodeX2 = $barcodeX1 + $width;
		$barcodeY2 = $top;
		$page->drawImage($imageDelhivery, $barcodeX1, $barcodeY1, $barcodeX2, $barcodeY2);

		/** Footer note */
		$this->_setFontBold($page, 10);
		$this->y -= 3;
		$page->drawText('Pre-Paid', 35, $this->y, 'UTF-8');
		$this->y -= 10;
	}

	 /**
     * Save barcode image to pub/media/barcodes/ and return full path.
     */
    private function saveBarcodeToMedia($binaryData, $awbNumber)
    {
        $mediaDir = BP . '/pub/media/barcodes/';
        if (!is_dir($mediaDir)) {
            mkdir($mediaDir, 0775, true);
        }

        $filePath = $mediaDir . 'barcode_' . preg_replace('/[^A-Za-z0-9]/', '_', $awbNumber) . '.png';
        file_put_contents($filePath, $binaryData);
        return $filePath;
    }



     protected function insertFexexLabel($page, $order) {
        
        // get stringbarcode
        $orderId = $order->getId();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderObj = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');

        $order = $orderObj->get($orderId);
        $orderIncrementId = $order->getIncrementId();
        // get fedex 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fedCollectionObj = $objectManager->create('Infomodus\Fedexlabel\Model\ResourceModel\Items\CollectionFactory');
        
        $fedexCollection = $fedCollectionObj->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('order_increment_id', $orderIncrementId)
            ->addFieldToFilter('lstatus', '0');
        
        $fedexCollection->setOrder('fedexlabel_id', 'DESC');
        $fedexCollection->getSelect()->limit(1);

         if($fedexCollection->getFirstItem()->getData('fedexlabel_id')){

            $this->y = $this->y ? $this->y : 100;
            $top = $this->y;

            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
            $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
            $this->_setFontRegular($page, 10);

            $this->y = $this->y - 100;

            foreach($fedexCollection as $fed){
                $fedexData = json_decode($fed->getData('responseData'));
            }
            $text = $fedexData->stringBarcode;
            $config = new \Zend_Config([
                'barcode' => 'code128',
                'barcodeParams' => [
                    'text' => $text,
                    'drawText' => true
                ],
                'renderer' => 'image',
                'rendererParams' => ['imageType' => 'png']
            ]);

             $barcodeResource = Barcode::factory($config)->draw();
             ob_start();
            imagepng($barcodeResource);
            $barcodeImage = ob_get_clean();
            $imageFedex = new \Zend_Pdf_Resource_Image_Png('data:image/png;base64,'.base64_encode($barcodeImage));


            $width = $imageFedex->getPixelWidth();
            $height = $imageFedex->getPixelHeight();
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));

            // first line
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $this->y = $top;// - 40;
             $fedex = $this->getFedexInstructions($order);
            $page->drawText(strip_tags(ltrim('Fedex Shipping Details: ')), 35, $this->y, 'UTF-8');
            $this->y -= 15;
            foreach($fedex as $key => $value){
                // $this->_setFontRegular();
                // $this->setFontSize(20);
                if($key == 'trackingnumber'){
                    $text = 'Tracking Number. : '.$value;
                    $page->drawText(strip_tags(ltrim($text)), 35, $this->y, 'UTF-8');
                }
                if($key == 'formid'){
                    $text = 'Form ID. : '.$value;
                    $page->drawText(strip_tags(ltrim($text)), 35, $this->y, 'UTF-8');

                }
                
                if($key == 'operational_instructions'){
                    $text = $value;
                    $page->drawText(strip_tags(ltrim($text)), 35, $this->y, 'UTF-8');
                }
                if($key == 'ursaCode'){
                    // $this->_setFontBold();
                    $text = $value;
                    $page->drawText(strip_tags(ltrim($text)), 35, $this->y, 'UTF-8');
                }
                $this->y -= 15;
            }
            $page->drawImage($imageFedex, 300, $top -$height, $top + 50, $top);


        }
    }
    public function getFedexInstructions($order)
    {
        $orderId = $order->getId();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderObj = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');

        $order = $orderObj->get($orderId);
        $orderIncrementId = $order->getIncrementId();
        // get fedex 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $fedCollectionObj = $objectManager->create('Infomodus\Fedexlabel\Model\ResourceModel\Items\CollectionFactory');
        $fedCollectionObj = $objectManager->create('Retailinsights\FedExCustom\Model\ResourceModel\FedexResponseData\CollectionFactory');
        
        $fedexCollection = $fedCollectionObj->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('order_id', $orderId);
        
        $fedexCollection->setOrder('id', 'DESC');
        $fedexCollection->getSelect()->limit(1);
        if($fedexCollection)
        {
            foreach($fedexCollection as $fed){
                $fedexData['order_id'] = $fed->getData('order_id');
                $fedexData['trackingnumber'] = $fed->getData('trackingnumber');
                $fedexData['stringbarcode'] = $fed->getData('stringbarcode');
                $fedexData['formid'] = $fed->getData('formid');
                $fedexData['ursaCode'] = $fed->getData('ursaCode');
                $fedexData['operational_instructions'] = $fed->getData('operational_instructions');
                // $fedexData = json_decode($fed->getData('responseData'), true);
            }
            return $fedexData;
        }
        return 'no-fedex';
    }

	/**
     * Insert order to pdf page.
     *
     * @param \Zend_Pdf_Page $page
     * @param \Magento\Sales\Model\Order $obj
     * @param bool $putOrderId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
     if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 80);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($page, 10);

        if ($putOrderId) {
            $page->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top -= 30, 'UTF-8');
            $top +=15;
        }

        $top -=30;
        $page->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $order->getStore(),
                    $order->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top,
            'UTF-8'
        );

		$shipping_address =  $order->getShippingAddress();
	    $postCode = $shipping_address->getPostcode();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('centralbooks_zones'); 
		
		$zones_sql = "Select zone FROM " . $tableName." Where pincode =".$postCode;
		$zones_result = $connection->fetchRow($zones_sql);
		if(!empty($zones_result)){
			$zone_value = $zones_result['zone'];

			$top -= 5;
			//$page->drawRectangle(25, $top, 110, $top - 30);
            $top -= 21;
			//$page->setFillColor(new \Zend_Pdf_Color_Rgb(255, 255, 255));
			$page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
			$this->_setFontRegular($page, 18);
			$this->_setFontBold($page, 18);
			$page->drawText(__(strip_tags(ltrim($zone_value))), 45, $top, 'UTF-8');
			$top -= 20;
		} else {
		 $top -= 20;
		}
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        //$page->drawRectangle(25, $top, 275, $top - 25);
		$page->drawRectangle(25, $top, 570, $top - 25);
       // $page->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
		//echo "<pre>";print_r ($order->getBillingAddress());die;
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
            );
            $shippingMethod = $order->getShippingDescription();
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        //$page->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            //$page->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
			 $page->drawText(__('Ship to:'), 35, $top - 15, 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        //$addressesHeight = $this->_calcAddressHeight($billingAddress);
		$addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        //$page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
		$page->drawRectangle(25, $top - 25, 570, $top - 70 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        /*
		foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $this->rtlTextHandler->reverseRtlText($_value);
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }*/

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $this->rtlTextHandler->reverseRtlText($_value);
                    }
                    foreach ($text as $part) {
                        //$page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
						$page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                        $this->y -= 14;
                    }
                }
            }
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerEmail = $order->getCustomerEmail();
            $student_name = $order->getStudentName();
            $roll_no = $order->getRollNo();
			if($customerEmail){
			//echo'<pre>';print_r($order->debug());
			$CustomerModel = $objectManager->create('Magento\Customer\Model\Customer');
			//$CustomerModel->setWebsiteId(1); **//Here 1 means Store ID**
			$CustomerModel->loadByEmail($customerEmail);
			$customerId = $CustomerModel->getId();
            if($customerId){
            $customerRepository = $objectManager->get('Magento\Customer\Api\CustomerRepositoryInterface');
			$customer = $customerRepository->getById($customerId);
			$registered_number = $customer->getCustomAttribute('mobile_number')->getValue();
			
			$page->drawText(__('Registered T :'.$registered_number), 35, $this->y, 'UTF-8');
			$this->y -= 14; 
			   }
			}
            //$page->drawText(__('Student Name: '.$student_name), 35, $this->y, 'UTF-8');
			//$this->y -= 14;
            $page->drawText(__('Roll Number: '.$roll_no), 35, $this->y, 'UTF-8');
			$this->y -= 14; 

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Payment Method:'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "("
                . __('Total Shipping Charges')
                . " "
                . $order->formatPriceTxt($order->getShippingAmount())
                . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $page->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $page->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $page->drawLine(570, $currentY, 570, $methodStartY);
            //right

            $this->y = $currentY;
            $this->y -= 15;
        }
    }

	

	public function getScopeConfig($configPath)
	 { 
	  return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }

	 public function newPage(array $settings = [])
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
		//$page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
		//$page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A6);
		//$page = new Zend_Pdf_Page('297:420');
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }
}