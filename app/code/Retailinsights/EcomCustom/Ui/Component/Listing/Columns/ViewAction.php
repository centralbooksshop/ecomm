<?php
namespace Retailinsights\EcomCustom\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory as DeliveryboyOrderCollF;

class ViewAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param DeliveryboyOrderCollF $deliveryboyOrderCollF
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        DeliveryboyOrderCollF $deliveryboyOrderCollF,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->deliveryboyOrderCollF = $deliveryboyOrderCollF;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Initialize data source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as &$item) {
            $name = $this->getData("name");
            $incrementId = $item['increment_id'];
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		    $orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);
            $orderId = $orderInfo->getId();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
			$sales_order_item_table = $resource->getTableName('sales_order_item'); 
				if(!empty($orderId)) {
					//SELECT product_id,NAME,optional_selected_items,parent_item_id FROM sales_order_item WHERE order_id =353078
					$sales_order_item_sql = "select product_id,name,optional_selected_items from " . $sales_order_item_table." where order_id =".$orderId;
					$sales_order_item_result = $connection->fetchAll($sales_order_item_sql); 
					$optionalItems = array();
					$optItems= array();
					$optItemsval= array();
					$optItemsIds= array();
					$productname = '';
					if(!empty($sales_order_item_result)) {
						foreach ($sales_order_item_result as $key => $sales_order_item_value) {
						   if(!empty($sales_order_item_value['optional_selected_items'])) {
							  $optionalItems = explode(',', $sales_order_item_value['optional_selected_items']);
							}

							$product_id = $sales_order_item_value['product_id'];

							if (!in_array($product_id, $optionalItems)) {
								 $optItems[]=$sales_order_item_value['name'];
							}
						}

						$productname = implode(", ", $optItems);
					}
				}
			    if(isset($productname)) {
				   $item['optional'] = $productname;
			    }
            }
        }
        return $dataSource;
    }
}
