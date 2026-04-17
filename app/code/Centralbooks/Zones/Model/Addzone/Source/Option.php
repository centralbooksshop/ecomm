<?php
namespace Centralbooks\Zones\Model\Addzone\Source;
use Magento\Framework\Data\OptionSourceInterface;
use Centralbooks\Zones\Model\ResourceModel\Addzone\CollectionFactory;

//class Option implements OptionSourceInterface
class Option implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $zoneType;

    /**
     * @param CollectionFactory $zoneType
     */
    public function __construct(CollectionFactory $zoneType)
    {
        $this->zoneType = $zoneType;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $availableOptions = $this->zoneType->create()->getData();
		
		//echo '<pre>';print_r($availableOptions);
        $options = [];
        foreach ($availableOptions as $item) {
            $options[] = [
                "label" => __($item['zonelabel']),
                "value" => $item['zonevalue'],
            ];
        }
        return $options;
    }
}




