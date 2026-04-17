<?php
namespace Centralbooks\OrderDashboards\Block\Adminhtml\HODashboard;

class OrdersFilter extends \Magento\Backend\Block\Widget\Grid
{
    
	protected function _construct()
	{
		parent::_construct();
		$params = $this->getRequest()->getParams();
		//echo '<pre>';print_r($params);
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$highestTime = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('cbo/payments/highest_orders_time');
		$this->getCollection()->addFieldToFilter('status', array('nin' => array('order_split')));
		
        if(!empty($params)) {
			foreach ($params as $key => $value) {
				if ($key == 'payment_method') {
					$this->getCollection()->getSelect()->join(["sop" => "sales_order_payment"], 'main_table.entity_id = sop.parent_id',["sop.method"])->where('sop.method = ?', $value);
					$date1 = (new \DateTime())->modify($highestTime);
					$this->getCollection()->addFieldToFilter('created_at', ['gteq' => $date1->format('Y-m-d h:i:s')]);
				} elseif($key == 'time') {
					if ($value == 'below-1') {
						$date = (new \DateTime())->modify('-24 hours');
						$this->getCollection()->addFieldToFilter('created_at', ['gteq' => $date->format('Y-m-d h:i:s')]);
					} else if ($value == 'below-2') {
						$date = (new \DateTime())->modify('-48 hours');
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
					$date1 = (new \DateTime())->modify($highestTime);
					$this->getCollection()->addFieldToFilter('created_at', ['gteq' => $date1->format('Y-m-d h:i:s')]);
				}
			}
		} else {
			$date1 = (new \DateTime())->modify($highestTime);
            $this->getCollection()->addFieldToFilter('created_at', ['gteq' => $date1->format('Y-m-d h:i:s')]);
		}
		/*if (!array_key_exists('time', $params)) {
            $date = (new \DateTime())->modify('-7 days');
            $this->getCollection()->addFieldToFilter('created_at', ['gteq' => $date->format('Y-m-d h:i:s')]);
		}*/
		//echo $this->getCollection()->getSelect()->__toString();
		//echo '<pre>';print_r(count($this->getCollection()->getData()));die;
	}
}