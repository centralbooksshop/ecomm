<?php
namespace Shipsy\EcommerceExtension\Ui\Component\Listing\Column;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Exception\NoSuchEntityException;

class TrackingUrl extends Column
{
    protected $orderRepository;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        array $components = [],
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {

            if (empty($item['entity_id'])) {
                $item[$this->getData('name')] = 'No';
                continue;
            }

            try {
                $order = $this->orderRepository->get((int)$item['entity_id']);
            } catch (NoSuchEntityException $e) {
                 $item[$this->getData('name')] = 'No';
                continue;
            }

            $trackingUrl = $order->getData('shipsy_tracking_url');

            if ($trackingUrl) {
                $item[$this->getData('name')] =
                    '<a href="' . $trackingUrl . '" target="_blank">Track Order</a>';
            } else {
                $item[$this->getData('name')] = 'No';
            }
        }

        return $dataSource;
    }
}
