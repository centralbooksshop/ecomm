<?php
declare(strict_types=1);

namespace Centralbooks\SchoolHub\Controller\Adminhtml\Schoolhub;

class Edit extends \Centralbooks\SchoolHub\Controller\Adminhtml\Schoolhub
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
        $id = $this->getRequest()->getParam('schoolhub_id');
        $model = $this->_objectManager->create(\Centralbooks\SchoolHub\Model\Schoolhub::class);
        
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Schoolhub no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('centralbooks_schoolhub_schoolhub', $model);
        
        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Schoolhub') : __('New Schoolhub'),
            $id ? __('Edit Schoolhub') : __('New Schoolhub')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Schoolhubs'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Schoolhub %1', $model->getId()) : __('New Schoolhub'));
        return $resultPage;
    }
}

