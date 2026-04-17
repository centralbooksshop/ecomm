<?php
namespace Magecomp\Cancelorder\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Cancelreasons extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn(
            'filetype',
            [
                'label' => __('Reasons'),
                'size' => '200px',
                'class' => 'required-entry'
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}