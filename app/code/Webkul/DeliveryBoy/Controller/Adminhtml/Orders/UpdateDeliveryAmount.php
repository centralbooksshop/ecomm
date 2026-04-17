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
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Orders;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

class UpdateDeliveryAmount extends \Magento\Backend\App\Action
{
    /**
     * @var \Webkul\DeliveryBoy\Model\OrderFactory
     */
    protected $deliveryboyOrderFactory;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrderFactory
    ) {
        parent::__construct($context);
        $this->deliveryboyOrderFactory = $deliveryboyOrderFactory;
    }

    /**
     * Assign Order To deliveryboy.
     *
     * @return \Magetno\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $order = $this->deliveryboyOrderFactory->create()->load($data['id']);
        if ($order) {
            $packageItems = intval($data['noofcovers']) . ' Covers,'.intval($data['noofboxes']).' Boxes';
            $order->setPackageItems($packageItems);
            $order->setDeliveryAmount($data['delivery_amount']);
            $order->setComments($data['comments']);
            $order->save();
            $this->messageManager->addSuccess(__('Delivery Amount updated.'));
        } else {
            $this->messageManager->addError(__('Invalid Submission.'));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
