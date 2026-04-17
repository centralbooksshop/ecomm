<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Controller\Adminhtml\Storeorder;

class Edit extends \Cynoinfotech\StorePickup\Controller\Adminhtml\StorePickupOrder
{
    /*
    * Backend Session
    *
    * @var \Magento\Backend\Model\Session
	*/
    protected $backendSession;
    
    /**
     * Page Factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * construct
     *
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Cynoinfotech\StorePickup\Model\StorePickupOrderFactory $storepickuporderFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     *
     */
    
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Cynoinfotech\StorePickup\Model\StorePickupOrderFactory $storepickuporderFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
           $this->backendSession = $backendSession;
           $this->resultPageFactory = $resultPageFactory;
           parent::__construct($storepickuporderFactory, $registry, $resultRedirectFactory, $context);
    }
    
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        
        /** @var \Cynoinfotech\StorePickup\Model\StorePickupOrder $storepickuporder */
        $storepickuporder = $this->_initStorePickupOrder();
        
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cynoinfotech_StorePickup::storepickuporder');
        if ($id) {
            $storepickuporder->load($id);
            if (!$storepickuporder->getId()) {
                $this->messageManager->addError(__('This Store order no longer exists.'));
                $resultRedirect = $this->_resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'storepickup/*/edit',
                    [
                        'entity_id' => $storepickuporder->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        
        $title = $storepickuporder->getId() ? $storepickuporder->getName() : __('New Store Order');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $data = $this->backendSession->getData('storepickuporder_data', true);
        if (!empty($data)) {
            $storepickuporder->setData($data);
        }
        return $resultPage;
    }
}
