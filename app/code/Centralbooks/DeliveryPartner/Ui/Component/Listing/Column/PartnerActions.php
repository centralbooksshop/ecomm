<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryPartner\Ui\Component\Listing\Column;

class PartnerActions extends \Magento\Ui\Component\Listing\Columns\Column
{

    const URL_PATH_EDIT = 'centralbooks_deliverypartner/partner/edit';
    const URL_PATH_DELETE = 'centralbooks_deliverypartner/partner/delete';
    const URL_PATH_DETAILS = 'centralbooks_deliverypartner/partner/details';
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['partner_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'partner_id' => $item['partner_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'partner_id' => $item['partner_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            /*'confirm' => [
                                'title' => __('Delete "${ $.$data.title }"'),
                                'message' => __('Are you sure you wan\'t to delete a "${ $.$data.title }" record?')
			    ]*/
			    'confirm' => [
				    'title' => __('Delete') . ' "' .  $item['name'] . '"',
    				    'message' => __('Are you sure you want to delete the "' .  $item['name'] . '" record?')
				]

                        ]
                    ];
                }
            }
        }
        
        return $dataSource;
    }
}

