<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Controller\Adminhtml\Index;

class Edit extends \Cynoinfotech\StorePickup\Controller\Adminhtml\StorePickup
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
     * @param \Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     *
     */
    
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
           $this->backendSession = $backendSession;
           $this->resultPageFactory = $resultPageFactory;
           parent::__construct($storepickupFactory, $registry, $resultRedirectFactory, $context);
    }
    
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        
        /** @var \Cynoinfotech\StorePickup\Model\StorePickup $storepickup */
        $storepickup = $this->_initStorePickup();
        
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cynoinfotech_StorePickup::storepickup');
        if ($id) {
            $storepickup->load($id);
            if (!$storepickup->getId()) {
                $this->messageManager->addError(__('This Store no longer exists.'));
                $resultRedirect = $this->_resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'storepickup/*/edit',
                    [
                        'entity_id' => $storepickup->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        
        $title = $storepickup->getId() ? $storepickup->getName() : __('New Store');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $data = $this->backendSession->getData('storepickup_data', true);
        if (!empty($data)) {
            $storepickup->setData($data);
        }
        return $resultPage;
    }
}
