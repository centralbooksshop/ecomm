<?php
namespace Retailinsights\Orders\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class ConfirmOrderAction extends Column
{
    /** @var UrlInterface */
    protected $urlBuilder;

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

    public function prepareDataSourcedup(array $dataSource)
	{
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as &$item) {
				if (!empty($item['item_id'])) {

					// Check if delivery_date exists
					$isConfirmed = !empty($item['delivery_date']);

					// Build action array
					if ($isConfirmed) {
						// Show "Confirmed" without href
						$item[$this->getData('name')] = [
							'confirm' => [
								'label' => __('Confirmed'),
								'hidden' => false,
								'disable' => true, // optional, some themes respect this
							]
						];
					} else {
						// Show clickable "Confirm Order"
						$item[$this->getData('name')] = [
							'confirm' => [
								'href'  => $this->urlBuilder->getUrl(
									$this->getData('config/viewUrlPath'),
							        ['item_id' => $item['all_item_ids'] ?? $item['item_id']]
									//['id' => $item['item_id']]
								),
								'label' => __('Confirm Order'),
								'hidden' => false,
							]
						];
					}
				}
			}
		}

		return $dataSource;
	}

	public function prepareDataSource(array $dataSource)
	{
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as &$item) {
				if (!empty($item['item_id']) || !empty($item['all_item_ids'])) {

					$total     = (int)($item['total_name_count'] ?? 0);
					$confirmed = (int)($item['confirmed_name_count'] ?? 0);

					// Row is fully confirmed ONLY when all items are confirmed
					$isFullyConfirmed = $total > 0 && $total === $confirmed;

					if ($isFullyConfirmed) {
						// Show non-clickable "Confirmed"
						$item[$this->getData('name')] = [
							'confirm' => [
								'label'   => __('Confirmed'),
								'hidden'  => false,
								'disable' => true,
							]
						];
					} else {
						// Some items are still not confirmed show "Confirm Order"
						$item[$this->getData('name')] = [
							'confirm' => [
								'href'  => $this->urlBuilder->getUrl(
									$this->getData('config/viewUrlPath'),
									['item_id' => $item['all_item_ids'] ?? $item['item_id']]
								),
								'label'  => __('Confirm Order'),
								'hidden' => false,
							]
						];
					}
				}
			}
		}

		return $dataSource;
	}


}
