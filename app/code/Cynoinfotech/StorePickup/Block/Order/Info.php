<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Order;

use Magento\Sales\Model\Order\Address;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;

class Info extends \Magento\Sales\Block\Order\Info
{
    protected $_template = 'Cynoinfotech_StorePickup::order/info.phtml';
    
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        \Cynoinfotech\StorePickup\Model\StorePickupOrderFactory $storepickuporder,
        PaymentHelper $paymentHelper,
        AddressRenderer $addressRenderer,
        array $data = []
    ) {
        $this->storepickuporder = $storepickuporder;
        parent::__construct($context, $registry, $paymentHelper, $addressRenderer, $data);
    }
    
    public function getStorepickupId($id)
    {
        $storepickup_data = $this->storepickuporder
            ->create()->getCollection()
            ->addFieldToSelect(
                ['pickup_address','calendar_inputField']
            )->addFieldToFilter('order_id', $id);
        return  $storepickup_data->getData();
    }
}
