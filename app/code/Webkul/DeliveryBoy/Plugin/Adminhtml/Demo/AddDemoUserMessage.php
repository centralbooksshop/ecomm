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

class AddDemoUserMessage
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
     * Add demo message at deliveryboy config page.
     *
     * @param \Magento\Config\Controller\Adminhtml\System\Config\Edit $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterExecute(
        \Magento\Config\Controller\Adminhtml\System\Config\Edit $subject,
        $result
    ) {
        $section = $this->request->getParam('section');
        if ($section !== 'deliveryboy') {
            return $result;
        }
        if ($this->deliveryboyHelper->isNotDemoUser()) {
            return $result;
        }
        $demoUserMessage = $this->deliveryboyHelper->getDemoUserMessage();
        $this->messageManager->addNoticeMessage($demoUserMessage);

        return $result;
    }
}
