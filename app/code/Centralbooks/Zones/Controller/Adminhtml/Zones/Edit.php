<?php
declare(strict_types=1);

namespace Centralbooks\Zones\Controller\Adminhtml\Zones;

class Edit extends \Centralbooks\Zones\Controller\Adminhtml\Zones
{

    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('zones_id');
        $model = $this->_objectManager->create(\Centralbooks\Zones\Model\Zones::class);
        
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Zones no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('centralbooks_zones_zones', $model);
        
        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Zones') : __('New Zones'),
            $id ? __('Edit Zones') : __('New Zones')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Zoness'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Zones %1', $model->getId()) : __('New Zones'));
        return $resultPage;
    }
	 /**
     * Authorize current admin user.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Centralbooks_Zones::centralbooks_zones_zones");
    }
}

