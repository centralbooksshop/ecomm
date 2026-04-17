<?php
/**
 * My own options
 *
 */
namespace Shipsy\EcommerceExtension\Model\Config\Source;
class ServiceTypeOption implements \Magento\Framework\Option\ArrayInterface
{
    protected $logger;
    public function __construct(
        \Shipsy\EcommerceExtension\Helper\Data $dataHelper,
        \Psr\Log\LoggerInterface $logger 
    ) {
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
       
    }
    /**
     * @return array
     */
    public function toOptionArray()
    {
        try {
            $addressArray = $this->dataHelper->getAddresses();
            $serviceTypeArrayOfObjects = $addressArray['data']['serviceTypes'];
            $serviceTypesArray = [];
            
            foreach($serviceTypeArrayOfObjects as $i => $i_value) {
                array_push($serviceTypesArray, ['value' => $i_value['name'], 'label' => __($i_value['name'])]);
            }
            return $serviceTypesArray;
        } catch (\Exception $e) {
            return [];
        }
    }
}

?>

