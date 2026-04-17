<?php
namespace Plumrocket\RMA\Plugin\Admin\Order;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;
use Magento\User\Model\ResourceModel\User\Collection as UserCollection;

class Grid extends \Magento\Framework\Data\Collection
{
    protected $coreResource;

    protected $adminUsers;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        ResourceConnection $coreResource,
        UserCollection $adminUsers
    ) {
        parent::__construct($entityFactory);
        $this->coreResource = $coreResource;
        $this->adminUsers = $adminUsers;
    }

    public function beforeLoad($printQuery = false, $logQuery = false)
    {
        if ($printQuery instanceof Collection) {
            $collection = $printQuery;

            $joined_tables = array_keys(
                $collection->getSelect()->getPart('from')
            );

                $collection->getSelect()
                    ->columns(
                        array(
                            'skus' => new \Zend_Db_Expr('(SELECT GROUP_CONCAT(`sku` SEPARATOR " & ") FROM `sales_order_item` WHERE `sales_order_item`.`order_id` = main_table.`entity_id` GROUP BY `sales_order_item`.`order_id`)')
                        )
                    );

        }
    }
}