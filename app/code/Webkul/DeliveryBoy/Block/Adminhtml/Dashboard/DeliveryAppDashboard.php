<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Block\Adminhtml\Dashboard;

class DeliveryAppDashboard extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var string
     */
    protected $_template = "dashboard/deliveryboyappdashboard.phtml";

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    protected $orderFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\DeliveryBoy\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        $this->resource = $resource;
        $this->scopeConfig = $context->getScopeConfig();
        $this->orderFactory = $orderFactory;
        
        parent::__construct($context, $backendHelper, $data);
    }

    public function deliveryBoyAppTabularData()
    {
        $orders = $this->orderFactory->create()->getCollection()->addFieldToSelect(['deliveryboy_id', 'order_id', 'increment_id', 'order_status']);
        $deliveryboy_table = $this->resource->getTableName('deliveryboy_deliveryboy');
        $cboTable = $this->resource->getTableName('cbo_assign_shippment');
        $orders->getSelect()->join(
            ['deliveryboy_table' => $deliveryboy_table],
            'main_table.deliveryboy_id = deliveryboy_table.id',
            ['name', 'email', 'partner_type']
        )->join(
            ['cboTable' => $cboTable],
            'main_table.order_id = cboTable.order_id',
            ['created_at']
        )->where(
            'cboTable.created_at >= ?',
            (new \DateTime())->modify('-7 days')->format('Y-m-d h:i:s')
        );

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $adminuser = $objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getUsername();
		if($adminuser !='admin') {
         $deliveryboypartner_id = $objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getDeliveryboyPartnerType();
         $orders->getSelect()->where("partner_type = '$deliveryboypartner_id'");
		}

		//echo '<pre>';print_r($orders->getData());die;
        return $orders->getData();
    }

    public function getDeliveryBoysList($deliveryBoyData)
    {
        return array_column($deliveryBoyData, 'name', 'deliveryboy_id');
    }

	public function getDeliveryBoysPartnerList($deliveryBoyData)
    {
        return array_column($deliveryBoyData, 'partner_type');
    }

    public function filteredCount($deliveryBoyData, $deliveryBoyId = null, $orderStatus = null, $dateString = null)
    {
        $html = '';
        if ($dateString) {
            if ($dateString == 'below-12') {
                $date = (new \DateTime())->modify("-12 hours")->format('Y-m-d h:i:s');
                $deliveryBoyData = array_filter($deliveryBoyData, function($value) use($date) { return $value['created_at'] >= $date; });                
            } elseif ($dateString == 'above-12') {
                $date1 = (new \DateTime())->modify("-12 hours")->format('Y-m-d h:i:s');
                $date2 = (new \DateTime())->modify("-24 hours")->format('Y-m-d h:i:s');
                $deliveryBoyData = array_filter($deliveryBoyData, function($value) use($date1, $date2) { return $value['created_at'] < $date1 && $value['created_at'] >=$date2; });
            } elseif ($dateString == 'above-24') {
                $date1 = (new \DateTime())->modify("-24 hours")->format('Y-m-d h:i:s');
                $date2 = (new \DateTime())->modify("-48 hours")->format('Y-m-d h:i:s');
                $deliveryBoyData = array_filter($deliveryBoyData, function($value) use($date1, $date2) { return $value['created_at'] < $date1 && $value['created_at'] >=$date2; });
            } elseif($dateString == 'above-48') {
                $date = (new \DateTime())->modify("-48 hours")->format('Y-m-d h:i:s');
                $deliveryBoyData = array_filter($deliveryBoyData, function($value) use($date) { return $value['created_at'] < $date; });
            }
        }

        if ($deliveryBoyId) {
            $deliveryBoyData = array_filter($deliveryBoyData, fn($value) => $value['deliveryboy_id'] == $deliveryBoyId);
        }

        if ($orderStatus) {
            $deliveryBoyData = array_filter($deliveryBoyData, fn($value) => $value['order_status'] == $orderStatus);   
        }
        if (count($deliveryBoyData)) {
            $html= '<div id="popup-modal-'.$deliveryBoyId.'-'.$orderStatus.'-'.$dateString.'" style="display:none;">';
            foreach ($deliveryBoyData as $value) {
                $html.= "<a href='".$this->getUrl('sales/order/view', ['_current' => true,'_use_rewrite' => true, '_query' => ['order_id' => $value['order_id']]])."' target='_blank'>".$value['increment_id']."</a><br>";
            }
            $html.= '</div><a href="#" class="popup-click" data-popupid="popup-modal-'.$deliveryBoyId.'-'.$orderStatus.'-'.$dateString.'">'.count($deliveryBoyData).'</a>';
        } else {
            $html= '<a href="#">'.count($deliveryBoyData).'</a>';
        }
        return $html;
    }
}