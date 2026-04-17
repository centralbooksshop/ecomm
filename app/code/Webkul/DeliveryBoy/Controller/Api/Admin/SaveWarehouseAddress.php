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
namespace Webkul\DeliveryBoy\Controller\Api\Admin;

use Magento\Framework\Exception\LocalizedException;

class SaveWarehouseAddress extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * Save warehouse coordinates.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $this->verifyUsernData();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->returnArray["message"] = __("Warehouse details updated successfully.");
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Verify User and Data.
     *
     * @return void
     * @throws LocalizedException
     */
    protected function verifyUsernData(): void
    {
        if (!$this->isAdmin()) {
            throw new LocalizedException(__("Unauthorized access."));
        }
    }

    /**
     * Verify Request Data.
     *
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest(): void
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }

    /**
     * Is Request from the Admin.
     *
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return $this->adminCustomerEmail === $this->deliveryboyHelper->getAdminEmail();
    }
}
