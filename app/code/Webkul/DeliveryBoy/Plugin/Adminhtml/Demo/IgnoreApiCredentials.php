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
namespace Webkul\DeliveryBoy\Plugin\Adminhtml\Demo;

class IgnoreApiCredentials
{
    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->deliveryboyHelper = $deliveryboyHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * Ignore authentication for demo user.
     *
     * @param \Magento\Config\Controller\Adminhtml\System\Config\Save $subject
     * @return null
     */
    public function beforeExecute(
        \Magento\Config\Controller\Adminhtml\System\Config\Save $subject
    ) {
        $section = $this->request->getParam('section');
        if ($section !== 'deliveryboy') {
            return null;
        }
        if ($this->deliveryboyHelper->isNotDemoUser()) {
            return null;
        }
        $groups = $this->request->getPost('groups');
        unset($groups['auth']);
        $this->request->setPostValue('groups', $groups);
        
        return null;
    }
}
