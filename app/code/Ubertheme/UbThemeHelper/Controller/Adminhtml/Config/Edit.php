<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Controller\Adminhtml\Config;

class Edit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbThemeHelper::view_settings';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Ubertheme\UbThemeHelper\Model\Config\Structure $configStructure
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ubertheme\UbThemeHelper\Model\Config\Structure $configStructure,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->_configStructure = $configStructure;
        $this->resultPageFactory = $resultPageFactory;
        $this->_objectManager = $context->getObjectManager();
        $this->registry = $registry;
    }

    /**
     * Edit configuration section
     *
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        $section = $this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');
        $themeId = $this->getRequest()->getParam('themeId');

        /** @var \Magento\Config\Model\Config\Structure\Element\Section $activeSection */
        $activeSection = $this->_configStructure->getElement($section);
        if ($section && !$activeSection->isVisible($website, $store)) {
            $this->messageManager->addWarning(__('Scope of configuration was not supported.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath('adminhtml/*/', ['website' => $website, 'store' => $store]);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('menu')->setAdditionalCacheKeyInfo([$section]);
        $resultPage->addBreadcrumb(__('System'), __('System'), $this->getUrl('*\/system'));
        
        $themeModel = $this->_objectManager->get('\Magento\Theme\Model\Theme');
        $themeModel->load($themeId);
        
        $resultPage->getConfig()->getTitle()->prepend(__($themeModel->getThemeTitle()));

        return $resultPage;
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
}
