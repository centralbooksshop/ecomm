<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Order\PrintOrder;

class Invoice extends \Magento\Sales\Block\Order\PrintOrder\Invoice
{
    protected $coreRegistry = null;
    protected $paymentHelper;
    protected $addressRenderer;
     
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Cynoinfotech\StorePickup\Model\StorePickupOrderFactory $storepickuporder,
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
