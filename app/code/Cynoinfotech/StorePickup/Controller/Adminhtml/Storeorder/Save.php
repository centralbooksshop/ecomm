<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Controller\Adminhtml\Storeorder;

class Save extends \Cynoinfotech\StorePickup\Controller\Adminhtml\StorePickupOrder
{
    protected $fileUploaderFactory;
    
    protected $fileSystem;
    
    /*
    *
    * Backend Session
    *
    * @var \Magento\Backend\Model\Session
    */
    
    protected $backendSession;
    
    /**
     * Construct
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\MediaStorage\Model\File\UploadFactory $fileuploader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $registry
     * @param \Cynoinfotech\StorePickup\Model\StorePickupOrderFactory $storepickuporderFactory
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     */
    
    public function __construct(
        \Magento\MediaStorage\Model\File\UploaderFactory $fileuploader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\Model\Session $backendSession,
        \Cynoinfotech\StorePickup\Model\StorePickupOrderFactory $storepickuporderFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->fileUploaderFactory = $fileuploader;
        $this->fileSystem = $filesystem;
        $this->backendSession = $backendSession;
        parent::__construct($storepickuporderFactory, $registry, $resultRedirectFactory, $context);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cynoinfotech_StorePickup::storeorder_save');
    }
    
    
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        $storepickuporder = $this->_initStorePickupOrder();
        
        if (isset($data)) {
			
		 //     if(!isset($data['entity_id']) && ($data['increment_id'] == $storepickuporder->getData('increment_id')))
        //     {
        //         $this->messageManager->addSuccess(__('Order already exists.'));

        //     }else{
                $state = '';
                $incrementId = $data['increment_id'];
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);
				$orderId = $orderInfo->getId();
				$order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
				if($data['order_status'] == 'processing'){
				$state = 'processing';
				} elseif($data['order_status'] == 'order_delivered'){
				$state = 'complete';
				} else {
                 $state = 'complete';
                }
				$status = $data['order_status'];
				$comment = 'Order Status is '.$status;
				$isNotified = false;
				$order->setState($state);
				$order->setStatus($status);
				$order->addStatusToHistory($order->getStatus(), $comment);
				$order->save();
				$storepickuporder->setData($data);
                
                $path = $this->fileSystem->getDirectoryRead(
                    \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
                )->getAbsolutePath('storepickuporder');
                
                try {
                    $storepickuporder->save();
                    $this->messageManager->addSuccess(__('The Store Order has been saved.'));
                    $this->backendSession->setStorepickuporderData(false);
                
                    if ($this->getRequest()->getParam('back')) {
                        $resultRedirect->setPath(
                            'storepickup/*/edit',
                            [
                                'entity_id' => $storepickuporder->getId(),
                                '_current' => true
                            ]
                        );
                        return $resultRedirect;
                    }
                    $resultRedirect->setPath('storepickup/*/');
                    return $resultRedirect;
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addError($e, __('Something went wrong while saving store'));
                }
                $this->_getSession()->setEventsData($data);
                $resultRedirect->setPath(
                    'storepickup/*/edit',
                    [
                        'entity_id' => $storepickuporder->getId(),
                        '_current' => true
                    ]
                );
            // }
            return $resultRedirect;
        }
        $resultRedirect->setPath('storepickup/*/');
        return $resultRedirect;
    }
}
