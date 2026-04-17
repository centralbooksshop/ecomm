<?php
namespace Centralbooks\OrderSchoolDashboards\Block\Adminhtml\HODashboard;

class OrdersFilter extends \Magento\Backend\Block\Widget\Grid
{
    
	protected function _construct()
	{
		parent::_construct();
		$params = $this->getRequest()->getParams();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $highestTime = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('schooldashboard/ordertime/highest_orders_time');
        foreach ($params as $key => $value) {
			if ($key == 'payment_method') {
				$this->getCollection()->getSelect()->join(["sop" => "sales_order_payment"], 'main_table.entity_id = sop.parent_id',["sop.method"])->where('sop.method = ?', $value);
			} elseif($key == 'time') {
		        if ($value == 'below-3') {
		            $date = (new \DateTime())->modify('-72 hours');
		            $this->getCollection()->addFieldToFilter('created_at', ['gteq' => $date->format('Y-m-d h:i:s')]);
		        } elseif ($value == 'above-3') {
		            //$date1 = (new \DateTime())->modify('-72 hours');
					$date1 = (new \DateTime())->modify($highestTime);
		            $date2 = (new \DateTime())->modify('-72 hours');
		            $this->getCollection()->addFieldToFilter('created_at', ['gt' => $date1->format('Y-m-d h:i:s')]);
		            $this->getCollection()->addFieldToFilter('created_at', ['lteq' => $date2->format('Y-m-d h:i:s')]);
		        }
			} else {
				$this->getCollection()->addFieldToFilter($key, $value);
			}
		}
		if (!array_key_exists('time', $params)) {
            //$date = (new \DateTime())->modify('-7 days');
			$date = (new \DateTime())->modify($highestTime);
            $this->getCollection()->addFieldToFilter('created_at', ['gteq' => $date->format('Y-m-d h:i:s')]);
		}
	}
}