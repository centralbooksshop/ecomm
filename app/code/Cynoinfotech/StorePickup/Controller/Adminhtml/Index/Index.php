<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
    /**
     * page result factory
     *
     * @var \Magento\Framework\View\Resulr\PageFactory
     */
    
    protected $resultPageFactory;
    /**
     * Page factory
     *
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
         parent::__construct($context);
         $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
        $this->_setPageData();
        return $this->getResultPage();
    }
    
    public function getResultPage()
    {
        if ($this->resultPage == null) {
            $this->resultPage = $this->resultPageFactory->create();
        }
        return $this->resultPage;
    }
    
    public function _setPageData()
    {
        $resultPage = $this->getResultPage();
        $resultPage->setActiveMenu('Cynoinfotech_StorePickup::storepickup');
        
        $title = 'Manage Stores';
        $resultPage->getConfig()->getTitle()->prepend($title);
        
        $resultPage->addBreadcrumb(__('Cynoinfotech'), __('Cynoinfotech'));
        $resultPage->addBreadcrumb(__('Stores'), __('Manage Stors'));

        return $this;
    }
}
