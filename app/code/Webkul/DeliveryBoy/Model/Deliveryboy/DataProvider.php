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
namespace Webkul\DeliveryBoy\Model\Deliveryboy;

use Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory as DeliveryboyResourceCollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var DeliveryboyResourceCollectionFactory
     */
    protected $collection;

    /**
     * Deliveryboy data object array
     *
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param DeliveryboyResourceCollectionFactory $collection
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        DeliveryboyResourceCollectionFactory $collection,
        \Magento\Framework\Session\SessionManagerInterface $session,
        array $meta = [],
        array $data = []
    ) {
        $this->session = $session;
        $this->collection = $collection->create();
        $this->collection->addFieldToSelect("*");
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get delivery boy data object array
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $result["image"] = $item->getData();
            $this->loadedData[$item->getId()] = $result;
        }
        $data = $this->session->getDeliveryboyFormData();
        if (!empty($data)) {
            $id = isset($data["expressdelivery_deliveryboy"]["id"])
                ? $data["expressdelivery_deliveryboy"]["id"]
                : null;
            $this->loadedData[$id] = $data;
            $this->session->unsImageFormData();
        }
        return $this->loadedData;
    }
}
