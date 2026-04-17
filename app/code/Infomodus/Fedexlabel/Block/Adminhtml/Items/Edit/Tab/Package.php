<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Infomodus\Fedexlabel\Block\Adminhtml\Items\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;


class Package extends Generic implements TabInterface
{
    protected $referenceType;
    protected $referenceTypeRefund;
    protected $boxes;
    protected $yesno;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Infomodus\Fedexlabel\Model\Config\ReferenceType $referenceType,
        \Infomodus\Fedexlabel\Model\Config\ReferenceTypeRefund $referenceTypeRefund,
        \Infomodus\Fedexlabel\Model\Config\Boxes $boxes,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->referenceType = $referenceType;
        $this->referenceTypeRefund = $referenceTypeRefund;
        $this->boxes = $boxes;
        $this->yesno = $yesno;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Package Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Package Information');
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
        $form->setHtmlIdPrefix('item_');
        if (!is_array($model['handy']->defPackageParams) || count($model['handy']->defPackageParams) == 0) {
            $model['handy']->defPackageParams = [[]];
        }

        foreach ($model['handy']->defPackageParams as $key => $package) {
            $fieldset = $form->addFieldset('package_fieldset_' . $key . '_', ['legend' => __('Package') . ' ' . ($key + 1)]);
            if ($model['handy']->type != 'refund') {
                $fieldset->addField(
                    'packagingreferencetype_' . $key . '_',
                    'select',
                    ['name' => 'package[packagingreferencetype][]',
                        'label' => __('Customer Reference Type'),
                        'title' => __('Customer Reference Type'),
                        'required' => true,
                        'values' => $this->referenceType->toOptionArray(),
                        'value' => isset($package['packagingreferencetype']) ? $package['packagingreferencetype'] : null,
                    ]
                );
                $fieldset->addField(
                    'packagingreferencenumbervalue_' . $key . '_',
                    'text',
                    ['name' => 'package[packagingreferencenumbervalue][]',
                        'label' => __('Customer Reference Value'),
                        'title' => __('Customer Reference Value'),
                        'required' => true,
                        'value' => isset($package['packagingreferencenumbervalue']) ? $package['packagingreferencenumbervalue'] : null,
                    ]
                );
                $fieldset->addField(
                    'packagingreferencetypereturn_' . $key . '_',
                    'select',
                    ['name' => 'package[packagingreferencetypereturn][]',
                        'label' => __('Customer Reference Type for Refund'),
                        'title' => __('Customer Reference Type for Refund'),
                        'required' => false,
                        'values' => $this->referenceTypeRefund->toOptionArray(),
                        'value' => isset($package['packagingreferencetypereturn']) ? $package['packagingreferencetypereturn'] : null,
                    ]
                );
                $fieldset->addField(
                    'packagingreferencenumbervaluereturn_' . $key . '_',
                    'text',
                    ['name' => 'package[packagingreferencenumbervaluereturn][]',
                        'label' => __('Customer Reference Value for Refund'),
                        'title' => __('Customer Reference Value for Refund'),
                        'required' => false,
                        'value' => isset($package['packagingreferencenumbervaluereturn']) ? $package['packagingreferencenumbervaluereturn'] : null,
                    ]
                );
            } else {
                $fieldset->addField(
                    'packagingreferencetypereturn_' . $key . '_',
                    'select',
                    ['name' => 'package[packagingreferencetypereturn][]',
                        'label' => __('Customer Reference Type'),
                        'title' => __('Customer Reference Type'),
                        'required' => true,
                        'values' => $this->referenceTypeRefund->toOptionArray(),
                        'value' => isset($package['packagingreferencetypereturn']) ? $package['packagingreferencetypereturn'] : null,
                    ]
                );
                $fieldset->addField(
                    'packagingreferencenumbervaluereturn_' . $key . '_',
                    'text',
                    ['name' => 'package[packagingreferencenumbervaluereturn][]',
                        'label' => __('Customer Reference Value'),
                        'title' => __('Customer Reference Value'),
                        'required' => true,
                        'value' => isset($package['packagingreferencenumbervaluereturn']) ? $package['packagingreferencenumbervaluereturn'] : null,
                    ]
                );
            }
            $fieldset->addField(
                'weight_' . $key . '_',
                'text',
                ['name' => 'package[weight][]',
                    'label' => __('Weight'),
                    'title' => __('Weight'),
                    'required' => true,
                    'value' => isset($package['weight']) ? $package['weight'] : null,
                ]
            );
            $fieldset->addField(
                'packweight_' . $key . '_',
                'text',
                ['name' => 'package[packweight][]',
                    'label' => __('Pack weight'),
                    'title' => __('Pack weight'),
                    'required' => false,
                    'value' => isset($package['packweight']) ? $package['packweight'] : null,
                ]
            );

            $fieldset->addField(
                'default_box_' . $key . '_',
                'select',
                ['name' => 'package[box][]',
                    'label' => __('Box'),
                    'title' => __('Box'),
                    'required' => false,
                    'values' => $this->boxes->toOptionArray(),
                    'value' => isset($package['box']) ? $package['box'] : null,
                    'class' => 'box-selected',
                ]
            );

            $fieldset->addField(
                'length_' . $key . '_',
                'text',
                ['name' => 'package[length][]',
                    'label' => __('Length'),
                    'title' => __('Length'),
                    'required' => false,
                    'value' => isset($package['length']) ? $package['length'] : null,
                    'class' => 'box-length',
                ]
            );
            $fieldset->addField(
                'width_' . $key . '_',
                'text',
                ['name' => 'package[width][]',
                    'label' => __('Width'),
                    'title' => __('Width'),
                    'required' => false,
                    'value' => isset($package['width']) ? $package['width'] : null,
                    'class' => 'box-width',
                ]
            );
            $fieldset->addField(
                'height_' . $key . '_',
                'text',
                ['name' => 'package[height][]',
                    'label' => __('Height'),
                    'title' => __('Height'),
                    'required' => false,
                    'value' => isset($package['height']) ? $package['height'] : null,
                    'class' => 'box-height',
                ]
            );
            if ($model['handy']->type != 'refund') {
                $fieldset->addField(
                    'cod_' . $key . '_',
                    'select',
                    ['name' => 'package[cod][]',
                        'label' => __('COD'),
                        'title' => __('COD'),
                        'required' => false,
                        'values' => $this->yesno->toOptionArray(),
                        'value' => isset($package['cod']) ? $package['cod'] : null,
                    ]
                );

                $fieldset->addField(
                    'insured_automaticaly_' . $key . '_',
                    'select',
                    ['name' => 'package[insured_automaticaly][]',
                        'label' => __('Insurance'),
                        'title' => __('Insurance'),
                        'required' => false,
                        'values' => $this->yesno->toOptionArray(),
                        'value' => isset($package['insured_automaticaly']) ? $package['insured_automaticaly'] : null,
                    ]
                );

                $fieldset->addField(
                    'codmonetaryvalue_' . $key . '_',
                    'text',
                    ['name' => 'package[codmonetaryvalue][]',
                        'label' => __('Monetary value'),
                        'title' => __('Monetary value'),
                        'required' => false,
                        'value' => isset($package['codmonetaryvalue']) ? round($package['codmonetaryvalue'], 2) : null,
                    ]
                );
            }

            $this->_eventManager->dispatch('infomodus_fedlab_intm_package_def_param', ['fieldset' => $fieldset, 'package' => $package, 'model' => $model, 'key' => $key]);
        }
        /*$form->setValues($model->getData());*/
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
