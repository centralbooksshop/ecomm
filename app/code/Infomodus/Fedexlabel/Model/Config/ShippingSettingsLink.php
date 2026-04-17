<?php
/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 01.10.14
 * Time: 11:55
 */
namespace Infomodus\Fedexlabel\Model\Config;
class ShippingSettingsLink extends \Magento\Config\Block\System\Config\Form\Field
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Infomodus_Fedexlabel::system/config/button/compliance.phtml');
    }
    public function getButtonHtml()
    {
        $button =
            [
                'id' => 'fedexlabel_shipping_button_method_native',
                'label' => __('Manage compliance of methods'),
                'onclick' => $this->getUrl("infomodus_fedexlabel/conformity/index"),
            ];
        return $button;
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}