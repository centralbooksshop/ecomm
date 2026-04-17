<?php
namespace Magecomp\Cancelorder\Block\Adminhtml;

use Magento\Framework\DataObject;
class Paymenttime extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    private $taxRenderer;
    protected  $velidatesRenderer;
    protected function _prepareToRender()
    {
        $this->addColumn('paymentmethod', [
            'label' => __('Payment Method'),
            'renderer' => $this->getTaxRenderer()
        ]);
        $this->addColumn('velidates', [
            'label' => __('Duration'),
            'renderer' => $this->getVelidatesRenderer()
        ]);
        $this->addColumn('time',
            ['label' => __('Value'),
            'class' => 'required-entry validate-number']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];

        $payment = $row->getTaxRenderer();
        if ($payment !== null) {
            $options['option_' . $this->getTaxRenderer()->calcOptionHash($payment)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);

        $optionsfor = [];

        $daystime = $row->getVelidatesRenderer();
        if ($daystime !== null) {
            $optionsfor['option_' . $this->getVelidatesRenderer()->calcOptionHash($daystime)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $optionsfor);
    }
    private function getTaxRenderer()
    {
        if (!$this->taxRenderer) {
            $this->taxRenderer = $this->getLayout()->createBlock(
                Paymentmetods::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->taxRenderer;
    }

    private function getVelidatesRenderer()
    {
        if (!$this->velidatesRenderer) {
            $this->velidatesRenderer = $this->getLayout()->createBlock(
                Velidatedayhour::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->velidatesRenderer;
    }
}
