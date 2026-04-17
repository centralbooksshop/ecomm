<?php

namespace Infomodus\Fedexlabel\Block\Adminhtml\Widget\Items\Grid\Column\Renderer;

class Type extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public $_config;
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Infomodus\Fedexlabel\Helper\Config $config,
        array $data = []
    ) {
        $this->_config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    public function setColumn($column)
    {
        parent::setColumn($column);
        $this->update();
        return $this;
    }

    /**
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getLstatus() == 1) {
            return '';
        }
        $baseDir = $this->_config->getBaseDir('media');
        $HVR = false;
        $Html = '';
        $Image = '';
        $Invoice = '';
        if (file_exists($baseDir . '/fedexlabel/label/HVR/' . $row->getTrackingnumber() . ".html")) {
            $HVR = ' / <a href="' . $baseDir . 'fedexlabel/label/HVR' . $row->getTrackingnumber() . '.html" target="_blank">HVR</a>';
        }
        if ($row->getTypePrint() == "GIF") {
            $Pdf = '<a href="' . $this->getUrl('adminhtml/fedexlabel_pdflabels/onepdf/label_id/' . $row->getId()) . '" target="_blank">PDF</a>';
            $Image = ' / <a href="' . $this->getUrl('adminhtml/fedexlabel_fedexlabel/print/imname/' . 'label' . $row->getTrackingnumber() . '.gif') . '" target="_blank">Image</a>';
        } else {
            $Pdf = '<a href="' . $this->getUrl('adminhtml/fedexlabel_fedexlabel/autoprint/label_id/'.$row->getId()) . '" target="_blank">' . __('Print Label') . '</a>';
        }
        if (file_exists($this->_config->getBaseDir('media') . '/fedexlabel/label/' . $row->getTrackingnumber() . '.html')) {
            $Html = ' / <a href="' . $baseDir . 'fedexlabel/label/' . $row->getTrackingnumber() . '.html" target="_blank">Html</a>';
        }
        if (file_exists($this->_config->getBaseDir('media') . '/fedexlabel/inter_pdf/' . $row->getShipmentidentificationnumber() . '.pdf')) {
            $Invoice = ' / <a href="' . $baseDir . 'fedexlabel/inter_pdf/' . $row->getShipmentidentificationnumber() . '.pdf" target="_blank">Invoice</a>';
        }
        return $Pdf .  $Html . $Image . $HVR.$Invoice;
    }
}