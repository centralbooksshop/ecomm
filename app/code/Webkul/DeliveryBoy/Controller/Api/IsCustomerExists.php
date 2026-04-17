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

class IsCustomerExists extends AbstractDeliveryboy
{
    /**
     * Save Token.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $customer = $this->customerFactory->create()->load($this->customerId);
            $this->returnArray['isCustomerExists'] = $customer->getId() > 0 ? true : false;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->returnArray["success"] = true;
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Verify Request.
     *
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest(): void
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->os = trim($this->wholeData["os"] ?? "");
            $this->customerId = trim($this->wholeData["customerId"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }
}
