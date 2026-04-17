<?php
/**
 * Copyright © 2019 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field\Tab;

class Items extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $_labelFactory;

    /**
     * @var \Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field\Activation
     */
    protected $_activation;

    /**
     * @var  \Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field\Tab\Type
     */
    protected $_type;

    protected $_template = 'system/config/form/field/custom.tabs.phtml';

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|\Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field\Activation
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getActivationRenderer()
    {
        if (!$this->_activation) {
            $this->_activation = $this->getLayout()->createBlock(
                '\Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field\Activation',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->_activation;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|Type
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getTypeRenderer()
    {
        $id = $this->_getCellInputElementId(
            '<%- _id %>',
            'type' );
        if (!$this->_type) {
            $this->_type = $this->getLayout()->createBlock(
                '\Ubertheme\UbThemeHelper\Block\Adminhtml\Config\Form\Field\Tab\Type',
                '',
                ['data' => ['is_render_to_js_template' => true ,'id' => $id]]
            );
        }

        return $this->_type;
    }

    /**
     * Add a column to array-grid
     *
     * @param string $name
     * @param array $params
     * @return void
     */
    public function addColumn($name, $params)
    {
        $this->_columns[$name] = [
            'label' => $this->_getParam($params, 'label', 'Column'),
            'placeholder' => $this->_getParam($params, 'placeholder'),
            'size' => $this->_getParam($params, 'size', false),
            'style' => $this->_getParam($params, 'style'),
            'class' => $this->_getParam($params, 'class'),
            'renderer' => false,
        ];

        if (!empty($params['renderer'])
            && $params['renderer'] instanceof \Magento\Framework\View\Element\AbstractBlock
        ) {
            $this->_columns[$name]['renderer'] = $params['renderer'];
        }
        if (isset($params['depending_columns']) && !empty($params['depending_columns'])) {
            $this->_columns[$name]['depending_columns'] = $params['depending_columns'];
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('type_default', [
            'label' => __(''),
            'style' => 'display:none'
        ]);
        $this->addColumn('title', [
            'label' => __('Label'),
            'placeholder' => __('Label'),
            'class'=>'required-entry ubcustom-tab-field tab-label',
            'placeholder' => __('Label')
        ]);
        $this->addColumn('type', [
                'label' => __('Type'),
                'renderer' => $this->_getTypeRenderer(),
                'depending_columns' => ['attribute_code', 'static_block']
            ]
        );
        $this->addColumn('attribute_code', [
            'label' => __('Attribute Code'),
            'placeholder' => __('Specify Attribute code'),
            'class' => 'required-entry validate-identifier ubcustom-tab-field attribute-code hide'
        ]);
        $this->addColumn('static_block', [
            'label' => __('CMS Block Identifier'),
            'placeholder' => __('Specify CMS Block Identifier'),
            'class' => 'required-entry validate-identifier static-block hide'
        ]);
        $this->addColumn('category_ids', [
            'label' => __('Category IDs'),
            'placeholder' => __('Eg: 1,2,3'),
            'class'=>'ubcustom-tab-field category-ids validate-ids'
        ]);
        $this->addColumn('product_ids', [
                'label' => __('Product IDs'),
                'placeholder' => __('Eg: 1,2,3'),
                'class'=>'ubcustom-tab-field product-ids validate-ids'
            ]
        );
        $this->addColumn('status', [
                'label' => __('Status'),
                'placeholder' => __('Status'),
                'renderer' => $this->_getActivationRenderer()
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];
        $customType = $row->getData('type');
        $key = 'option_' . $this->_getActivationRenderer()->calcOptionHash($customType);
        $options[$key] = 'selected="selected"';
        $options['option_' . $this->_getActivationRenderer()->calcOptionHash($row->getData('status'))] = 'checked';
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new \Exception('Wrong column name specified.');
        }

        $inputName = $this->_getCellInputElementName($columnName);

        //special process for status field
        if ($columnName == 'status') {
            $id = $this->_getCellInputElementId('<%- _id %>', $columnName);
            $html = '<label class="switch tooltip">'.
                '<input type="checkbox" data-status="on" checked id="'.$id.'" name = "'.$inputName.'" />'.
                '<span class="slider round"></span>'.
                '<span class="tooltiptext" style="display:none"></span>'.
                '</label>';
            return $html;
        }

        $column = $this->_columns[$columnName];
        if ($column['renderer']) {
            $html = $column['renderer']->setInputName(
                $inputName
            )->setInputId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setColumnName(
                $columnName
            )->setColumn(
                $column
            )->toHtml();
        } else {
            $html = '<input type="text" id="' . $this->_getCellInputElementId(
                    '<%- _id %>',
                    $columnName
                ) .
                '"' .
                ' name="' .
                $inputName .
                '" value="<%- ' .
                $columnName .
                ' %>" ' .
                ($column['size'] ? 'size="' .
                    $column['size'] .
                    '"' : '') .
                ' class="' .
                (isset($column['class'])
                    ? $column['class']
                    : 'input-text') . '"' . (isset($column['style']) ? ' style="' . $column['style'] . '"' : '')
                . ($columnName == "static_block" ? "disabled " : "" ) .
                (($columnName == "category_ids"
                    || $columnName == "product_ids") ? "data-validate={\"validate-custom-ids\":true}" : "").
                (($columnName == "attribute_code"
                    || $columnName == "static_block") ? "data-validate={\"validate-custom-identifier\":true}" : "").
                ' placeholder="'. $this->_columns[$columnName]['placeholder'] .'"' .
                ' />';
        }

        return $html;
    }

    /**
     * Render block HTML
     *
     * @return string
     * @throws \Exception
     */
    protected function _toHtml()
    {
        $html =  parent::_toHtml();
        $html .= '<div class="custom_note">'.__("Note").' : </br>
            <p class="note">'.__(" - Leave Category ID(s) / Product ID(s) fields blank to display tabs on all product pages.").'</p>
            <p class="note">'.__("- System tabs (Details, More Information, Review) cannot be removed from the tab list.").'</p></div>
        ';

        return $html;
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td class="label"><label for="' .
            $element->getHtmlId() . '"><span' .
            $this->_renderScopeLabel($element) . '>' .
            $element->getLabel() .
            '</span></label></td>';
        $html .= $this->_renderValue($element);
        $html .= $this->_renderHint($element);

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Render reset button which allow reset to default value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderResetButton(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $htmlId = $element->getHtmlId();
        /*$namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());
        $checkedHtml = $element->getInherit() == 1 ? 'checked="checked"' : '';*/

        $html = '<div class="use-default btn-reset-tab">';

        $class = 'admin__field-fallback-reset btn-reset show';

        $html .= '<button id="'.$htmlId.'_inherit" class="' . $class . '" type="button"><span>';
        $html .=  __('Use default value') . '</span></button>';

        $html .= '</div>';

        return $html;
    }

    /**
     * @return string
     */
    public function getHtmlResetButton()
    {
        $html = '';
        /** @var \Magento\Framework\Data\Form\Element\AbstractElement */
        $element = $this->getElement();
        $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);
        if ($isCheckboxRequired) {
            $html = $this->_renderResetButton($element);
        }

        return $html;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getArrayRowsDefault()
    {
        $result = [];
        /** @var \Magento\Framework\Data\Form\Element\AbstractElement */
        $element = $this->getElement();
        $dataConfig = $element->getData('field_config');
        $arrDefault = json_decode($dataConfig['default_value'],true);
        if ($arrDefault && is_array($arrDefault)) {
            foreach ($arrDefault as $rowId => $row) {
                $rowColumnValues = [];
                foreach ($row as $key => $value) {
                    $row[$key] = $value;
                    $rowColumnValues[$this->_getCellInputElementId($rowId, $key)] = $row[$key];
                }
                $row['_id'] = $rowId;
                $row['column_values'] = $rowColumnValues;
                $result[$rowId] = new \Magento\Framework\DataObject($row);
                $this->_prepareArrayRow($result[$rowId]);
            }
        }

        return $result;
    }

    public function getDependingColumns() {
        $dependingColumns = [];
        $columns = $this->getColumns();
        foreach ($columns as $columnName => $column) {
            if (isset($column['depending_columns']) && $column['depending_columns']) {
                $dependingColumns = array_merge($dependingColumns, $column['depending_columns']);
            }
        }

        return $dependingColumns;
    }
}