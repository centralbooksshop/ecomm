<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Block\Adminhtml\Order\Creditmemo\Create;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Form
{
    protected $storepickuporder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Cynoinfotech\StorePickup\Model\StorePickupOrderFactory $storepickuporder,
        array $data = []
    ) {
        $this->_adminHelper = $adminHelper;
        $this->_coreRegistry = $registry;
        $this->storepickuporder = $storepickuporder;
        parent::__construct($context, $registry, $adminHelper, $data);
    }
    
    public function getStorepickupId($id)
    {
        $storepickup_data = $this->storepickuporder
            ->create()->getCollection()
            ->addFieldToSelect(
                ['pickup_address','calendar_inputField']
            )
            ->addFieldToFilter('order_id', $id);
        return  $storepickup_data->getData();
    }
}
