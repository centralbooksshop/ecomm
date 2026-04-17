<?php
namespace Infomodus\Fedexlabel\Model\Config;

use Infomodus\Fedexlabel\Model\ResourceModel\Boxes\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class Defaultdimensionsset implements OptionSourceInterface
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * Defaultdimensionsset constructor.
     * @param CollectionFactory $collection
     */
    public function __construct(
        CollectionFactory $collection
    )
    {
        $this->collection = $collection;
    }

    public function toOptionArray()
    {

        $collection = $this->collection->create();
        $c = [];
        if ($collection->count() > 0) {
            foreach ($collection as $item) {
                if ($item->getEnable() == 1) {
                    $c[] = ['label' => $item->getName(), 'value' => $item->getId()];
                }
            }
        }

        return $c;
    }

    public function getDimensionSets()
    {
        $collection = $this->collection->create();
        $c = [];
        if ($collection->count() > 0) {
            foreach ($collection as $item) {
                if ($item->getEnable() == 1) {
                    $c[$item->getId()] = $item->getName();
                }
            }
        }

        return $c;
    }

    public function toOptionObjects()
    {
        $collection = $this->collection->create();
        $c = [];
        if ($collection->count() > 0) {
            foreach ($collection as $item) {
                if ($item->getEnable() == 1) {
                    $c[$item->getId()] = $item;
                }
            }
        }

        return $c;
    }
}
