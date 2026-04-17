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

class EditReview extends \Webkul\DeliveryBoy\Controller\Api\AbstractRating
{
    /**
     * Edit Review.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->verifyUsernData();
            $rating = $this->ratingRepository->getById($this->ratingId);
            if (!$rating->getId()) {
                throw new LocalizedException(__("Review not found"));
            }
            if ($this->title) {
                $rating->setTitle($this->title);
            }
            if (in_array($this->status, array_keys($this->ratingStatus->toOptionArray()))) {
                $rating->setStatus($this->status);
            }
            if ($this->comment) {
                $rating->setComment($this->comment);
            }
            $this->ratingRepository->save($rating);
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Review updated successfully.");
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Verify Usern Data.
     *
     * @return void
     * @throws LocalizedException
     */
    protected function verifyUsernData(): void
    {
        if ($this->adminCustomerEmail !== $this->deliveryboyHelper->getAdminEmail()) {
            throw new LocalizedException(__("Unauthorized access."));
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
        if ("POST" === $this->getRequest()->getMethod() && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->ratingId = trim($this->wholeData["ratingId"] ?? 0);
            $this->title = trim($this->wholeData["title"] ?? "");
            $this->comment = trim($this->wholeData["comment"] ?? "");
            $this->status = trim($this->wholeData["status"] ?? "");
            $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }
}
