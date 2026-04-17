<?php
namespace Retailinsights\DtdcCustom\Model;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Retailinsights\DtdcCustom\Model\ResourceModel\ProcessDtdcOrders\Grid\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Retailinsights\DtdcCustom\Model\ResourceModel\ProcessDtdcOrders\Grid\Collection
     */
    protected $collection;

    protected $loadedData = [];

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        // Initialize collection before parent constructor
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        // Add DTDC filters and current month range
        //$this->applyDefaultFilters();
    }

    protected function applyDefaultFilters()
    {
        $startOfMonth = '2025-10-01 00:00:00';
        $endOfMonth   = date('Y-m-t 23:59:59');

        $this->collection->addFieldToFilter('main_table.tracking_title', ['eq' => 'dtdc'])
            ->addFieldToFilter('so.status', ['in' => ['order_delivered', 'dispatched_to_courier']])
            ->addFieldToFilter('so.created_at', ['from' => $startOfMonth, 'to' => $endOfMonth])
		    ->addFieldToFilter('main_table.tracking_title', ['eq' => 'DTDC']);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return [
                'totalRecords' => $this->collection->getSize(),
                'items' => $this->collection->getData()
            ];
        }

        return ['totalRecords' => 0, 'items' => []];
    }
}
