<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Infomodus\Fedexlabel\Block\Adminhtml\Items\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;


class Main extends Generic implements TabInterface
{
    protected $fedexMethod;
    protected $defaultAddress;
    protected $dropoff;
    protected $fedexPackageCodes;
    protected $allCurrency;
    protected $notificationCode;
    protected $deliveryConfirmation;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Infomodus\Fedexlabel\Model\Config\Fedexmethod $fedexMethod,
        \Infomodus\Fedexlabel\Model\Config\Defaultaddress $defaultAddress,
        \Infomodus\Fedexlabel\Model\Config\Dropoff $dropoff,
        \Infomodus\Fedexlabel\Model\Config\Fedexpackagecode $fedexPackageCodes,
        \Magento\Config\Model\Config\Source\Locale\Currency\All $allCurrency,
        \Infomodus\Fedexlabel\Model\Config\Notificationcode $notificationCode,
        \Infomodus\Fedexlabel\Model\Config\DeliveryConfirmation $deliveryConfirmation,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->fedexMethod = $fedexMethod;
        $this->defaultAddress = $defaultAddress;
        $this->dropoff = $dropoff;
        $this->fedexPackageCodes = $fedexPackageCodes;
        $this->allCurrency = $allCurrency;
        $this->notificationCode = $notificationCode;
        $this->deliveryConfirmation = $deliveryConfirmation;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Main options');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Main options');
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

        $fieldset = $form->addFieldset('configuration_fieldset', ['legend' => __('Configuration options')]);

        $fieldset->addField(
            'fedexaccount',
            'select',
            ['name' => 'fedexaccount',
                'label' => __('Who pay for Shipment?'),
                'title' => __('Who pay for Shipment?'),
                'required' => true,
                'options' => $model['handy']->fedexAccounts,
                'value' => $confParams['fedexaccount'],
            ]
        );

        $fieldset->addField(
            'serviceCode',
            'select',
            ['name' => 'serviceCode',
                'label' => __('FedEx shipping method'),
                'title' => __('FedEx shipping method'),
                'required' => true,
                'options' => $this->fedexMethod->getFedexMethods(),
                'value' => $confParams['serviceCode'],
            ]
        );

        /*$fieldset->addField(
            'shipping_methods',
            'hidden',
            ['name' => 'shipping_methods',
                'required' => true,
                'value' => $confParams['shipping_methods']
            ]
        );*/

        $fieldset->addField(
            'shipper_no',
            'select',
            ['name' => 'shipper_no',
                'label' => __('Shipper address'),
                'title' => __('Shipper address'),
                'required' => true,
                'values' => $this->defaultAddress->toOptionArray(),
                'value' => $confParams['shipper_no'],
            ]
        );
        $fieldset->addField(
            'testing',
            'select',
            ['name' => 'testing',
                'label' => __('Test mode'),
                'title' => __('Test mode'),
                'required' => true,
                'options' => [__('No'), __('Yes')],
                'value' => $confParams['testing'],
            ]
        );
        $fieldset->addField(
            'dropoff',
            'select',
            ['name' => 'dropoff',
                'label' => __('Dropoff'),
                'title' => __('Dropoff'),
                'required' => true,
                'values' => $this->dropoff->toOptionArray(),
                'value' => $confParams['dropoff'],
            ]
        );
        $fieldset->addField(
            'packagingtypecode',
            'select',
            ['name' => 'packagingtypecode',
                'label' => __('Packaging Type'),
                'title' => __('Packaging Type'),
                'required' => true,
                'values' => $this->fedexPackageCodes->toOptionArray(),
                'value' => $confParams['packagingtypecode'],
            ]
        );
        if ($model['handy']->type == 'shipment' || $model['handy']->type == 'invert') {
            $fieldset->addField(
                'addtrack',
                'select',
                ['name' => 'addtrack',
                    'label' => __('Add tracking number automatically ?'),
                    'title' => __('Add tracking number automatically ?'),
                    'required' => false,
                    'options' => [__('No'), __('Yes')],
                    'value' => $confParams['addtrack'],
                ]
            );
        }
        $fieldset->addField(
            'currencycode',
            'select',
            ['name' => 'currencycode',
                'label' => __('Currency'),
                'title' => __('Currency'),
                'required' => false,
                'values' => $this->allCurrency->toOptionArray(),
                'value' => $confParams['currencycode'],
            ]
        );

        $fieldset->addField(
            'codmonetaryvalue',
            'text',
            ['name' => 'codmonetaryvalue',
                'label' => __('Monetary value'),
                'title' => __('Monetary value'),
                'required' => false,
                'value' => round($confParams['codmonetaryvalue'], 2),
            ]
        );
        if ($model['handy']->type == 'shipment'/* && $model['handy']->shipment_id !== null*/) {
            $fieldset->addField(
                'default_return',
                'select',
                ['name' => 'default_return',
                    'label' => __('Create return label now'),
                    'title' => __('Create return label now'),
                    'required' => false,
                    'options' => [__('No'), __('Yes')],
                    'value' => $confParams['default_return'],
                ]
            );

            $fieldset->addField(
                'default_return_servicecode',
                'select',
                ['name' => 'default_return_servicecode',
                    'label' => __('FedEx shipping method for return label'),
                    'title' => __('FedEx shipping method for return label'),
                    'required' => false,
                    'options' => $this->fedexMethod->getFedexMethods(),
                    'value' => $confParams['default_return_servicecode'],
                ],
                'default_return'
            );

            /*$fieldset->addField(
                'return_methods',
                'hidden',
                ['name' => 'return_methods',
                    'required' => true,
                    'value' => $confParams['return_methods']
                ]
            );*/

            $this->setChild(
                'form_after',
                $this->getLayout()->createBlock(
                    'Magento\Backend\Block\Widget\Form\Element\Dependence'
                )->addFieldMap(
                    "{$htmlIdPrefix}default_return",
                    'default_return'
                )->addFieldMap(
                    "{$htmlIdPrefix}default_return_servicecode",
                    'default_return_servicecode'
                )->addFieldDependence(
                    'default_return_servicecode',
                    'default_return',
                    '1'
                )
            );
        }
        $fieldset->addField(
            'qvn',
            'select',
            ['name' => 'qvn',
                'label' => __('Notification'),
                'title' => __('Notification'),
                'required' => false,
                'options' => [__('No'), __('Yes')],
                'value' => $confParams['qvn'],
            ],
            'to'
        );
        $fieldset->addField(
            'qvn_code',
            'multiselect',
            ['name' => 'qvn_code',
                'label' => __('Notification Type'),
                'title' => __('Notification Type'),
                'required' => false,
                'values' => $this->notificationCode->toOptionArray(),
                'value' => $confParams['qvn_code'],
            ],
            'qvn'
        );
        $fieldset->addField(
            'qvn_email_shipper',
            'text',
            ['name' => 'qvn_email_shipper',
                'label' => __('Shipper Email'),
                'title' => __('Shipper Email'),
                'required' => false,
                'value' => $confParams['qvn_email_shipper'],
            ]
        );

        $fieldset->addField(
            'adult',
            'select',
            ['name' => 'adult',
                'label' => __('Delivery Signature'),
                'title' => __('Delivery Signature'),
                'required' => false,
                'values' => $this->deliveryConfirmation->toOptionArray(),
                'value' => $confParams['adult'],
            ]
        );
        $fieldset->addField(
            'saturday_delivery',
            'select',
            ['name' => 'saturday_delivery',
                'label' => __('Saturday Delivery'),
                'title' => __('Saturday Delivery'),
                'required' => false,
                'options' => [__('No'), __('Yes')],
                'value' => $confParams['saturday_delivery'],
            ]
        );
        /*$form->setValues($model->getData());*/
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
