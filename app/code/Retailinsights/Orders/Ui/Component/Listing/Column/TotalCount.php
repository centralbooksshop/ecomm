<?php
namespace Retailinsights\Orders\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class TotalCount extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {

                $name             = $item['name'] ?? '';
                $productPurchased = $item['product_purchased'] ?? '';
                $deliveryStatus   = $item['delivery_status'] ?? 'not_confirmed';
				$dispatchStatus   = $item['dispatch_status'] ?? 'not_confirmed';
                $givenOptions     = $item['given_options'] ?? '';

                $url = '/cbsadmin/retailinsights_admin/willbegivenitems/index?'
                    . http_build_query([
                        'name' => $name,
                        'product_purchased' => $productPurchased,
                        'delivery_status' => $deliveryStatus,
						'dispatch_status' => $dispatchStatus,
                        'given_options' => $givenOptions
                    ]);

                $item[$this->getData('name')] =
                    '<a href="' . $url . '" target="_blank">View</a>';
            }
        }

        return $dataSource;
    }
}
