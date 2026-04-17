<?php
namespace Magecomp\Cancelorder\Block\Adminhtml;
use Magecomp\Cancelorder\Helper\Data;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Payment\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Paymentmetods extends Select
{
    protected $_helper;
    protected $_paymentModelConfig;
    protected $_appConfigScopeConfigInterface;

    public function __construct(
        Context $context,
        Data $helper,
        Config $paymentModelConfig,
        ScopeConfigInterface $appConfigScopeConfigInterface,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_helper = $helper;
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_paymentModelConfig = $paymentModelConfig;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    private function getSourceOptions()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getPaymentmethods());
        }
        return $this->getPaymentmethods();
    }

    private function getPaymentmethods()
    {
        $payments = $this->_paymentModelConfig->getActiveMethods();
        $methods = array();
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->_appConfigScopeConfigInterface
                ->getValue('payment/' . $paymentCode . '/title');
            if($paymentCode == "free"){
                $paymentmethods = "free";
            } else{
            $methods[] = array(
                'label' => $paymentTitle,
                'value' => $paymentCode
            );
            }
        }
        return $methods;
    }
}
