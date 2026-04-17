<?php
namespace Retailinsights\Registers\Model\Config;
 
class Multiselect extends \Magento\Captcha\Model\Config\Form\AbstractForm
{
    /**
     * @var string
     */
    protected $_configPath = 'retailinsights/multiselect';
 
    /**
     * Returns options for form multiselect
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $backendConfig = $this->_config->getValue($this->_configPath, 'default');
        if ($backendConfig) {
            foreach ($backendConfig as $formName => $formConfig) {
                if (!empty($formConfig['label'])) {
                    $optionArray[] = ['label' => $formConfig['label'], 'value' => $formName];
                }
            }
        }
        return $optionArray;
    }
}