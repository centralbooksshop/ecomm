<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Webkul\DeliveryBoy\Api\Data\OrderTransactionInterface;

class OrderTransaction extends AbstractModel implements OrderTransactionInterface, IdentityInterface
{
    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    public const CACHE_TAG = "expressdelivery_order_transaction";
    
    /**
     * Default Id for when id field value is null
     */
    public const NOROUTE_ID = "no-route";

    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    protected $_cacheTag = "expressdelivery_order_transaction";
    
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = "expressdelivery_order_transaction";

    /**
     * Initialize Model object
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\DeliveryBoy\Model\ResourceModel\OrderTransaction::class);
    }

    /**
     * @inheritDoc
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteOrder();
        }
        return parent::load($id, $field);
    }

    /**
     * Load object with noroute id data
     *
     * @return self
     */
    public function noRouteOrder()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
    }

    /**
     * Return array of name of object in cache
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . "_" . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }
    
    /**
     * @inheritDoc
     */
    public function getDeliveryboyOrderId()
    {
        return parent::getData(self::DELIVERYBOY_ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryboyOrderId($deliveryboyOrderId)
    {
        parent::setData(self::DELIVERYBOY_ORDER_ID, $deliveryboyOrderId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return parent::getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setAmount($amount)
    {
        parent::setData(self::AMOUNT, $amount);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTransactionId()
    {
        return parent::getData(self::TRANSACTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionId($transactionId)
    {
        parent::setData(self::TRANSACTION_ID, $transactionId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIsClosed()
    {
        return parent::getData(self::IS_CLOSED);
    }

    /**
     * @inheritDoc
     */
    public function setIsClosed($isClosed)
    {
        parent::setData(self::IS_CLOSED, $isClosed);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        parent::setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($updatedAt)
    {
        parent::setData(self::UPDATED_AT, $updatedAt);
        return $this;
    }
}
