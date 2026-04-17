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
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface as DeliveryboyInterface;

class DeleteDeliveryboyReview extends \Webkul\DeliveryBoy\Controller\Api\AbstractRating
{
    /**
     * Delete Deliveryboy Review.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->validateRequest();
            $this->extractRequestData();
            $this->authorize();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $rating = $this->getRatingById($this->ratingId);
            $rating->delete();

            $this->emulate->stopEnvironmentEmulation($environment);
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Review deleted successfully.");
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
    public function validateRequest(): void
    {
        if (!($this->getRequest()->getMethod() == "POST" && $this->wholeData)) {
            throw new LocalizedException(__("Invalid request."));
        }
    }

    /**
     * Extract Request data.
     *
     * @return void
     */
    public function extractRequestData(): void
    {
        $this->storeId = trim($this->wholeData["storeId"] ??
                $this->storeManager->getDefaultStoreView()->getId());
        $this->ratingId = trim($this->wholeData["ratingId"] ?? 0);
        $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
    }

    /**
     * Authorize Request.
     *
     * @return bool
     * @throws LocalizedException
     */
    public function authorize()
    {
        if (!$this->isAdmin()) {
            throw new LocalizedException(__('Unauthorized access.'));
        }

        return true;
    }

    /**
     * Is Admin User.
     *
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return $this->adminCustomerEmail === $this->deliveryboyHelper->getAdminEmail();
    }

    /**
     * Get Rating by id.
     *
     * @param int $ratingId
     * @return \Webkul\Deliveryboy\Api\Data\RatingInterface
     * @throws NoSuchEntityException
     */
    protected function getRatingById(
        int $ratingId
    ): \Webkul\Deliveryboy\Api\Data\RatingInterface {
        $rating = $this->ratingFactory->create()->load($ratingId);
        if ($ratingId > 0 && $rating->getId() == null) {
            throw new NoSuchEntityException(__("Invalid rating."));
        }
        if ($rating->getId() == null) {
            $rating = $this->ratingFactory->create();
        }

        return $rating;
    }
}
