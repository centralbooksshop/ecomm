<?php
declare(strict_types=1);

namespace Centralbooks\LocationCode\Controller\Adminhtml;

abstract class Locationcode extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Centralbooks_LocationCode::top_level';
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
            ->addBreadcrumb(__('Locationcode'), __('Locationcode'));
        return $resultPage;
    }
}

