<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Fedexlabel\Model\Config;
class ShippingMethods implements \Magento\Framework\Option\ArrayInterface
{
    protected $shippingConfig;
    protected $config;

    public function __construct(\Magento\Shipping\Model\Config $shippingConfig, \Infomodus\Fedexlabel\Helper\Config $config)
    {
        $this->shippingConfig = $shippingConfig;
        $this->config = $config;
    }

    function toOptionArray($store=null){
        $option = [];
        $_methods = $this->shippingConfig->getActiveCarriers($store);
        foreach($_methods as $_carrierCode => $_carrier){
            if($_carrierCode !=="ups" && $_carrierCode !=="dhlint" && $_carrierCode !=="usps" && $_carrierCode !=="fedex" && $_method = $_carrier->getAllowedMethods())  {
                if(!$_title = $this->config->getStoreConfig('carriers/'.$_carrierCode.'/title', $store)) {
                    $_title = $_carrierCode;
                }
                foreach($_method as $_mcode => $_m){
                    $_code = $_carrierCode . '_' . $_mcode;
                    $option[] = ['label' => "(".$_title.")  ". $_m, 'value' => $_code];
                }
            }
        }
        return $option;
    }

    function getShippingMethodsSimple($store=null){
        $option = [];
        $_methods = $this->shippingConfig->getActiveCarriers($store);
        foreach($_methods as $_carrierCode => $_carrier){
            if($_carrierCode !=="ups" && $_carrierCode !=="dhlint" && $_carrierCode !=="usps" && $_carrierCode !=="fedex" && $_method = $_carrier->getAllowedMethods())  {
                if(!$_title = $this->config->getStoreConfig('carriers/'.$_carrierCode.'/title', $store)) {
                    $_title = $_carrierCode;
                }
                foreach($_method as $_mcode => $_m){
                    $_code = $_carrierCode . '_' . $_mcode;
                    $option[$_code] =  "(".$_title.")  ". $_m;
                }
            }
        }
        return $option;
    }
}