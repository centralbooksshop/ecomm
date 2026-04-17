<?php
/**
 *
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Controller\Adminhtml\Item;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbContentSlider::item';
    
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        //get current selected slide id and save to session for use in other context
        $slideId = $this->getRequest()->getParam('slide_id');
        if ($slideId) {
            $this->_objectManager->get('Magento\Backend\Model\Session')->setSlideId($slideId);
        } else {
            $slideId = $this->_objectManager->get('Magento\Backend\Model\Session')->getSlideId();
        }
        if ($slideId){
            $model = $this->_objectManager->create('Ubertheme\UbContentSlider\Model\Slide');
            $model->load($slideId);
            $title = $model->getTitle()." ({$model->getIdentifier()})";
        } else {
            $title = null;
        }
        
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ubertheme_UbContentSlider::slide');
        $resultPage->addBreadcrumb(__('UbContentSlider'), __('UbContentSlider'));
        $resultPage->addBreadcrumb(__('Manage Slide Items'), __('Manage Slide Items'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Slide Items: %1', $title));

        return $resultPage;
    }
}
