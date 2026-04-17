<?php
namespace Infomodus\Fedexlabel\Model\Config;

class FrontShippingMethod implements \Magento\Framework\Option\ArrayInterface
{
    protected $shippingConfig;

    public function __construct(\Magento\Shipping\Model\Config $shippingConfig)
    {
        $this->shippingConfig = $shippingConfig;
    }

    public function toOptionArray($isMultiSelect = false)
    {
        $option = array(array('value'=>'', 'label'=> __('--Please Select--')));
        $_methods = $this->shippingConfig->getActiveCarriers();
        foreach ($_methods as $_carrierCode => $_carrier) {
            if ($_carrierCode !=="ups" && $_carrierCode !=="dhl" && $_carrierCode !=="usps"
                && $_method = $_carrier->getAllowedMethods()) {
                $_title = $_carrierCode;
                foreach ($_method as $_mcode => $_m) {
                    $_code = $_carrierCode . '_' . $_mcode;
                    $option[] = array('label' => "(".$_title.")  ". $_m, 'value' => $_code);
                }
            }
        }
        return $option;
    }
}
