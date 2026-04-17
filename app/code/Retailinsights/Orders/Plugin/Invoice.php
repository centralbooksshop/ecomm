<?php
namespace Retailinsights\Orders\Plugin;

use \Laminas\Barcode\Barcode;
use \Laminas\Config\Config;
 
class Invoice
{
   protected $_checkoutSession;


   public function __construct(
      \Magento\Checkout\Model\Session $checkoutSession,
      \Magento\Sales\Model\Order\Invoice $invoice,
      \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
      \Magento\Sales\Model\OrderFactory $order,
      \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
  )
  {
   $this->orderRepository = $orderRepository;
      $this->_checkoutSession = $checkoutSession;
      $this->invoiceRepository = $invoiceRepository;
      $this->invoice = $invoice;
      $this->order = $order;
  }
   public function beforeInsertDocumentNumber($subject, $page, $text) 
   {
          $docHeader = $subject->getDocHeaderCoordinates();
          $image = $this->_generateBarcode($text);
          $width = $image->getPixelWidth();
          $height = $image->getPixelHeight();
 
          $page->drawImage($image, $docHeader[2] - $width, $docHeader[1] -$height, $docHeader[2], $docHeader[1]);
  }
 
  protected function _generateBarcode($text) 
  {
     $config = new Config([
     'barcode' => 'code128',
     'barcodeParams' => [
     'text' => $this->_extractInvoiceNumber($text),
     'drawText' => true
     ],
     'renderer' => 'image',
     'rendererParams' => ['imageType' => 'png']
     ]);
 
     	
      $barcodeResource = Barcode::factory($config)->draw();
 
     ob_start();
     imagepng($barcodeResource);
     $barcodeImage = ob_get_clean();
 
     $image = new \Zend_Pdf_Resource_Image_Png('data:image/png;base64,'.base64_encode($barcodeImage));
 
     return $image;
  }
 
   protected function _extractInvoiceNumber($text) 
   {
         $array_of_words = explode("#", $text);
         $invoiceData = $this->invoice->loadByIncrementId(preg_replace('/[^A-Za-z0-9\-]/', '', $array_of_words[1]));
         
         $orderId = $invoiceData->getData('order_id');
         $order = $this->orderRepository->get(preg_replace('/[^A-Za-z0-9\-]/', '', $orderId));
         $orderIncrementId = $order->getIncrementId();
         return $orderIncrementId;
   }
}