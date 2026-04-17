<?php

namespace Plumrocket\RMA\Controller\Adminhtml\Returns;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Plumrocket\RMA\Model\ReturnsFactory;
use Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory;
use Plumrocket\RMA\Model\Returns\ItemFactory;

class massIntransit extends Action
{
    protected $filter;
    protected $resultPageFactory;
    protected $collectionFactory;
    protected $ReturnsFactory;
    //private $scopeConfig;
    protected $itemFactory;
    protected $logger;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filter $filter,
        //ScopeConfigInterface $scopeConfig,
        ReturnsFactory $returnsModelFactory,
	CollectionFactory $collectionFactory,
	ItemFactory $itemFactory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        //$this->scopeConfig = $scopeConfig;
        $this->returnsModelFactory = $returnsModelFactory;
	$this->collectionFactory = $collectionFactory;
	$this->itemFactory = $itemFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
          try {
			//$selectedpost = $this->getRequest()->getPostValue('selected');
			$collection = $this->filter->getCollection($this->collectionFactory->create());
			//echo "<pre>";print_r($collection->getData());die;
            $updated = 0;
            foreach ($collection as $item) {
                //$model = $this->returnsModelFactory->create()->load($item['entity_id']);
                //$model->setData('status', $selectedpost);
                //$model->save();
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
				$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
				$connection = $resource->getConnection();
				$tableName = $resource->getTableName('plumrocket_rma_returns'); //gives table name with 
				$sql = "Update " . $tableName . " Set status = 'received' where entity_id = " .$item['entity_id'];
				$connection->query($sql);

				$itemCollection = $this->itemFactory->create()->getCollection()
                                              ->addFieldToFilter('parent_id', $item['entity_id']);
                                foreach ($itemCollection as $collectionData) {
                                        $returnItemData = $this->itemFactory->create()->load($collectionData->getEntityId());
                                        $qtyRequested = $returnItemData->getQtyRequested();
                                        $qtyAuthorized = $returnItemData->getQtyAuthorized();
                                        $qtyReceived = $returnItemData->getQtyReceived();
                                        if($qtyRequested > 0 && $qtyAuthorized > 0){
                                             $returnItemData->setQtyReceived($qtyRequested)->save();
                                        }
                                }
                $updated++;
            }
            if ($updated) {
                $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $updated));
            }

        } catch (\Exception $e) {
            //\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info($e->getMessage());
			$this->messageManager->addError(__($e->getMessage()));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    protected function _isAllowed()
    {
        return true;
    }
}
