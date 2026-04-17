<?php
namespace Retailinsights\ProcessCBOOrders\Ui\DataProvider\Orders;

use Retailinsights\ProcessCBOOrders\Model\ResourceModel\ProcessCBOOrders\CollectionFactory; 
// use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
 
class ProcessedDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
 
    public function __construct(
        // CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        // $collection = $collectionFactory->create();
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }
}

?>