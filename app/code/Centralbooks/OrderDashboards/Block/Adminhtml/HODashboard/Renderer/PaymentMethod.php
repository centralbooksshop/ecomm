<?php

namespace Centralbooks\OrderDashboards\Block\Adminhtml\HODashboard\Renderer;

use Magento\Sales\Model\OrderFactory;

class PaymentMethod extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $orderFactory;

    public function __construct(OrderFactory $orderFactory)
    {
        $this->orderFactory = $orderFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $value = parent::render($row);
        $order = $this->orderFactory->create()->load($value);
		$payment = $order->getPayment();
		if(!empty($payment)){
        $method = $payment->getMethodInstance();
        $methodTitle = $method->getTitle();
		} else {
		 $methodTitle = '';
		}
        return $methodTitle;
    }
}