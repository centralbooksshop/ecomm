<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Order;

use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Controller\Customer as CustomerController;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;

class Index extends CustomerController
{

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        parent::__construct($context, $customerSession);
    }

    /**
     * SHow Delvieryboy Order.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if ($navigationBlock = $resultPage->getLayout()->getBlock("customer_account_navigation")) {
            $navigationBlock->setActive("expressdelivery/order");
        }
        if ($block = $resultPage->getLayout()->getBlock("express_order_list")) {
            $block->setRefererUrl($this->redirect->getRefererUrl());
        }
        $resultPage->getConfig()->getTitle()->set(__("My Express Orders"));
        return $resultPage;
    }
}
