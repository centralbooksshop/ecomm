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

class EditDeliveryboyReview extends \Webkul\DeliveryBoy\Controller\Api\AbstractRating
{
    /**
     * Edit Deliveryboy Review.
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
            
            if ($this->isTitleValid($this->title)) {
                $rating->setTitle($this->title);
            }
            if ($this->isCommentValid($this->comment)) {
                $rating->setComment($this->comment);
            }
            if ($this->isStatusValid($this->status)) {
                $rating->setStatus($this->status);
            }
            if ($this->deliveryboyId > 0) {
                $rating->setDeliveryboyId($this->deliveryboyId);
            }
            if ($this->customerId > 0) {
                $rating->setCustomerId($this->customerId);
            }
            if ($this->rating >= 1 && $this->rating <= 5) {
                $rating->setRating($this->rating);
            }

            $rating->save();
            
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->returnArray["success"] = true;
            $this->returnArray['statusList'] = $this->getDeliveryboyReviewStatusList();
            $ratingDataArray = $this->buildReviewDataForAppFromReviewModel($this->mFactor, $rating);
            $this->returnArray = array_merge($this->returnArray, $ratingDataArray);
            if ($this->ratingId > 0) {
                $this->returnArray["message"] = __("Review updated successfully.");
            } else {
                $this->returnArray["message"] = __("Review saved successfully.");
            }
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }
        
        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Validate Request.
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
     * Extract Request Data.
     *
     * @return void
     */
    public function extractRequestData(): void
    {
        $this->storeId = trim($this->wholeData["storeId"] ??
                $this->storeManager->getDefaultStoreView()->getId());
        $this->pageNumber = trim($this->wholeData["pageNumber"] ?? 1);
        $this->ratingId = trim($this->wholeData["ratingId"] ?? 0);
        $this->title = trim($this->wholeData["title"] ?? "");
        $this->mFactor = trim($this->wholeData["mFactor"] ?? 1);
        $this->comment = trim($this->wholeData["comment"] ?? "");
        $this->rating = trim($this->wholeData["rating"] ?? 1);
        $this->customerId = trim($this->wholeData["customerId"] ?? "");
        $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? "");
        $this->status = trim($this->wholeData["status"] ?? 3);
        $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
    }

    /**
     * Build Review Data from Request.
     *
     * @param float $mFactor
     * @param Rating $reviewModel
     * @return array
     */
    public function buildReviewDataForAppFromReviewModel($mFactor, $reviewModel)
    {
        $result = parent::buildReviewDataForAppFromReviewModel($mFactor, $reviewModel);
        $result['author'] = $result['customerName'];
        $deliveryboyModel = $this->deliveryboyHelper->getDeliveryboyModelById(
            $reviewModel->getDeliveryboyId()
        );
        $result['email'] = $deliveryboyModel->getEmail();
        return $result;
    }

    /**
     * Authorize Current User.
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
     * Is Deliveryboy Exists.
     *
     * @param int $deliveryboyId
     * @return bool
     */
    protected function isDeliveryboyExists(int $deliveryboyId): bool
    {
        return $this->deliveryboyResourceCollection->create()
            ->addFieldToFilter(DeliveryboyInterface::ID, $deliveryboyId)
            ->getFirstItem()->getId() == $deliveryboyId;
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
     * Is Status Valid.
     *
     * @param int $statusId
     * @return bool
     */
    protected function isStatusValid(int $statusId): bool
    {
        return (in_array($statusId, $this->getRatingStatusValueArray()));
    }

    /**
     * Is Title Valid.
     *
     * @param string $title
     * @return bool
     */
    protected function isTitleValid(string $title): bool
    {
        return (!empty($title) && (strlen($title) >= 3));
    }

    /**
     * Is Vomment Valid.
     *
     * @param string $comment
     * @return bool
     */
    protected function isCommentValid(string $comment): bool
    {
        return !empty($comment);
    }

    /**
     * Get Rating Id.
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
