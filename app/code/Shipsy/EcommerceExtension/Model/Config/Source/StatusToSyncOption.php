<?php
/**
 * My own options
 *
 */
namespace Shipsy\EcommerceExtension\Model\Config\Source;
class StatusToSyncOption 
{
    protected $orderStatus;

    public function __construct(
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatus
    ){
        $this->orderStatus = $orderStatus;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->orderStatus->toOptionArray();
    }
}

?>







