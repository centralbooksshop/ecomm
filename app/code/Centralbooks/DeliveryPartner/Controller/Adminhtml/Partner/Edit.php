<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryPartner\Controller\Adminhtml\Partner;

class Edit extends \Centralbooks\DeliveryPartner\Controller\Adminhtml\Partner
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
        $id = $this->getRequest()->getParam('partner_id');
        $model = $this->_objectManager->create(\Centralbooks\DeliveryPartner\Model\Partner::class);
        
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Partner no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('centralbooks_deliverypartner_partner', $model);
        
        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Partner') : __('New Partner'),
            $id ? __('Edit Partner') : __('New Partner')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Partners'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Partner %1', $model->getId()) : __('New Partner'));
        return $resultPage;
    }
}

