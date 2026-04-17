<?php

namespace Webkul\DeliveryBoy\Model\Deliveryboy\Source;

use Magento\Framework\Data\OptionSourceInterface;
//use \Centralbooks\DeliveryPartner\Model\Partner;
use Centralbooks\DeliveryPartner\Model\ResourceModel\Partner\CollectionFactory;

class Partnertype implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $partner;

    /**
     * @param CollectionFactory $partner
     */
    public function __construct(CollectionFactory $partner)
    {
        $this->partner = $partner;
    }

    /**
     * Get Vehicle type options.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->partner->create()->getData();
	//$availableOptions = $this->partner->getAvailableTypes();
	$options = [];
	$options[] = [
            'value' => '',
            'label' => __('None')
        ];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                "label" => $value['name'],
                "value" => $value['partner_id']
            ];
        }
        return $options;
    }
}
