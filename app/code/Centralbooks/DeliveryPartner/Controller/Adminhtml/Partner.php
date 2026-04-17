<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryPartner\Controller\Adminhtml;

abstract class Partner extends \Magento\Backend\App\Action
{

//	const ADMIN_RESOURCE = 'Centralbooks_DeliveryPartner::top_level';
	const ADMIN_RESOURCE = 'Centralbooks_DeliveryPartner::Partner';
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function initPage($resultPage)
    {
	   $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Centralbooks'), __('Centralbooks'))
            ->addBreadcrumb(__('Partner'), __('Partner'));
       return $resultPage; 
    }
}

