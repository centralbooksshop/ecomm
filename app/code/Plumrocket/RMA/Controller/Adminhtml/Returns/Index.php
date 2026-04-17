<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml\Returns;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Plumrocket\RMA\Controller\Adminhtml\Returns
{

    /**
     * Show returns list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $currentDefaultManager = $this->configHelper->getDefaultManagerId();
        $user = $this->userFactory->create()->load($currentDefaultManager);
        if (null === $user->getId()) {
            $url = $this->_url->getUrl('adminhtml/system_config/edit', ['section' => 'prrma']);
            $url .= '#prrma_newrma_default_manager';
            $this->messageManager->addComplexErrorMessage('prMissingRmaManagerMessage', ['url' => $url]);
        }

        $title = __('Manage Returns');
        /** @var \Magento\Backend\Model\View\Result\Page $pageResult */
        $pageResult = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $pageResult->setActiveMenu('Plumrocket_RMA::returns');
        $pageResult->getConfig()->getTitle()->prepend($title);
        $pageResult->addBreadcrumb($title, $title);
        return $pageResult;
    }
}
