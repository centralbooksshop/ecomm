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
use Webkul\DeliveryBoy\Api\Data\OrderLocationInterface;

class OrderLocation extends AbstractModel implements OrderLocationInterface, IdentityInterface
{
    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    public const CACHE_TAG = "expressdelivery_order_location";
    
    /**
     * Default Id for when id field value is null
     */
    public const NOROUTE_ID = "no-route";

    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    protected $_cacheTag = "expressdelivery_order_location";
    
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = "expressdelivery_order_location";

    /**
     * Initialize Model object
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\DeliveryBoy\Model\ResourceModel\OrderLocation::class);
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
    public function getOrderId()
    {
        return parent::getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($deliveryboyOrderId)
    {
        parent::setData(self::ORDER_ID, $deliveryboyOrderId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLatitude()
    {
        return parent::getData(self::LATITUDE);
    }

    /**
     * @inheritDoc
     */
    public function setLatitude($latitude)
    {
        parent::setData(self::LATITUDE, $latitude);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLongitude()
    {
        return parent::getData(self::LONGITUDE);
    }

    /**
     * @inheritDoc
     */
    public function setLongitude($longitude)
    {
        parent::setData(self::LONGITUDE, $longitude);
        return $this;
    }
}
