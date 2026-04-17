<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Infomodus\Fedexlabel\Block\Adminhtml\Items\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;


class International extends Generic implements TabInterface
{
    protected $defaultAddress;
    protected $reasonForExport;
    protected $purposeOfShipment;
    protected $termsOfSale;
    protected $soldToAddress;
    protected $country;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Infomodus\Fedexlabel\Model\Config\Defaultaddress $defaultAddress,
        \Infomodus\Fedexlabel\Model\Config\ReasonForExport $reasonForExport,
        \Infomodus\Fedexlabel\Model\Config\PurposeOfShipment $purposeOfShipment,
        \Infomodus\Fedexlabel\Model\Config\TermsOfSale $termsOfSale,
        \Infomodus\Fedexlabel\Model\Config\SoldToAddress $soldToAddress,
        \Magento\Directory\Model\Config\Source\Country $country,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->defaultAddress = $defaultAddress;
        $this->reasonForExport = $reasonForExport;
        $this->purposeOfShipment = $purposeOfShipment;
        $this->termsOfSale = $termsOfSale;
        $this->soldToAddress = $soldToAddress;
        $this->country = $country;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('International Invoice');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('International Invoice');
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
        $model = $this->_coreRegistry->registry('current_infomodus_fedexlabel_items');
        $confParams = $model['handy']->defConfParams;
        $address = $this->defaultAddress->getAddressesById($confParams['shipper_no']);
        if(($address && $confParams['shiptocountrycode'] == $address->getCountry()) || $model['handy']->type == 'refund'){
            return true;
        }
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
        $fieldset = $form->addFieldset('international_fieldset', ['legend' => __('Configuration')]);

        $fieldset->addField(
            'isElectronicTradeDocuments',
            'select',
            ['name' => 'isElectronicTradeDocuments',
                'label' => __('Electronic Trade Documents'),
                'title' => __('Electronic Trade Documents'),
                'required' => false,
                'options' => [__('No'), __('Yes')],
                'value' => $confParams['isElectronicTradeDocuments']
            ]
        );

        $fieldset->addField(
            'international_invoicenumber',
            'text',
            ['name' => 'international_invoicenumber',
                'label' => __('Invoice number'),
                'title' => __('Invoice number'),
                'required' => true,
                'value' => $confParams['international_invoicenumber']
            ]
        );

        $fieldset->addField(
            'international_invoicedate',
            'text',
            ['name' => 'international_invoicedate',
                'label' => __('Invoice date'),
                'title' => __('Invoice date'),
                'required' => true,
                'value' => $confParams['international_invoicedate']
            ]
        );

        $fieldset->addField(
            'international_reasonforexport',
            'select',
            ['name' => 'international_reasonforexport',
                'label' => __('Reason for export'),
                'title' => __('Reason for export'),
                'required' => true,
                'values' => $this->reasonForExport->toOptionArray(),
                'value' => $confParams['international_reasonforexport']
            ]
        );

        $fieldset->addField(
            'international_reasonforexport_desc',
            'text',
            ['name' => 'international_reasonforexport_desc',
                'label' => __('Reason for export description'),
                'title' => __('Reason for export description'),
                'required' => true,
                'value' => $confParams['international_reasonforexport_desc']
            ]
        );

        $fieldset->addField(
            'international_reasonforexport_return',
            'select',
            ['name' => 'international_reasonforexport_return',
                'label' => __('Reason for export for return'),
                'title' => __('Reason for export for return'),
                'required' => true,
                'values' => $this->reasonForExport->toOptionArray(),
                'value' => $confParams['international_reasonforexport_return']
            ]
        );

        $fieldset->addField(
            'international_reasonforexport_return_desc',
            'text',
            ['name' => 'international_reasonforexport_return_desc',
                'label' => __('Reason for export description for return'),
                'title' => __('Reason for export description for return'),
                'required' => true,
                'value' => $confParams['international_reasonforexport_return_desc']
            ]
        );

        $fieldset->addField(
            'purpose_of_shipment',
            'select',
            ['name' => 'purpose_of_shipment',
                'label' => __('Purpose of shipment'),
                'title' => __('Purpose of shipment'),
                'required' => true,
                'values' => $this->purposeOfShipment->toOptionArray(),
                'value' => $confParams['purpose_of_shipment']
            ]
        );

        $fieldset->addField(
            'international_termsofsale',
            'select',
            ['name' => 'international_termsofsale',
                'label' => __('Terms of sale'),
                'title' => __('Terms of sale'),
                'required' => false,
                'values' => $this->termsOfSale->toOptionArray(),
                'value' => $confParams['international_termsofsale']
            ]
        );

        $fieldset->addField(
            'soldto_address',
            'select',
            ['name' => 'soldto_address',
                'label' => __('SoldTo address'),
                'title' => __('SoldTo address'),
                'values' => $this->soldToAddress->toOptionArray(),
                'value' => $confParams['soldto_address']
            ]
        );

        $this->_eventManager->dispatch('infomodus_fedexlabel_international_field', ['form'=> $form, 'fieldset' => $fieldset, 'confParams' => $confParams]);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "{$htmlIdPrefix}international_reasonforexport",
                'international_reasonforexport'
            )->addFieldMap(
                "{$htmlIdPrefix}international_reasonforexport_return",
                'international_reasonforexport_return'
            )->addFieldMap(
                "{$htmlIdPrefix}international_reasonforexport_return_desc",
                'international_reasonforexport_return_desc'
            )->addFieldMap(
                "{$htmlIdPrefix}international_reasonforexport_desc",
                'international_reasonforexport_desc'
            )->addFieldDependence(
                'international_reasonforexport_desc',
                'international_reasonforexport',
                'OTHER'
            )->addFieldDependence(
                'international_reasonforexport_return_desc',
                'international_reasonforexport_return',
                'OTHER'
            )
        );

        if (isset($confParams['invoice_product']) && count($confParams['invoice_product']) > 0) {
            foreach ($confParams['invoice_product'] as $key => $product) {
                $fieldsetProducts = $form->addFieldset('invoice_product_fieldset' . $key, ['legend' => __('Products') . (" " . ($key + 1))]);
                $fieldsetProducts->addField(
                    'invoice_product-enabled' . $key,
                    'select',
                    ['name' => 'invoice_product[' . $key . '][enabled]',
                        'label' => __('Enabled'),
                        'title' => __('Enabled'),
                        'required' => true,
                        'options' => [__('No'), __('Yes')],
                        'value' => 1
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-description' . $key,
                    'text',
                    ['name' => 'invoice_product[' . $key . '][description]',
                        'label' => __('Description'),
                        'title' => __('Description'),
                        'required' => false,
                        'value' => $product['description']
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-price' . $key,
                    'text',
                    ['name' => 'invoice_product[' . $key . '][price]',
                        'label' => __('Price'),
                        'title' => __('Price'),
                        'required' => false,
                        'value' => round($product['price'], 2)
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-country_code' . $key,
                    'select',
                    ['name' => 'invoice_product[' . $key . '][country_code]',
                        'label' => __('Origin Country Code'),
                        'title' => __('Origin Country Code'),
                        'required' => false,
                        'values' => $this->country->toOptionArray(),
                        'value' => $product['country_code']
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-qty' . $key,
                    'text',
                    ['name' => 'invoice_product[' . $key . '][qty]',
                        'label' => __('Quantity'),
                        'title' => __('Quantity'),
                        'required' => false,
                        'value' => round($product['qty'], 2)
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-weight' . $key,
                    'hidden',
                    ['name' => 'invoice_product[' . $key . '][weight]',
                        'required' => false,
                        'value' => $product['weight']
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-id' . $key,
                    'hidden',
                    ['name' => 'invoice_product[' . $key . '][id]',
                        'required' => false,
                        'value' => $product['id']
                    ]
                );
                $fieldsetProducts->addField(
                    'invoice_product-harmonized' . $key,
                    'text',
                    ['name' => 'invoice_product[' . $key . '][harmonized]',
                        'label' => __('Harmonized code'),
                        'title' => __('Harmonized code'),
                        'required' => false,
                        'value' => $product['harmonized']
                    ]
                );
            }
        }
        /*$form->setValues($model->getData());*/
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
