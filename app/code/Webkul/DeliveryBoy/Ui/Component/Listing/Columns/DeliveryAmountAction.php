<?php

namespace Webkul\DeliveryBoy\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\Data\Form\FormKey;

class DeliveryAmountAction extends Column
{

    private $urlBuilder;
    private $formKey;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        FormKey $formKey,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->formKey = $formKey;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['id'])) {
                    $item['html'] = "<button class='button'><span>".__("Update")."</span></button>";
                    $item['title'] = __('Update Delivery Amount');
                    $item['id'] = $item['id'];
                    $item['incrementid'] = $item['increment_id'];
                    $item['packageitems'] = $item['package_items'];
                    $item['deliveryamount'] = $item['delivery_amount'];
                    $item['priceforcover'] = $this->scopeConfig->getValue('deliveryboy/configuration/priceforcover');
                    $item['priceforbox'] = $this->scopeConfig->getValue('deliveryboy/configuration/priceforbox');
                    $item['comments'] = $item['comments'];
                    $item['formkry'] = $this->formKey->getFormKey();
                    $item['formaction'] = $this->urlBuilder->getUrl('expressdelivery/orders/updatedeliveryamount');
                }
            }
        }
        return $dataSource;
    }
}