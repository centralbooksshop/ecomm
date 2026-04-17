<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_VehicleType
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Model\VehicleType;

use Webkul\DeliveryBoy\Model\ResourceModel\VehicleType\CollectionFactory as VehicleTypeResourceCollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var VehicleTypeResourceCollectionFactory
     */
    protected $collection;

    /**
     * VehicleType data object array
     *
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param VehicleTypeResourceCollectionFactory $collection
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        VehicleTypeResourceCollectionFactory $collection,
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
            $this->loadedData[$item->getId()]['expressdelivery_vehicletype'] =
                $item->getData();
        }
        $data = $this->session->getVehicleTypeFormData();
        $data = $data["expressdelivery_vehicletype"] ?? null;
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()]['expressdelivery_vehicletype'] = $model->getData();
            $this->session->unsVehicleTypeFormData();
        }
        return $this->loadedData;
    }
}
