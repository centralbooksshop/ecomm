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
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface;

class Deliveryboy extends AbstractModel implements DeliveryboyInterface, IdentityInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    public const TYPE_BYKE = "bike";
    public const TYPE_CYCLE = "cycle";
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;
    
    /**
     * Default Id for when id field value is null
     */
    public const NOROUTE_ID = "no-route";
    
    /**
     * Tag to associate cache entries with
     */
    public const CACHE_TAG = "expressdelivery_deliveryboy";
    
    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    protected $_cacheTag = "expressdelivery_deliveryboy";

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = "expressdelivery_deliveryboy";

    /**
     * @var \Webkul\DeliveryBoy\Model\Deliveryboy\Validator\CompositeValidator
     */
    private $validator;

    /**
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy\Validator\CompositeValidator $validator
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\VehicleType\CollectionFactory $vehicleTypesCollF
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy|null $resource
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Webkul\DeliveryBoy\Model\Deliveryboy\Validator\CompositeValidator $validator,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\DeliveryBoy\Model\ResourceModel\VehicleType\CollectionFactory $vehicleTypesCollF,
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy $resource = null,
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $resourceCollection = null,
        array $data = []
    ) {
        $this->validator = $validator;
        $this->vehicleTypesCollF = $vehicleTypesCollF;
        
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize model object
     */
    protected function _construct()
    {
        $this->_init(\Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy::class);
    }

    /**
     * Load object data
     *
     * @param int $id
     * @param null|string $field
     * @return self
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteDeliveryboy();
        }
        return parent::load($id, $field);
    }

    /**
     * Return No route deliveryboy.
     *
     * @return self
     */
    public function noRouteDeliveryboy()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
    }

    /**
     * Get Deliveryboy Statues.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED  => __("Enabled"),
            self::STATUS_DISABLED => __("Disabled")
        ];
    }

    /**
     * Get deliveryboy available types.
     *
     * @return array
     */
    public function getAvailableTypes()
    {
        $vehicleTypes = [];
        $vehicleTypesColl = $this->vehicleTypesCollF->create();
        foreach ($vehicleTypesColl as $vehicleType) {
            $vehicleTypes[$vehicleType->getValue()] = $vehicleType->getLabel();
        }
        return $vehicleTypes;
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
    public function getName()
    {
        return parent::getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getEmail()
    {
        return parent::getData(self::EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheritDoc
     */
    public function getImage()
    {
        return parent::getData(self::IMAGE);
    }

    /**
     * @inheritDoc
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getAddress()
    {
        return parent::getData(self::ADDRESS);
    }

    /**
     * @inheritDoc
     */
    public function setAddress($address)
    {
        return $this->setData(self::ADDRESS, $address);
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
        return $this->setData(self::LATITUDE, $latitude);
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
        return $this->setData(self::LONGITUDE, $longitude);
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return parent::getData(self::PASSWORD);
    }

    /**
     * @inheritDoc
     */
    public function setPassword($password)
    {
        return $this->setData(self::PASSWORD, $password);
    }

    /**
     * @inheritDoc
     */
    public function getRpToken()
    {
        return parent::getData(self::RP_TOKEN);
    }

    /**
     * @inheritDoc
     */
    public function setRpToken($rpToken)
    {
        return $this->setData(self::RP_TOKEN, $rpToken);
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
        return $this->setData(self::CREATED_AT, $createdAt);
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
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function getVehicleType()
    {
        return parent::getData(self::VEHICLE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setVehicleType($vehicleType)
    {
        return $this->setData(self::VEHICLE_TYPE, $vehicleType);
    }

    /**
     * @inheritDoc
     */
    public function getMobileNumber()
    {
        return parent::getData(self::MOBILE_NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function setMobileNumber($mobileNumber)
    {
        return $this->setData(self::MOBILE_NUMBER, $mobileNumber);
    }

    /**
     * @inheritDoc
     */
    public function getVehicleNumber()
    {
        return parent::getData(self::VEHICLE_NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function setVehicleNumber($vehicleNumber)
    {
        return $this->setData(self::VEHICLE_NUMBER, $vehicleNumber);
    }

    /**
     * @inheritDoc
     */
    public function getRpTokenCreatedAt()
    {
        return parent::getData(self::RP_TOKEN_CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setRpTokenCreatedAt($rpTokenCreatedAt)
    {
        return $this->setData(self::RP_TOKEN_CREATED_AT, $rpTokenCreatedAt);
    }

    /**
     * @inheritDoc
     */
    public function getAvailabilityStatus()
    {
        return parent::getData(self::AVAILABILITY_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setAvailabilityStatus($availabilityStatus)
    {
        return $this->setData(self::AVAILABILITY_STATUS, $availabilityStatus);
    }

    /**
     * Get Validator instance.
     *
     * @return \Zend_Validate_Interface|null
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }
}
