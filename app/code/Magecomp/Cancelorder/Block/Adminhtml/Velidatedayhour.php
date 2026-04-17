<?php
namespace Magecomp\Cancelorder\Block\Adminhtml;
use Magento\Framework\View\Element\Html\Select;
class Velidatedayhour extends Select
{
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
        return [
            ['label' => 'Days', 'value' => '1'],
            ['label' => 'Hours', 'value' => '2'],
        ];
    }
}
