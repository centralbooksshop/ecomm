<?php
declare(strict_types=1);

namespace Retailinsights\Orders\Plugin\Bundle;

use Magento\Checkout\Controller\Cart\Index;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Quote\Model\Quote;

class BundleCartNotice
{
    private CheckoutSession $checkoutSession;

    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function afterExecute(Index $subject, $result)
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote || !$quote->getId()) {
            return $result;
        }

        $allItems = $quote->getAllItems();

        foreach ($quote->getAllVisibleItems() as $parentItem) {

            /** Only bundle parents */
            if ($parentItem->getProduct()->getTypeId() !== BundleType::TYPE_CODE) {
                continue;
            }

            /** Skip already invalidated bundle */
            if ((int)$parentItem->getData('bundle_invalid') === 1) {
                continue;
            }

            /** Collect child items */
            $childItems = [];
            foreach ($allItems as $item) {
                if ((int)$item->getParentItemId() === (int)$parentItem->getItemId()) {
                    $childItems[] = $item;
                }
            }

            if (!$childItems) {
                continue;
            }

            /** Current bundle config */
            $product      = $parentItem->getProduct();
            $typeInstance = $product->getTypeInstance();
            $optionIds    = $typeInstance->getOptionsIds($product);
            $selections   = $typeInstance->getSelectionsCollection($optionIds, $product);

            foreach ($childItems as $childItem) {

               $selectionId = $this->getSelectionIdFromChild($childItem);

				if (!$selectionId) {
					continue;
				}

				$selection = $selections->getItemById($selectionId);

				if (!$selection) {
					continue;
				}


                if (!$selection) {
                    continue;
                }

                /**  Disabled / removed selection */
                if (!$selection->isSalable()) {
                    $this->invalidateBundle($quote, $parentItem);
                    break;
                }

                /**  Default qty changed */
                if ((float)$selection->getSelectionQty() !== (float)$childItem->getQty()) {
                    $this->invalidateBundle($quote, $parentItem);
                    break;
                }

                /**  Custom field changed */
                $cartGivenOption = trim((string)$childItem->getData('given_options'));
                $currentCustom   = trim((string)$selection->getCustomField());

                if ($currentCustom !== '' && $currentCustom !== $cartGivenOption) {
                    $this->invalidateBundle($quote, $parentItem);
                    break;
                }
            }
        }

        return $result;
    }

	private function getSelectionIdFromChild($childItem): ?int
	{
		foreach ($childItem->getOptions() as $option) {
			if ($option->getCode() === 'selection_id') {
				return (int)$option->getValue();
			}
		}
		return null;
	}


    private function invalidateBundle(Quote $quote, $parentItem): void
    {
        /** Reload item from quote (CRITICAL) */
        $item = $quote->getItemById((int)$parentItem->getItemId());
        if (!$item) {
            return;
        }

        if ((int)$item->getData('bundle_invalid') === 1) {
            return;
        }

        /** Mark bundle invalid */
        $item->setData('bundle_invalid', 1);
        $item->setHasError(true);

        /** INLINE ITEM MESSAGE (THIS RENDERS) */
        $item->addMessage(
            __('This bundle product was updated. Please review it before checkout.'),
            'error'
        );

        /** Disable checkout globally */
        $quote->setData('bundle_invalid', 1);

        /** Force cart UI refresh */
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
    }
}
