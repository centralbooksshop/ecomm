<?php

namespace Infomodus\Fedexlabel\Model\Config;

use Infomodus\Fedexlabel\Model\ResourceModel\Boxes\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class Boxes implements OptionSourceInterface
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * Defaultdimensionsset constructor.
     * @param Collection $collection
     */
    public function __construct(
        CollectionFactory $collection
    )
    {
        $this->collection = $collection;
    }

    public function toOptionArray()
    {
        $storeId = null;
        $c = [['label' => __('--PLEASE SELECT--'), 'value' => '']];
        $collection = $this->collection->create();
        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {
                if ($item->getEnable() == 1) {
                    $c[] = ['label' => $item->getName(), 'value' => round($item->getOuterWidth(), 2) . 'x' . round($item->getOuterHeight(), 2) . 'x' . round($item->getOuterLengths(), 2)];
                }
            }
        }

        return $c;
    }
}