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
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ItemCollection;
use Magento\Sales\Block\Order\Totals as MagentoOrderTotalsBlock;

class GetRegistrationConfig extends AbstractDeliveryboy
{
    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected $deliveryboyOrderResourceCollectionInstance;

    /**
     * Get Registration Config.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment  = $this->emulate->startEnvironmentEmulation($this->storeId);
            $isAnonymousRegistrationEnabled = $this->deliveryboyHelper
                ->isEnabledAnonymousDeliveryboyRegistration();
            $this->returnArray['isRegistrationEnabled'] = $isAnonymousRegistrationEnabled;
            if ($isAnonymousRegistrationEnabled) {
                $this->returnArray['vehicle'] =
                    $this->deliveryboyHelper->getVehicleTypeList();
            }
            $this->returnArray['success'] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
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
        if ($this->getRequest()->getMethod() == "GET") {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }
}
