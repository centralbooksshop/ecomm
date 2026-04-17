<?php
/**
 *
 * @category  Webkul
 * @package   Webkul_Quotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\DeliveryBoy\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class InsertDefaultData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\VehicleType\CollectionFactory $vehicleTypeCollectionF
     * @param \Webkul\DeliveryBoy\Model\VehicleTypeFactory $vehicleTypeF
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Webkul\DeliveryBoy\Model\ResourceModel\VehicleType\CollectionFactory $vehicleTypeCollectionF,
        \Webkul\DeliveryBoy\Model\VehicleTypeFactory $vehicleTypeF,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->vehicleTypeCollectionF = $vehicleTypeCollectionF;
        $this->vehicleTypeF = $vehicleTypeF;
        $this->logger = $logger;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $vehicleTypeCollection = $this->vehicleTypeCollectionF->create();
        if ($vehicleTypeCollection->getSize() == 0) {
            $this->moduleDataSetup->getConnection()->startSetup();
            $connection = $this->moduleDataSetup->getConnection();
            $vehicleType = $this->vehicleTypeF->create();
            try {
                $bike = [
                    'value' => 'bike',
                    'label' => __("Bike"),
                ];
                $cycle = [
                    'value' => 'cycle',
                    'label' => __("Cycle"),
                ];

                $connection->insert($this->moduleDataSetup->getTable('deliveryboy_vehicle_type'), $bike);
                $connection->insert($this->moduleDataSetup->getTable('deliveryboy_vehicle_type'), $cycle);
                $this->moduleDataSetup->getConnection()->endSetup();
            } catch (\Exception $e) {
                $this->logger->info('Save Vehicle Type Data Error '.$e->getMessage());
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
        ];
    }
}
