<?php
namespace Shipsy\EcommerceExtension\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class ServiceActions extends Column
{
    /** @var UrlInterface */
    protected $urlBuilder;

    const EDIT_URL_PATH = 'softdatasync/service/edit';
    const DELETE_URL_PATH = 'softdatasync/service/delete';

    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Add Edit/Delete links to each row
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                self::EDIT_URL_PATH,
                                ['entity_id' => $item['entity_id']]
                            ),
                            'label' => __('Edit'),
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                self::DELETE_URL_PATH,
                                ['entity_id' => $item['entity_id']]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "%1"', $item['name'] ?? ''),
                                'message' => __('Are you sure you want to delete this service?')
                            ]
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
