<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Infomodus\Fedexlabel\Block\Adminhtml\Items\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;


class Weightdimension extends Generic implements TabInterface
{
    protected $weight;
    protected $unitOfMeasurement;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Infomodus\Fedexlabel\Model\Config\Weight $weight,
        \Infomodus\Fedexlabel\Model\Config\Unitofmeasurement $unitOfMeasurement,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->weight = $weight;
        $this->unitOfMeasurement = $unitOfMeasurement;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Weight and Dimension');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Weight and Dimension');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_infomodus_fedexlabel_items');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $htmlIdPrefix = 'item_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $confParams = $model['handy']->defConfParams;
        $fieldset = $form->addFieldset('weightdimension_fieldset', ['legend' => __('Weight and Dimension')]);
        $fieldset->addField(
            'weightunits',
            'select',
            ['name' => 'weightunits',
                'label' => __('Specific unit weight'),
                'title' => __('Specific unit weight'),
                'required' => true,
                'options' => $this->weight->getArray(),
                'value' => $confParams['weightunits'],
            ]
        );
        $fieldset->addField(
            'unitofmeasurement',
            'select',
            ['name' => 'unitofmeasurement',
                'label' => __('Unit of measurement'),
                'title' => __('Unit of measurement'),
                'required' => false,
                'options' => $this->unitOfMeasurement->getArray(),
                'value' => $confParams['unitofmeasurement'],
            ]
        );

        /*$form->setValues($model->getData());*/
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
