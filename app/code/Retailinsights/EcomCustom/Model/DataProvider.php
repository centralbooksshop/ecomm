<?php
namespace Retailinsights\EcomCustom\Model;

use Retailinsights\ProcessCBOOrders\Model\ResourceModel\ProcessCBOOrders\Grid\CollectionFactory;



class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;
    protected $collection;
    // @codingStandardsIgnoreStart
    public function __construct(
        \Magento\Framework\Data\CollectionFactory $collection,
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $blogCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $blogCollectionFactory->create()
        ->addFieldToSelect('*')
        ->addFieldToFilter('status', array('in' => array('order_delivered','dispatched_to_courier')))
        ->addFieldToFilter('tracking_title', array('in' => array('fedex')));
        
    }

    
}