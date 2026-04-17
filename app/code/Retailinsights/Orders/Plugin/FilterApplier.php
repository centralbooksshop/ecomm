<?php

namespace Retailinsights\Orders\Plugin;

use Magento\Framework\Api\Filter;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;
use Magento\Sales\Api\Data\OrderInterface;


class FilterApplier
{
    const SALES_ORDER_GRID_NAMESPACE = 'sales_order_grid';
	const SALES_ORDER_DUPLICATE_GRID_NAMESPACE = 'retailinsights_salesordergrid_list';
    protected $request;

    public function __construct(
        Http $request
    )
    {
        $this->request = $request;
    }

    public function beforeApply(FilterApplierInterface $subject, Collection $collection, Filter $filter)
    {
        $namespace = $this->request->getParam('namespace');
        if ($namespace == self::SALES_ORDER_GRID_NAMESPACE || $namespace == self::SALES_ORDER_DUPLICATE_GRID_NAMESPACE) {
            if ($filter->getField() == OrderInterface::INCREMENT_ID) {
				$modifiedFilterValue = $this->formatString($filter->getValue());
                $modifiedFilterValue = str_replace('%', '', $filter->getValue());
                $modifiedFilterValue = preg_replace('/\s+/', '', $modifiedFilterValue);
                if (strpos($modifiedFilterValue, ",") !== false) {
                    $filter->setValue($modifiedFilterValue);
                    $filter->setConditionType('in');
                } else {
                    $filter->setValue('%' . $modifiedFilterValue . '%');
                    $filter->setConditionType('like');
                }

            }
        }
        return [$collection, $filter];
    }

	public function formatString($input) {
		// Step 1: Replace all occurrences of a comma with a space
		$input = preg_replace('/[,]+/', ' ', $input);

		// Step 2: Remove leading and trailing spaces
		$input = preg_replace('/^ *| *$/', '', $input);

		// Step 3: Squeeze multiple spaces into a single space
		$input = preg_replace('/[ ]+/', ' ', $input);

		// Step 4: Replace spaces with a single comma
		$input = preg_replace('/[ ]+/', ',', $input);

		return $input;
	}
}