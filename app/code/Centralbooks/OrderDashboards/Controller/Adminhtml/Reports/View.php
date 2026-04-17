<?php
namespace Centralbooks\OrderDashboards\Controller\Adminhtml\Reports;

use Magento\Framework\Controller\ResultFactory;

class View extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Centralbooks_OrderDashboards::sync_cronreports';
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    protected $_reportsFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry,
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Centralbooks\OrderDashboards\Model\CronReportsFactory $reportsFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->_reportsFactory = $reportsFactory;
    }

    /**
     * Mapped Grid List page.
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $rowId = (int) $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        if ($rowId) {
            $rowData = $this->_reportsFactory->create()->load($rowId);
            $this->coreRegistry->register('row_data', $rowData);

            if (!$rowData->getId()) {
                $this->messageManager->addError(__('row data no longer exist.'));
                $this->_redirect('dashboards/reports/index');
                return;
            }
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Updated Records'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Centralbooks_OrderDashboards::sync_cronreports');
    }
}
