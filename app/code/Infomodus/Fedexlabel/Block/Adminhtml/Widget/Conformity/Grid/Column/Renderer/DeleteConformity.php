<?php

namespace Infomodus\Fedexlabel\Block\Adminhtml\Widget\Conformity\Grid\Column\Renderer;

class DeleteConformity extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    public function setColumn($column)
    {
        parent::setColumn($column);
        return $this;
    }

    /**
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return '<a href="'.$this->getUrl('infomodus_fedexlabel/conformity/delete', ['id' => $row->getId()]).'">'
            .__('Delete').'</a>';
    }
}
