<?php
	namespace Retailinsights\Orders\Controller\Adminhtml\Rewrite\Order;
	
	use Magento\Framework\Controller\ResultFactory;

	use Magento\Framework\App\ResponseInterface;
	use Magento\Framework\App\Filesystem\DirectoryList;
	use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
	use Magento\Ui\Component\MassAction\Filter;
	use Magento\Sales\Model\Order\Pdf\Invoice;
	use Magento\Framework\Stdlib\DateTime\DateTime;
	use Magento\Framework\App\Response\Http\FileFactory;
	use Magento\Backend\App\Action\Context;
	use Magento\Framework\Controller\ResultInterface;
	use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
	use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
	
	class Pdfinvoices extends \Magento\Sales\Controller\Adminhtml\Order\Pdfinvoices
	{
		protected $postFactory;

    public function __construct(
        \Retailinsights\Orders\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
    	\Retailinsights\Orders\Model\PostFactory $postFactory,
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DateTime $dateTime,
        FileFactory $fileFactory,
        Invoice $pdfInvoice
    ) {
        $this->postCollectionFactory = $postCollectionFactory;
    	$this->postFactory = $postFactory;
        parent::__construct($context, $filter, $collectionFactory, $dateTime, $fileFactory, $pdfInvoice);
    }
		 protected function massAction(AbstractCollection $collection)
    {
        $invoicesCollection = $this->collectionFactory->create()->setOrderFilter(['in' => $collection->getAllIds()]);
        
		$invoice_id='';
		foreach ($invoicesCollection as $invoices) {
            $invoice_id = $invoices->getEntityId();
            $OrderId = $invoices->getOrderId();
            $collection= $this->postCollectionFactory->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('order_id', $OrderId);
            $id = 0;
            $count = 0;

            foreach($collection as $data){
                $id = $data->getData('id');
                if($id){
                    $count =  $data->getData('invoice_count') + 1;
                }else{
                    $id = 0;
                }
            }
            if (!$invoicesCollection->getSize()) {
                $this->messageManager->addErrorMessage(__('There are no printable documents related to selected orders.'));
                return $this->resultRedirectFactory->create()->setPath($this->getComponentRefererUrl());
            }

            if($invoice_id!=''){
                if($id > 0){
                    $mandeetotcol = $this->postFactory->create();
                    $postUpdate = $mandeetotcol->load($id);
                    $postUpdate->setInvoiceCount($count);
                    if($postUpdate->save()){
                    }

                }else{
                    $mandeetotcol = $this->postFactory->create();
                    $mandeetotcol->setOrderId($OrderId);
                    $mandeetotcol->setInvoiceId($invoice_id);
                    $mandeetotcol->setInvoiceCount(1);
                    if($mandeetotcol->save()){
                    }
                }
            }
		 } 

        $pdf = $this->pdfInvoice->getPdf($invoicesCollection->getItems());
        $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

        return $this->fileFactory->create(
            sprintf('invoice%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $fileContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}