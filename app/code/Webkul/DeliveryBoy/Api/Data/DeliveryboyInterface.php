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
namespace Webkul\DeliveryBoy\Api\Data;

interface DeliveryboyInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    public const ID = "id";
    public const NAME = "name";
    public const EMAIL = "email";
    public const IMAGE = "image";
    public const STATUS = "status";
    public const ADDRESS = "address";
    public const LATITUDE = "latitude";
    public const PASSWORD = "password";
    public const RP_TOKEN = "rp_token";
    public const LONGITUDE = "longitude";
    public const CREATED_AT = "created_at";
    public const UPDATED_AT = "updated_at";
    public const VEHICLE_TYPE = "vehicle_type";
    public const MOBILE_NUMBER = "mobile_number";
    public const VEHICLE_NUMBER = "vehicle_number";
    public const RP_TOKEN_CREATED_AT = "rp_token_created_at";
    public const AVAILABILITY_STATUS = "availability_status";

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId();
    
    /**
     * Set Id
     *
     * @param  int $id
     * @return self
     */
    public function setId($id);
    
    /**
     * Get Name
     *
     * @return string|null
     */
    public function getName();
    
    /**
     * Set Name
     *
     * @param  string $name
     * @return self
     */
    public function setName($name);
    
    /**
     * Get Email
     *
     * @return string|null
     */
    public function getEmail();
    
    /**
     * Set Email
     *
     * @param  string $email
     * @return self
     */
    public function setEmail($email);
    
    /**
     * Get Image
     *
     * @return string|null
     */
    public function getImage();
    
    /**
     * Set Image
     *
     * @param  string $image
     * @return self
     */
    public function setImage($image);
    
    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();
    
    /**
     * Set Status
     *
     * @param  int $status
     * @return self
     */
    public function setStatus($status);
    
    /**
     * Get Address.
     *
     * @return string|null
     */
    public function getAddress();
    
    /**
     * Set Address.
     *
     * @param  string $address
     * @return self
     */
    public function setAddress($address);
    
    /**
     * Get Latitude
     *
     * @return string|null
     */
    public function getLatitude();
    
    /**
     * Set Latitude.
     *
     * @param  string $latitude
     * @return self
     */
    public function setLatitude($latitude);
    
    /**
     * Get Longitude.
     *
     * @return string|null
     */
    public function getLongitude();
    
    /**
     * Set Longitude.
     *
     * @param  string $longitude
     * @return self
     */
    public function setLongitude($longitude);
    
    /**
     * Get Password.
     *
     * @return string|null
     */
    public function getPassword();
    
    /**
     * Set Password
     *
     * @param  string $password
     * @return self
     */
    public function setPassword($password);
    
    /**
     * Get Rp TOken
     *
     * @return string|null
     */
    public function getRpToken();
    
    /**
     * Set Rp TOken
     *
     * @param  string $rpToken
     * @return self
     */
    public function setRpToken($rpToken);
    
    /**
     * Get Created At.
     *
     * @return string
     */
    public function getCreatedAt();
    
    /**
     * Set Created at.
     *
     * @param  string $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt);
    
    /**
     * Get Updated at.
     *
     * @return string
     */
    public function getUpdatedAt();
    
    /**
     * Set Updated at.
     *
     * @param  string $updatedAt
     * @return self
     */
    public function setUpdatedAt($updatedAt);
    
    /**
     * Get Vehicle Type.
     *
     * @return string
     */
    public function getVehicleType();
    
    /**
     * Set Vehicle type.
     *
     * @param  string $vehicleType
     * @return self
     */
    public function setVehicleType($vehicleType);
    
    /**
     * Get Mobile Number.
     *
     * @return string|null
     */
    public function getMobileNumber();
    
    /**
     * Set Mobile Number.
     *
     * @param  string $mobileNumber
     * @return self
     */
    public function setMobileNumber($mobileNumber);
    
    /**
     * Get Vehicle Number.
     *
     * @return string|null
     */
    public function getVehicleNumber();
    
    /**
     * Set Vehicle Number.
     *
     * @param  string $vehicleNumber
     * @return self
     */
    public function setVehicleNumber($vehicleNumber);
    
    /**
     * Get Rp TOken Created at..
     *
     * @return string
     */
    public function getRpTokenCreatedAt();
    
    /**
     *  Set Rp TOken Created at..
     *
     * @param  string $rpTokenCreatedAt
     * @return self
     */
    public function setRpTokenCreatedAt($rpTokenCreatedAt);
    
    /**
     * Get Availability Status
     *
     * @return int|null
     */
    public function getAvailabilityStatus();
    
    /**
     * Set Availability Status.
     *
     * @param  int $availabilityStatus
     * @return self
     */
    public function setAvailabilityStatus($availabilityStatus);
}
