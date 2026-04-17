<?php
namespace Retailinsights\Orders\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Acknowledgement extends Column
{
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $itemId = $item['item_id'];

            if (!empty($item['acknowledgement_upload'])) {
                $url = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA])
                     . 'acknowledgement_upload/' . $item['acknowledgement_upload'];
                $item[$this->getData('name')] = '<a href="' . $url . '" target="_blank">Download</a>';
            } else {
                $uploadUrl = $this->urlBuilder->getUrl(
                    'retailinsights_admin/orders/uploadForm',
                    ['item_id' => $itemId]
                );
                $item[$this->getData('name')] =
                    '<button type="button" onclick="window.location.href=\'' . $uploadUrl . '\'">Upload</button>';
            }
        }

        return $dataSource;
    }
}
