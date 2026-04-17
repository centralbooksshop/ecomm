<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsImportAwb
 * @author   	 CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ecom\Ecomexpress\Model;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;
use Zend_Barcode_Object_Ean13;

class Shippinglabel extends \Magento\Sales\Model\Order\Pdf\AbstractPdf 
{
	public function getPdf($shipmentIds=[])
	{
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$vid = $objectManager->get('Magento\Customer\Model\Session')->getVendorId();
        $waybills = array();
        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();  
        foreach($shipmentIds as $shipmentId)
        {
            $shipment = $objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);        
            //$vorder = $objectManager->create('Ced\CsMarketplace\Model\Vorders')->getVorderByShipment($shipment);
            $order= $objectManager->create('Magento\Sales\Model\Order')->load($shipment->getOrderId());         
            $trackCollection = $objectManager->create('Magento\Sales\Model\Order\Shipment\Track')->getCollection()
            ->addFieldToFilter('parent_id',$shipmentId);          
            foreach ($trackCollection as $track)              
                $waybills=$track->getNumber();    

	        $flag = false;
            $page = $this->newPage();
	        if($waybills){

                try{ 
                    $this->y = $this->y ? $this->y : 815;
                    $image = $this->_scopeConfig->getValue(
                        'carriers/ecomexpress/logo',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $order->getStoreId()
                    );
                    if ($image) {
                        $imagePath = '/sales/store/logo/' . $image;
                        if ($this->_mediaDirectory->isFile($imagePath)) {
                            $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
                            $top = 830;
                            //top border of the page
                            $widthLimit = 150;
                            //half of the page width
                            $heightLimit = 150;
                            //assuming the image is not a "skyscraper"
                            $width = $image->getPixelWidth();
                            $height = $image->getPixelHeight();

                            //preserving aspect ratio (proportions)
                            $ratio = $width / $height;
                            if ($ratio > 1 && $width > $widthLimit) {
                                $width = $widthLimit;
                                $height = $width / $ratio;
                            } elseif ($ratio < 1 && $height > $heightLimit) {
                                $height = $heightLimit;
                                $width = $height * $ratio;
                            } elseif ($ratio == 1 && $height > $heightLimit) {
                                $height = $heightLimit;
                                $width = $widthLimit;
                            }

                            $y1 = $top - $height;
                            $y2 = $top;
                            $x1 = 25;
                            $x2 = $x1 + $width;

                            //coordinates after transformation are rounded by Zend
                            $page->drawImage($image, $x1, $y1, $x2, $y2);

                            $this->y = $y1 - 10;
                            $top = $this->y;
                        }
                    }
                    $this->_setFontBold($page, 15);
                    $page->drawText(__('ECOM EXPRESS'), 240, $top, 'UTF-8');
                    $Barcodeimagepath= $objectManager->create('Magento\Framework\Module\Dir\Reader')->getModuleDir('view', 'Ecom_Ecomexpress').'/adminhtml/web/images/barcode.jpeg';    
                    //echo $Barcodeimagepath;die;                         
                    $barcodeOptions = array(
                            'text' => trim($waybills),
                            'drawtext'=>false,
                    ); 

                    $rendererOptions = array();
                    $imageResource =    \Zend_Barcode::draw(
                        'code128', 'image', $barcodeOptions, $rendererOptions
                    );
                         
                    imagejpeg($imageResource, $Barcodeimagepath, 100);
                    imagedestroy($imageResource);
                    $image = \Zend_Pdf_Image::imageWithPath($Barcodeimagepath);
                         
                    $page->drawImage($image, 190,$top-5,400 ,$top-40 ); 

                    $this->_setFontBold($page, 14);
                    $page->drawText(__('[ PPD ]'), 35, $top-25 , 'UTF-8');
                    $page->drawText(__('[ DHQ/DL ]'), 480, $top-25 , 'UTF-8');
                    $top -= 40;
                    $page->drawText($waybills, 260, $top-12 , 'UTF-8');
                    $top -= 30;
                    $this->y = $top;
                    //$page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                    $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
                    $page->setLineWidth(0.7);
                    $page->drawRectangle(25, $this->y, 570, $this->y - 25);
                    //$page->drawRectangle(275, $this->y, 570, $this->y - 25);
                    $top -= 20;
                    $this->y -= 15;
                    $this->_setFontBold($page, 12);
                    $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                    $page->drawText(__('Shipper : '), 35, $this->y, 'UTF-8');
                    $page->drawText(__('Order # : '), 280, $this->y, 'UTF-8');
                    $this->_setFontRegular($page, 12);
                    $page->drawText(__('ECOM EXPRESS PRIVATE LIMITED'), 85, $this->y, 'UTF-8');
                    $page->drawText( $order->getRealOrderId(), 330, $this->y, 'UTF-8');
                    $top -= 20;
                    //$page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                    //$page->setLineColor(new \Zend_Pdf_Color_GrayScale(1));
                    $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
                    $page->setLineWidth(0.7);
                    $page->drawRectangle(25, $top, 275, $top - 25);
                    $page->drawRectangle(275, $top, 570, $top - 25);
                    $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                    //die('=====');
                    /* Calculate blocks info */
                    
                    /* Billing Address */
                    $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));
                    $shippingfrom=array();
                    
                    $region = $objectManager->create('Magento\Directory\Model\Region')->load($this->_scopeConfig->getValue('general/store_information/region_id'),'region_id')->getDefaultName();
                    $shippingfrom = 'Store - '.$this->_scopeConfig->getValue('general/store_information/name');
                    $shippingfrom .= ', '.$this->_scopeConfig->getValue('general/store_information/street_line1')." ".$this->_scopeConfig->getValue('general/store_information/street_line2');
                    $shippingfrom .= ', '.$this->_scopeConfig->getValue('general/store_information/city');
                    $shippingfrom .= ', '."Zipcode - ".$this->_scopeConfig->getValue('general/store_information/postcode');
                
                    $shippingfrom .= ', '.$region.' , '.$this->_scopeConfig->getValue('shipping/origin/country_id');
                    $shippingfrom .= ', Mobile - '.$this->_scopeConfig->getValue('general/store_information/phone');
                    if($vat = $this->_scopeConfig->getValue('general/store_information/merchant_vat_number'))
                        $shippingfrom .= ' GST - '.$vat;
                    /* Payment */

                    $items = '';
                    $name = '';
                    $qty = 0;
                    $weight = 0;
                    $sku = array();
                    foreach ($shipment->getAllItems() as $item) {
                        if ($item->getOrderItem()->getParentItem()) {
                            continue;
                        }
                        $name .= $name ? ', '.$item->getName() : $item->getName();
                        $qty += (int)$item->getQty();
                        $weight += $item->getWeight();
                        $sku[] = $item->getSku();
                    }
                    
                    $package_collection = $objectManager->get('Magento\Catalog\Model\Product')->getCollection()->addAttributeToSelect('*')
                         ->addAttributeToFilter('sku', array('in' => $sku));
                    //print_r($package_collection->getData());die('----');
                    $length =  $height = $width = 0;
                    if(count($package_collection->getData())){
                        foreach($package_collection as $packge_dimension){
                            //echo $packge_dimension->getEcomLength();die('----');
                             $length  += $packge_dimension->getEcomLength();             
                             $width +=  $packge_dimension->getEcomBreadth();
                             $height += $packge_dimension->getEcomHeight();
                        }
                    }
                    if(!$length || !$height || !$width)
                        $length =  $height = $width = 10;
                    $items =   'Item Description : '.$name;
                    $items .= ', Quantity : '.$qty;
                    $items .= ', Dimension : '.$length.'*'.$width.'*'.$height;
                    $items .= ', Actual Weight : '.$weight;
                    $items .= ', Order Date : '.$order->getCreatedAt();

                    /* Shipping Address and Method */
                    if (!$order->getIsVirtual()) {
                        /* Shipping Address */
                        $shippingAddress = $this->_formatAddress($this->addressRenderer->format($order->getShippingAddress(), 'pdf'));
                        $shippingMethod = $order->getShippingDescription();
                    }

                    $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                    $this->_setFontBold($page, 12);
                    $page->drawText(__('Consignee Details:'), 35, $top - 15, 'UTF-8');

                    //if (!$order->getIsVirtual()) {
                    $page->drawText(__('Shipper Details:'), 285, $top - 15, 'UTF-8');
                    /*} else {
                        $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
                    }*/
                    $addressesHeight = $this->_calcAddressHeight($billingAddress);
                    /*$addressesHeight = $this->_calcAddressHeight($shippingfrom);
                    if (isset($shippingAddress)) {
                        $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
                    }*/

                    $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
                    $page->drawRectangle(25, $top - 25, 570, $top - 60 - $addressesHeight);
                    $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                    $this->_setFontRegular($page, 11);
                    $this->y = $top - 40;
                    $addressesStartY = $this->y;
                    foreach (explode(",", $items) as $value) {
                        if ($value !== '') {
                            $text = [];
                            foreach ($this->string->split($value, 45, true, true) as $_value) {
                                $text[] = $_value;
                            }
                            foreach ($text as $part) {
                                $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                                $this->y -= 15;
                            }
                        }
                    }                   
                    $addressesEndY = $this->y;

                    //if (!$order->getIsVirtual()) {
                        $this->y = $addressesStartY;
                        foreach ($shippingAddress as $value) {
                            if ($value !== '') {
                                $text = [];
                                foreach ($this->string->split($value, 45, true, true) as $_value) {
                                    $text[] = $_value;
                                }
                                foreach ($text as $part) {
                                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                                    $this->y -= 15;
                                }
                            }
                        }
                        //$this->y -=15;
                        $addressesEndY = min($addressesEndY, $this->y);
                        $page->drawLine(275, $top - 25, 275, $addressesEndY-20);
                        $this->y -= 35;
                        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
                        $page->drawRectangle(25, $this->y, 570, $this->y - 70);
                        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                        $this->_setFontBold($page, 12);
                        $page->drawText(__('IF UNDELIVERED RETURN TO'), 200, $this->y-15, 'UTF-8');
                        $page->drawLine(25, $this->y-25, 570, $this->y-25);
                        $this->y = $this->y-40;
                        $this->_setFontRegular($page, 11);
                        if ($shippingfrom !== '') {
                            $text = [];
                            //echo strlen($shippingfrom);die;
                            if(strlen($shippingfrom)>100){
                                $text = $this->string->split($shippingfrom, 110, true, true);
                                //print_r( $text);die;
                                foreach ($text as $part) {
                                    
                                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                                    $this->y -= 15;
                                }                 
                            }else{
                                $page->drawText($shippingfrom, 35, $this->y, 'UTF-8');
                            }         
                        }
                        $this->y -= 65;
                    }catch(\Exception $e){
                        echo $e->getMessage(); 
                    }           
                //}   
            }             
       }
      
       $this->_afterGetPdf();               
       return $pdf;	
	}  

    /**
     * Set font as regular
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_It-2.8.2.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set PDF object
     *
     * @param  \Zend_Pdf $pdf
     * @return $this
     */
    protected function _setPdf(\Zend_Pdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * Retrieve PDF object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Zend_Pdf
     */
    protected function _getPdf()
    {
        if (!$this->_pdf instanceof \Zend_Pdf) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please define the PDF object before using.'));
        }

        return $this->_pdf;
    }

}