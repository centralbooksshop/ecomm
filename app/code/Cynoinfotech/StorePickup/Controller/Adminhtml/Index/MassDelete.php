<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Controller\Adminhtml\Index;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Mass Action Filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    
    protected $filter;
    
    /**
     * Collection Factory
     *
     * @var \Cynoinfotech\StorePickup\Model\ResourceModel\StorePickup\CollectionFactory
     */
    
    protected $collectionFactory;
    
    /**
     * Construct
     *
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Cynoinfotech\StorePickup\Model\ResourceModel\StorePickup\CollectionFactory $collectionFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    
    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Cynoinfotech\StorePickup\Model\ResourceModel\StorePickup\CollectionFactory $collectionFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
            $this->filter = $filter;
            $this->collectionFactory = $collectionFactory;
            parent::__construct($context);
    }
    
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    
    public function execute()
    {
        $deleteIds = $this->getRequest()->getParams('selected');
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['in' => $deleteIds]);

        $delete = 0;
        foreach ($collection as $item) {
            $item->delete();
            $delete++;
        }
        
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $delete));
        
         /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
         
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
