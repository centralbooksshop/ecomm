<?php
namespace Magecomp\Cancelorder\Model;
use Magento\Customer\Model\ResourceModel\Group\Collection;
class Customergroups implements \Magento\Framework\Option\ArrayInterface
{
    protected $_options;
	protected $_customerGroup;
	
	public function __construct(Collection $collection)
	{
		$this->_customerGroup = $collection;
	}
	
    public function toOptionArray()
    {
        if (!$this->_options)
		{
            $this->_options = $this->_customerGroup
                ->setRealGroupsFilter()
                ->loadData()->toOptionArray();
            array_unshift($this->_options, ['value'=> '0', 'label'=>__('NOT LOGGED IN')]);
        }
		
        return $this->_options;
    }
}