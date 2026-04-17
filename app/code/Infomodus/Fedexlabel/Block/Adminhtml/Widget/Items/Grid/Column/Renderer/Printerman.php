<?php

namespace Infomodus\Fedexlabel\Block\Adminhtml\Widget\Items\Grid\Column\Renderer;

class Printerman extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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

        $path_url = $this->_config->getBaseUrl('media') . 'fedexlabel/label/';
        $path_dir = $this->_config->getBaseDir('media') . '/fedexlabel/label/';
        if ($row->getTypePrint() == "pdf" || $row->getTypePrint() == "png") {
            $pdf = '<a href="' . $this->getUrl('infomodus_fedexlabel/pdflabels/one',
                    ['label_name'=>$row->getLabelname()]) . '" target="_blank">'
                . __('PDF') . '</a>';
        } else {
            if($this->_config->getStoreConfig('fedexlabel/printing/automatic_printing')==1) {
                $pdf = '<a href="' . $this->getUrl('infomodus_fedexlabel/items/autoprint',
                        ['label_id' => $row->getId(), 'order_id' => $row->getOrderId()]) . '" target="_blank">'
                    . __('Print Label') . '</a>';
            } else {
                $printersText = $this->_config->getStoreConfig('fedexlabel/printing/printer_name');
                $printers = explode(",", $printersText);
                $pdf = '<a class="thermal-print-file" data-printer="'.(trim($printers[0])).'" href="' . $this->getUrl('infomodus_fedexlabel/items/autoprint', ['label_id' => $row->getId(), 'order_id' => $row->getOrderId(), 'type_print' => 'manual']) . '">' . __('Print thermal') . '</a>';
            }
        }

        $invoice="";
        if(file_exists($path_dir.'invoice'.$row->getTrackingnumber().'.pdf')){
            $invoice = ' &nbsp; <a href="' . ($path_url.'invoice'.$row->getTrackingnumber().'.pdf') . '" target="_blank">'
                . __('Invoice') . '</a>';
        }

        return $pdf.$invoice;
    }
}
