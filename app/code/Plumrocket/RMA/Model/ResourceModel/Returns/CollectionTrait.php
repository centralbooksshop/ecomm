<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket RMA v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


namespace Plumrocket\RMA\Model\ResourceModel\Returns;

trait CollectionTrait
{
    /**
     * Add Return data to collection
     *
     * @return $this
     */
    public function addReturnsItemData()
    {
        $this->getSelect()->joinLeft(
            ['r' => $this->getTable('plumrocket_rma_returns_item')],
           
            'r.parent_id = main_table.entity_id',
            ['r.reason_id as reason_id', 'r.resolution_id']
        )->joinLeft(
             ['rs' => $this->getTable('plumrocket_rma_reason')],
            'r.reason_id = rs.id',
            ['rs.title as rma_reason']
        )->joinLeft(
             ['rl' => $this->getTable('plumrocket_rma_resolution')],
            'r.resolution_id = rl.id',
            ['rl.title as resolution_id']
        );
        return $this;
    }
    /**
     * Add Return data to collection
     *
     * @return $this
     */
    public function addReturnedItems()
    {
        $this->getSelect()->joinLeft(
            ['pr' => $this->getTable('plumrocket_rma_returns_item')],
           
            'pr.parent_id = main_table.entity_id',
            ['pr.order_item_id']
        )->joinLeft(
             ['st' => $this->getTable('sales_order_item')],
            'pr.order_item_id = st.item_id',
            ['st.name as returned_items']
        );

        return $this;
    }

	 /**
     * Add addProductName items to collection
     *
     * @return $this
     */
    public function addProductName()
    {
        $this->getSelect()->join(
            ['soi' => $this->getTable('sales_order_item')],
            'soi.order_id = main_table.order_id',
             ['soi.name as product_name']
            
        )->where('soi.parent_item_id IS NULL');

        return $this;
    }

    /**
     * Add order data to collection
     *
     * @return $this
     */
    public function addOrderData()
    {
        $this->getSelect()->join(
            ['o' => $this->getTable('sales_order')],
            'o.entity_id = main_table.order_id',
            [
                'increment_id as order_increment_id',
                // 'GREATEST(COALESCE(o.`created_at`, 0), COALESCE(o.`updated_at`, 0)) as order_date'
                'updated_at as order_date', 'store_id','school_name' => 'o.school_name'
            ]
        );

        return $this;
    }
    /**
     * Add order data to collection
     *
     * @return $this
     */
    /*public function addSchoolName()
    {
        $this->getSelect()->join(
            ['sales_order' => $this->getTable('sales_order')],
            'sales_order.entity_id = main_table.order_id',
            [
                'increment_id as order_increment_id','updated_at as order_date', 'school_name'
            ]
        );

        return $this;
    }*/

	 public function addSchoolCode() {

	    $this->getSelect()->joinLeft(
            ['sr' => $this->getTable('schools_registered')],
            'o.school_code = sr.school_code',
            [
                'location_code'
            ]
        );


        return $this;
	 }
    /**
     * Add order items to collection
     *
     * @return $this
     */
    public function addRmaItems()
    {
        $this->getSelect()->join(
            ['ri' => $this->getTable('plumrocket_rma_returns')],
            'ri.order_id = main_table.order_id',
             array('ri.entity_id as rma_items')
        );

        return $this;
    }

    /**
     * Add customer data to collection
     *
     * @return $this
     */
    public function addCustomerData()
    {
        $this->getSelect()->joinLeft(
            ['cgf' => $this->getTable('customer_grid_flat')],
            'cgf.entity_id = o.customer_id',
            ['customer_name' => 'cgf.name','customer_address' => 'cgf.shipping_full']
        );

        return $this;
    }

    /**
     * Add admin data to collection
     *
     * @return $this
     */
    public function addAdminData()
    {
        $this->getSelect()->joinLeft(
            ['au' => $this->getTable('admin_user')],
            'au.user_id = main_table.manager_id',
            ['COALESCE(CONCAT(au.`firstname`, " ", au.`lastname`), "N/A") as manager_name']
        );

        return $this;
    }

    /**
     * Add data of last reply to collection
     *
     * @return $this
     */
    public function addLastReplyData()
    {
        $messagesTable = '(SELECT created_at,name,entity_id,parent_id FROM ' .
            $this->getTable('plumrocket_rma_returns_message') .
            ' WHERE is_system = 0 AND is_internal = 0 ORDER BY entity_id DESC)';

        $this->getSelect()->joinLeft(
            ['rm' => new \Zend_Db_Expr($messagesTable)],
            'rm.parent_id = main_table.entity_id',
            ['rm.created_at as reply_at', 'rm.name as reply_name']
        );

        $this->getSelect()->group('main_table.entity_id');

        return $this;
    }
}
