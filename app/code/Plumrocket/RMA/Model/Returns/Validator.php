<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Model\Returns;

use Magento\Framework\DataObject;
use Magento\Framework\Validator\NotEmpty as NotEmptyValidator;
use Magento\Framework\Validator\StringLength;
use Plumrocket\RMA\Helper\Config as ConfigHelper;
use Plumrocket\RMA\Helper\Returns as ReturnsHelper;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;
use Plumrocket\RMA\Model\Returns;
use Plumrocket\RMA\Model\Returns\Item\GetActiveConditionIds;
use Plumrocket\RMA\Model\Returns\Item\GetActiveReasonIds;
use Plumrocket\RMA\Model\Returns\Item\GetActiveResolutionIds;

class Validator extends DataObject
{
    /**
     * @var \Plumrocket\RMA\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Plumrocket\RMA\Helper\Returns
     */
    private $returnsHelper;

    /**
     * @var \Plumrocket\RMA\Helper\Returns\Item
     */
    private $itemHelper;

    /**
     * @var \Plumrocket\RMA\Model\Returns\Item\GetActiveReasonIds
     */
    private $getActiveReasonIds;

    /**
     * @var \Plumrocket\RMA\Model\Returns\Item\GetActiveConditionIds
     */
    private $getActiveConditionIds;

    /**
     * @var \Plumrocket\RMA\Model\Returns\Item\GetActiveResolutionIds
     */
    private $getActiveResolutionIds;

    /**
     * @var Returns
     */
    private $returns = null;

    /**
     * Validation error messages
     *
     * @var array
     */
    private $messages = [];

    /**
     * @var \Magento\Framework\Validator\NotEmpty
     */
    private $notEmptyValidator;

    /**
     * @var \Magento\Framework\Validator\StringLength
     */
    private $stringLength;

    /**
     * @param \Plumrocket\RMA\Helper\Config                             $configHelper
     * @param \Plumrocket\RMA\Helper\Returns                            $returnsHelper
     * @param \Plumrocket\RMA\Helper\Returns\Item                       $itemHelper
     * @param \Plumrocket\RMA\Model\Returns\Item\GetActiveReasonIds     $getActiveReasonIds
     * @param \Plumrocket\RMA\Model\Returns\Item\GetActiveConditionIds  $getActiveConditionIds
     * @param \Plumrocket\RMA\Model\Returns\Item\GetActiveResolutionIds $getActiveResolutionIds
     * @param \Magento\Framework\Validator\NotEmpty                     $notEmptyValidator
     * @param \Magento\Framework\Validator\StringLength                 $stringLength
     * @param array                                                     $data
     */
    public function __construct(
        ConfigHelper $configHelper,
        ReturnsHelper $returnsHelper,
        ItemHelper $itemHelper,
        GetActiveReasonIds $getActiveReasonIds,
        GetActiveConditionIds $getActiveConditionIds,
        GetActiveResolutionIds $getActiveResolutionIds,
        NotEmptyValidator $notEmptyValidator,
        StringLength $stringLength,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        $this->returnsHelper = $returnsHelper;
        $this->itemHelper = $itemHelper;
        $this->getActiveReasonIds = $getActiveReasonIds;
        $this->getActiveConditionIds = $getActiveConditionIds;
        $this->getActiveResolutionIds = $getActiveResolutionIds;
        parent::__construct($data);
        $this->notEmptyValidator = $notEmptyValidator;
        $this->stringLength = $stringLength;
    }

    /**
     * Set returns entity
     *
     * @param Returns $returns
     * @return $this
     */
    public function setReturns(Returns $returns)
    {
        $this->returns = $returns;
        return $this;
    }

    /**
     * Get returns entity
     *
     * @return Returns
     */
    public function getReturns()
    {
        return $this->returns;
    }

    /**
     * Check if is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return empty($this->messages);
    }

    /**
     * Validate comment
     *
     * @param  string $text
     * @param  array|null $files
     * @param  bool $textRequired
     * @return $this
     */
    public function validateMessage($text, $files, $textRequired = true)
    {
        if ($textRequired
            && (! is_string($text) || ! $this->notEmptyValidator->isValid(trim($text)))
        ) {
            return $this->error(__('Comment field cannot be empty'));
        }

        if (! $this->stringLength->isValid($text)) {
            return $this->error(__('Comment is too long'));
        }

        if (! empty($files) && empty($text)) {
            return $this->error(__('Comment field  cannot be empty, if you have attached files'));
        }

        return $this;
    }

    /**
     * Validate items for customer
     * - item exists
     * - can return item
     * - correct qty
     * - correct reason
     * - correct condition
     * - correct and doesn't expired resolution
     *
     * @param  array $value
     * @return $this
     */
    public function validateItemsCustomer($value)
    {
        $orderItems = $this->returnsHelper->getOrderItems(
            $this->getReturns()->getOrder()
        );

        $validItems = [];
        $hasActive = false;
        if (is_array($value)) {
            foreach ($value as $data) {
                $data = new DataObject($data);

                // Ignore row with wrong order item id.
                if (! isset($orderItems[$data->getOrderItemId()])) {
                    continue;
                }
                $orderItem = $orderItems[$data->getOrderItemId()];

                // Ignore non-requested new item.
                if ($data->getQtyRequested() < 1 || empty($data['active'])) {
                    continue;
                }

                // Check if item can be returned.
                if (! $this->itemHelper->canReturnAdmin($orderItem)) {
                    $this->error(__('"%1" cannot be returned', $orderItem->getName()));
                    continue;
                }

                $hasActive = true;

                // Validate lists.
                $cols = [
                    ItemHelper::QTY_REQUESTED,
                    ItemHelper::REASON_ID,
                    ItemHelper::CONDITION_ID,
                    ItemHelper::RESOLUTION_ID,
                ];
                foreach ($cols as $col) {
                    if (! $this->isValidColumnValue($col, $data->getData($col), $orderItem)) {
                        continue(2);
                    }
                }

                $validItems[] = [
                    ItemHelper::ORDER_ITEM_ID => $data->getData(ItemHelper::ORDER_ITEM_ID),
                    ItemHelper::REASON_ID => $data->getData(ItemHelper::REASON_ID),
                    ItemHelper::CONDITION_ID => $data->getData(ItemHelper::CONDITION_ID),
                    ItemHelper::RESOLUTION_ID => $data->getData(ItemHelper::RESOLUTION_ID),
                    ItemHelper::QTY_REQUESTED => $data->getData(ItemHelper::QTY_REQUESTED),
                ];
            }
        }

        if (! $hasActive) {
            $this->error(__('You need to choose at least one item from order'));
        }

        // Set valid items.
        $this->setValidItems($validItems);

        return $this;
    }

    /**
     * Validate items for admin
     * - order item exists
     * - item exists
     * - can return order item
     * - correct qty's
     * - correct reason
     * - correct condition
     * - correct resolution
     *
     * @param  array $value
     * @return $this
     */
    public function validateItemsAdmin($value)
    {
        $orderItems = $this->returnsHelper->getOrderItems(
            $this->getReturns()->getOrder()
        );
        $items = $this->getReturns()->getItemsCollection();

        $validItems = [];
        $qty = [];
        $hasActive = false;
        if (is_array($value)) {
            foreach ($value as $data) {
                if (is_array($data)) {
                    $data = new DataObject($data);
                }

                // Ignore row with wrong order item id.
                if (! isset($orderItems[$data->getOrderItemId()])) {
                    continue;
                }
                $orderItem = $orderItems[$data->getOrderItemId()];

                // Item Id.
                if ('' === $data->getEntityId()) {
                    // If items are exists, check if they contain an order id.
                    if ($items->count()
                        && ! $items->getItemByColumnValue(
                            ItemHelper::ORDER_ITEM_ID,
                            $data->getOrderItemId()
                        )
                    ) {
                        continue;
                    }

                    // Ignore non-requested new item.
                    if ($data->getQtyRequested() < 1) {
                        continue;
                    }

                    // Check if item can be returned.
                    if (! $this->itemHelper->canReturnAdmin($orderItem)) {
                        $this->error(__('"%1" cannot be returned', $orderItem->getName()));
                        continue;
                    }
                } else {
                    // Ignore row with wrong item id.
                    if (! $item = $items->getItemById($data->getEntityId())) {
                        continue;
                    }

                    // Check min requested value.
                    if ($data->getQtyRequested() < 1) {
                        $this->error(__('"%1" has incorrect return qty', $orderItem->getName()));
                        continue;
                    }
                }

                $hasActive = true;

                // Validate lists.
                $cols = [
                    ItemHelper::REASON_ID,
                    ItemHelper::CONDITION_ID,
                    ItemHelper::RESOLUTION_ID,
                ];
                foreach ($cols as $col) {
                    if (! $this->isValidColumnValue($col, $data->getData($col), $orderItem)) {
                        continue(2);
                    }
                }

                // Check requested qty.
                if (! isset($qty[$orderItem->getId()])) {
                    $qty[$orderItem->getId()] = 0;
                }
                $qty[$orderItem->getId()] += $data->getQtyRequested();

                if (empty($item) || ! $qtyPurchased = $item->getQtyPurchased()) {
                    $qtyPurchased = $this->itemHelper
                        ->getQtyToReturn($orderItem, $this->getReturns()->getId());
                }

                if ($qtyPurchased < $qty[$orderItem->getId()]) {
                    $this->error(__('"%1" has incorrect return qty', $orderItem->getName()));
                    continue;
                }

                // Check format and max qty of numeric fields.
                $cols = [
                    ItemHelper::QTY_REQUESTED,
                    ItemHelper::QTY_AUTHORIZED,
                    ItemHelper::QTY_RECEIVED,
                    ItemHelper::QTY_APPROVED,
                ];

                foreach ($cols as $col) {
                    if ($data->getData($col)
                        && (! is_numeric($data->getData($col))
                            || $data->getData($col) < 1)
                    ) {
                        $this->error(__('"%1" has incorrect numeric values', $orderItem->getName()));
                        continue (2);
                    }

                    $nextCol = next($cols);
                    if ($nextCol && $data->getData($nextCol) > $data->getData($col)) {
                        $this->error(__('"%1" has incorrect numeric values', $orderItem->getName()));
                        continue (2);
                    }
                }

                $validItems[] = [
                    ItemHelper::ENTITY_ID => $data->getData(ItemHelper::ENTITY_ID),
                    ItemHelper::ORDER_ITEM_ID => $data->getData(ItemHelper::ORDER_ITEM_ID),
                    ItemHelper::REASON_ID => $data->getData(ItemHelper::REASON_ID),
                    ItemHelper::CONDITION_ID => $data->getData(ItemHelper::CONDITION_ID),
                    ItemHelper::RESOLUTION_ID => $data->getData(ItemHelper::RESOLUTION_ID),
                    ItemHelper::QTY_REQUESTED => $data->getData(ItemHelper::QTY_REQUESTED),
                    ItemHelper::QTY_AUTHORIZED => $data->getData(ItemHelper::QTY_AUTHORIZED),
                    ItemHelper::QTY_RECEIVED => $data->getData(ItemHelper::QTY_RECEIVED),
                    ItemHelper::QTY_APPROVED => $data->getData(ItemHelper::QTY_APPROVED),
                ];
            }
        }

        if (! $hasActive) {
            $this->error(__('You need to choose at least one item from order'));
        }

        // Set valid items.
        $this->setValidItems($validItems);

        return $this;
    }

    /**
     * Validate column value
     *
     * @param  string $colName
     * @param  mixed $value
     * @param  \Magento\Sales\Model\Order\Item $orderItem
     * @return bool
     */
    private function isValidColumnValue($colName, $value, $orderItem)
    {
        $haystack = [];

        switch ($colName) {
            case ItemHelper::QTY_REQUESTED:
                // $haystack = range(1, $this->itemHelper->getQtyToReturn());
                $haystack = $this->itemHelper->getQtyOptions(
                    $this->itemHelper->getQtyToReturn($orderItem)
                );
                $haystack = array_keys($haystack);
                break;

            case ItemHelper::REASON_ID:
                $haystack = $this->getActiveReasonIds->get($orderItem->getStoreId());
                break;

            case ItemHelper::CONDITION_ID:
                $haystack = $this->getActiveConditionIds->get($orderItem->getStoreId());
                /**
                 * As condition field is not required $haystack should contains variants of missed condition id.
                 *
                 * '' - for admin
                 * '0' - for web api
                 */
                $haystack[] = '';
                $haystack[] = '0';
                break;

            case ItemHelper::RESOLUTION_ID:
                $haystack = $this->getActiveResolutionIds->get($orderItem->getStoreId());
                break;
        }

        if ($haystack && ! in_array($value, $haystack, false)) {
            $this->error(__(
                '"%1" has incorrect %2.',
                $orderItem->getName(),
                $this->itemHelper->getCols($colName)
            ));

            return false;
        }

        return true;
    }

    /**
     * Validate agree checkbox
     *
     * @param bool $value
     * @return $this
     */
    public function validateAgree($value)
    {
        if (empty($value)) {
            $this->error(__('You need to agree to return policy'));
        }

        return $this;
    }

    /**
     * Validate tracking carrier and number
     *
     * @param string $carrier
     * @param string $number
     * @return $this
     */
    public function validateTrack($carrier, $number)
    {
        // Validate carrier code
        $carriers = $this->configHelper->getShippingCarriers();
        if (! is_string($carrier)
            || ! in_array($carrier, $carriers, false)
        ) {
            $this->error(__('Tracking carrier is incorrect'));
        }

        // Validate number
        if (! is_string($number)
            || ! $this->notEmptyValidator->isValid(trim($number))
        ) {
            $this->error(__('Tracking number field cannot be empty'));
        }

        return $this;
    }

    /**
     * Add error message
     *
     * @param  string $message
     * @return $this
     */
    public function error($message)
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * Return error messages
     *
     * @return array
     */
    public function getMessages()
    {
        return array_unique($this->messages);
    }
}
