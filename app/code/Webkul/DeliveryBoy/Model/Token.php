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
use Webkul\DeliveryBoy\Api\Data\TokenInterface;

class Token extends AbstractModel implements TokenInterface, IdentityInterface
{
    /**
     * Tag to associate cache entries with
     */
    public const CACHE_TAG = "expressdelivery_token";
    
    /**
     * Default Id for when id field value is null
     */
    public const NOROUTE_ID = "no-route";

    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    protected $_cacheTag = "expressdelivery_token";

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = "expressdelivery_token";

    /**
     * Initialize model object
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\DeliveryBoy\Model\ResourceModel\Token::class);
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
     * @inheritDoc
     */
    public function noRouteOrder()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
    }

    /**
     * Return array of name of object in cache
     *
     * @return string[]
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
    public function getOs()
    {
        return parent::getData(self::OS);
    }

    /**
     * @inheritDoc
     */
    public function setOs($os)
    {
        return $this->setData(self::OS, $os);
    }

    /**
     * @inheritDoc
     */
    public function getToken()
    {
        return parent::getData(self::TOKEN);
    }

    /**
     * @inheritDoc
     */
    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryboyId()
    {
        return parent::getData(self::DELIVERYBOY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryboyId($deliveryboyId)
    {
        return $this->setData(self::DELIVERYBOY_ID, $deliveryboyId);
    }
}
