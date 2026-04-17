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
namespace Webkul\DeliveryBoy\Controller\Api;

use Magento\Framework\Exception\LocalizedException;
use Webkul\DeliveryBoy\Model\Deliveryboy\Source\ApproveStatus;

class Login extends AbstractDeliveryboy
{
    /**
     * Login
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->iconHeight = $this->iconWidth = 144 * $this->mFactor;
            if (!\Zend_Validate::is($this->username, "EmailAddress")) {
                throw new LocalizedException(__("Invalid Username."));
            }
            if ($this->password == "") {
                throw new LocalizedException(__("Invalid Password."));
            }
            $data = $this->verifyUser();
            if(isset($data['success'])) {
                return $this->getJsonResponse($data);
            }
            $this->saveToken();
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->returnArray["trace"] = $e->getTraceAsString();
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Verify User.
     *
     * @return void
     * @throws LocalizedException
     */
    protected function verifyUser()
    {
        $this->returnArray["isDeliveryBoy"] = false;
        $customer = $this->customerFactory->create()
            ->setWebsiteId($this->websiteId)->loadByEmail($this->username);
        if ($this->username === $this->deliveryboyHelper->getAdminEmail()) {
            $this->applyCustomer($customer);
            $this->returnArray["isAdmin"] = true;
        } else {
            $this->returnArray["isAdmin"] = false;
            $deliveryboy = $this->deliveryboyResourceCollection
                ->create()
                ->addFieldToFilter("email", $this->username)
                ->getFirstItem();
            if ($deliveryboy->getStatus() != 1) {
                $this->returnArray["success"] = false;
                $this->returnArray["message"] = "The Deliveryboy is not enabled. Please contact admin.";
                return $this->returnArray;
            }
            if ($deliveryboy->getApproveStatus() != ApproveStatus::STATUS_APPROVED) {
                $this->returnArray["success"] = false;
                $this->returnArray["message"] = "The Deliveryboy is not enabled. Please contact admin.";
                return $this->returnArray;
            }
            if (!$deliveryboy->getId()) {
                $this->returnArray["success"] = false;
                $this->returnArray["message"] = "No user with this username exist.";
                return $this->returnArray;
            } else {
                if ($deliveryboy->getPassword() == $this->operationHelper->getSha256Hash($this->password)) {
                    $availableTypes = $this->deliveryboy->getAvailableTypes();
                    $this->returnArray["id"] = $deliveryboy->getId();
                    $this->returnArray["name"] = $deliveryboy->getName();
                    $this->returnArray["email"] = $deliveryboy->getEmail();
                    $this->returnArray["mobile"] = $deliveryboy->getMobileNumber();
                    $adminEmail = $this->deliveryboyHelper->getAdminEmail();
                    $customer = $this->customerFactory->create()
                        ->setWebsiteId($this->websiteId)->loadByEmail($adminEmail);
                    $this->returnArray['ownerId'] = $customer->getId();
                    $this->returnArray['chatId'] = $customer->getId();
                    $this->returnArray["success"] = true;
                    $this->returnArray["vehicleType"] = $availableTypes[$deliveryboy->getVehicleType()] ?? "";
                    $this->returnArray["onlineStatus"] = (bool)$deliveryboy->getAvailabilityStatus();
                    $this->returnArray["vehicleNumber"] = $deliveryboy->getVehicleNumber();
                    $this->returnArray["isDeliveryBoy"] = true;
                    $newUrl = "";
                    $basePath = $this->baseDir . DIRECTORY_SEPARATOR . $deliveryboy->getImage();
                    try {
                        if ($this->fileDriver->isFile($basePath)) {
                            $newPath = $this->baseDir . DIRECTORY_SEPARATOR . "deliveryboyresized" .
                                DIRECTORY_SEPARATOR.
                                $this->iconWidth . "x" . $this->iconHeight . DIRECTORY_SEPARATOR .
                                $deliveryboy->getImage();
                            $this->helperCatalog->resizeNCache(
                                $basePath,
                                $newPath,
                                $this->iconWidth,
                                $this->iconHeight
                            );
                            $newUrl = $this->deliveryboyHelper->getUrl("media") . "deliveryboyresized" .
                                DIRECTORY_SEPARATOR .
                                $this->iconWidth . "x" . $this->iconHeight . DIRECTORY_SEPARATOR .
                                $deliveryboy->getImage();
                        }
                    } catch (\Throwable $t) {
                        $this->logger->debug($t->getMessage());
                    }
                    $this->returnArray["avatar"] = $newUrl;
                } else {
                    throw new LocalizedException(__("Invalid Password."));
                }
            }
        }
    }

    /**
     * Apply Customer to Response.
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     */
    protected function applyCustomer(\Magento\Customer\Model\Customer $customer): void
    {
        $hash = $customer->getPasswordHash();
        if (null == $customer->getId()) {
            throw new LocalizedException(__("Invalid username or password."));
        }
        $validatePassword = $this->encryptor->validateHash($this->password, $hash);
        if (!$validatePassword) {
            throw new LocalizedException(__("Invalid username or password."));
        } else {
            $this->returnArray["id"] = $customer->getId();
            $this->returnArray["name"] = $customer->getName();
            $this->returnArray["email"] = $customer->getEmail();
            $this->returnArray["success"] = true;
        }
    }

    /**
     * Verify Request.
     *
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest(): void
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->os = trim($this->wholeData["os"] ?? "android");
            $this->token = trim($this->wholeData["token"] ?? "");
            $this->mFactor = trim($this->wholeData["mFactor"] ?? 1);
            $this->username = trim($this->wholeData["username"] ?? "");
            $this->password = trim($this->wholeData["password"] ?? "");
            $this->websiteId = trim($this->wholeData["websiteId"] ?? 1);
            $this->appendDefaultStoreData();
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }

    /**
     * Save Token.
     *
     * @return void
     */
    public function saveToken(): void
    {
        $tokenCollection = $this->tokenFactory->create()
            ->getCollection()
            ->addFieldToFilter('token', $this->token);
        $deliveryboyId = $this->isDeliveryboy() ? $this->returnArray["id"] : 0;
        if ($tokenCollection->getSize() > 0) {
            foreach ($tokenCollection as $eachRow) {
                $token = $this->tokenFactory->create()
                    ->load($eachRow->getId());
                $this->applyRequestToToken($token);
                $token->save();
            }
        } else {
            $token = $this->tokenFactory
                ->create()
                ->setOs($this->os)
                ->setToken($this->token);
                $this->applyRequestToToken($token);
                $token->save();
        }
    }

    /**
     * Append Default store Data.
     *
     * @return void
     */
    public function appendDefaultStoreData()
    {
        $defaultStore = $this->storeManager->getDefaultStoreView();
        $this->returnArray['defaultStore'] = [
            'id' => $defaultStore->getId(),
            'code' => $defaultStore->getCode(),
            'name' => $defaultStore->getName(),
        ];
    }

    /**
     * Is Deliveyrboy.
     *
     * @return bool
     */
    protected function isDeliveryboy(): bool
    {
        return $this->returnArray["isDeliveryBoy"];
    }

    /**
     * Apply Request To Token.
     *
     * @param \Webkul\DeliveryBoy\Model\Token $token
     * @return \Webkul\DeliveryBoy\Model\Token
     */
    protected function applyRequestToToken(\Webkul\DeliveryBoy\Model\Token $token): \Webkul\DeliveryBoy\Model\Token
    {
        $isAdmin = (int)$this->returnArray["isAdmin"] ?: 0;
        $token->setIsAdmin($isAdmin);
        $deliveryboyId = $this->returnArray["isAdmin"] ? 0 : $this->returnArray["id"];
        $token->setDeliveryboyId($deliveryboyId);

        return $token;
    }
}
