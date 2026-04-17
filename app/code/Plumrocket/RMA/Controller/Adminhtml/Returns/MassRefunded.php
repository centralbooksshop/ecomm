<?php

namespace Plumrocket\RMA\Controller\Adminhtml\Returns;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Plumrocket\RMA\Model\ReturnsFactory;
use Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory;

class MassRefunded extends Action
{
    protected $filter;
    protected $resultPageFactory;
    protected $collectionFactory;
    protected $ReturnsFactory;
    //private $scopeConfig;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filter $filter,
        //ScopeConfigInterface $scopeConfig,
        ReturnsFactory $returnsModelFactory,
        CollectionFactory $collectionFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        //$this->scopeConfig = $scopeConfig;
        $this->returnsModelFactory = $returnsModelFactory;
        $this->collectionFactory = $collectionFactory;
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
				$sql = "Update " . $tableName . " Set status = 'cancel_refund' where entity_id = " .$item['entity_id'];
				$connection->query($sql);
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