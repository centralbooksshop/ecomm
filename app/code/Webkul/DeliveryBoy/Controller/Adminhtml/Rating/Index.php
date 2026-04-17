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
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Rating;

class Index extends \Webkul\DeliveryBoy\Controller\Adminhtml\Rating
{
    /**
     * Show Deliveryboy Ratings.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Webkul_DeliveryBoy::rating");
        $resultPage->getConfig()->getTitle()->prepend(__("Manage Deliveryboy Rating"));
        return $resultPage;
    }

    /**
     * Authorize Current Admin User.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_DeliveryBoy::rating");
    }
}
