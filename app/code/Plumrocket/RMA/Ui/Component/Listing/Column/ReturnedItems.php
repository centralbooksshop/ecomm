<?php

namespace Plumrocket\RMA\Ui\Component\Listing\Column;

use Magento\Framework\App\ResourceConnection;

class ReturnedItems extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $resourceConnection;
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ){
         $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $entity_id = $item['entity_id'];

                $connection = $this->resourceConnection->getConnection();
                $table = $connection->getTableName('plumrocket_rma_returns_item');
                $query = "SELECT `order_item_id` FROM `" . $table . "` WHERE parent_id = $entity_id";

                $tableSalesOrder = $connection->getTableName('sales_order_item');
                $result = $connection->fetchAll($query);
                $data='';
                foreach ($result as $key => $value) {
                if($key >0){
                    $data .= ',<br>';
                }
                    $order_item_id = $value['order_item_id'];
                    $querySales = "SELECT `name` FROM `" . $tableSalesOrder . "` WHERE item_id = $order_item_id";
                    $result = $connection->fetchAll($querySales);
                    foreach ($result as $key => $valueName) {
                        $data .= $valueName['name'];
                    }
                } 
                $item['returned_items'] = $data;
            }
        }
       
        //Retailinsights/Pricerules/view/adminhtml/templares/buyxyz.phtml
        return $dataSource;
    }
}