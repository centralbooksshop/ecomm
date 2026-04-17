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
namespace Webkul\DeliveryBoy\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;

class Limit extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    protected $collectionFactory;
	protected $deliveryboy;
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
		\Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
		\Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
		$this->deliveryboy = $deliveryboy;
		$this->collectionFactory = $collectionFactory;
    }

    /**
     * Initialize data source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            //$target = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $fieldName = $this->getData('name');

            foreach ($dataSource["data"]["items"] as &$item) {
                $deliveryboyId = $item["id"];
				if ($deliveryboyId) {
				$deliveryboyOrderColl = $this->collectionFactory->create()
				->addFieldToFilter("deliveryboy_id", $deliveryboyId)
				->addFieldToFilter("order_status", 'dispatched_to_courier');
				$deliverboyordercoll = count($deliveryboyOrderColl);
				$deliveryBoy = $this->deliveryboy->load($deliveryboyId);
				$deliveryboyorder_limit = $deliveryBoy->getData("order_limit");
				if(!empty($deliveryboyorder_limit)) {
				$order_limit_diff = $deliveryboyorder_limit - $deliverboyordercoll; 
				 $item[$fieldName] = $order_limit_diff;
				  }
			    }
            }
        }
        return $dataSource;
    }
}
