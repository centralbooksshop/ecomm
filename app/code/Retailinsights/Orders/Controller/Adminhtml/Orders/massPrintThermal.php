<?php
namespace Retailinsights\Orders\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
//use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Retailinsights\Orders\Helper\ThermalPrinter;

class MassPrintThermal extends Action
{
    protected $filter;
    protected $collectionFactory;
    protected $thermalPrinterHelper;
    protected $fileFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ThermalPrinter $thermalPrinterHelper,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->thermalPrinterHelper = $thermalPrinterHelper;
        $this->fileFactory = $fileFactory;
    }


    public function execute()
    {
         $collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );

        if (!$collection->getSize()) {
            $this->messageManager->addErrorMessage(__('No orders selected.'));
            return $this->_redirect('sales/order');
        }

        $orders = [];

        foreach ($collection as $order) {
            $orders[] = $order;

            if ($order->getStatus() === 'processing') {
                $order->setStatus('assigned_to_picker');
                $order->addCommentToStatusHistory('assigned_to_picker');
                $order->save();
            } elseif (in_array(
				$order->getStatus(),
				['pending', 'canceled', 'closed', 'holded', 'payment_review'],
				true
			)) {

				$this->messageManager->addErrorMessage(__('Picking slip not downloaded.'));
                return $this->_redirect('sales/order');
				
			}

        }

        // Generate combined PDF and save it
        $pdfPath = $this->thermalPrinterHelper->generateCombinedPdf($orders);

		  // Send to thermal printer
        /*foreach ($orders as $order) {
            $content = $this->thermalPrinterHelper->generateThermalContent($order);
            $this->thermalPrinterHelper->sendToPrinter($content); // Make sure sendToPrinter() exists
        }*/
        
        // Encode file name to prevent 404
        $encodedFile = base64_encode(basename($pdfPath));

        // Redirect to download action
        return $this->_redirect('retailinsights_admin/orders/download', ['file' => $encodedFile]);
    }
}
