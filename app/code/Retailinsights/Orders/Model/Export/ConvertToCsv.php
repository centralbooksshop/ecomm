<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Retailinsights\Orders\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Ui\Model\Export\ConvertToCsv as ConvertToCsvParent;
/**
 * Class ConvertToCsv
 */
class ConvertToCsv extends ConvertToCsvParent
{
    /**
     * @var DirectoryList
     */
    protected $directory;
    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;
    /**
     * @var int|null
     */
    protected $pageSize = null;
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var Product
     */
    private $productHelper;
    /**
     * @var TimezoneInterface
     */
    private $timezone;
	protected $deliveryboy;
    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param int $pageSize
     * @throws FileSystemException
     */
    public function __construct(
        \Retailinsights\ProcessCBOOrders\Model\ResourceModel\ProcessCBOOrders\CollectionFactory $driverCollectionFactory,
        \Retailinsights\Autodrivers\Model\ResourceModel\Listautodrivers\CollectionFactory $autoDriverCollection,
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        TimezoneInterface $timezone,
		\Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
        $pageSize = 200
    ) {
        $this->autoDriverCollection = $autoDriverCollection;
        $this->driverCollectionFactory = $driverCollectionFactory;
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->pageSize = $pageSize;
        parent::__construct($filesystem, $filter, $metadataProvider, $pageSize);
        $this->timezone = $timezone;
		$this->deliveryboy = $deliveryboy;
    }
    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     * @throws \Exception
     */
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();
        $name = md5(microtime());
        $file = 'export/' . $component->getName() . $name . '.csv';
        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        $i = 1;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setCurrentPage($i)
            ->setPageSize($this->pageSize);
       $totalCount = (int)$dataProvider->getSearchResult()->getTotalCount();
        while ($totalCount > 0)
        {
            $items = $dataProvider->getSearchResult()->getItems();
            foreach ($items as $item) {
				$this->metadataProvider->convertDate($item, $component->getName());
				//echo $component->getName();die;
                if($component->getName()=='sales_order_grid') {
                    $trackingColumn = $this->getTrackingNumber($item);
                    //$dispatchedOn = $this->getDispatchedOnDate($item);
                    $item->setData('trackingColumn', $trackingColumn);
                    //$item->setData('dispatched_on', $dispatchedOn);
					$stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
                } elseif($component->getName() == 'retailinsights_salesordergrid_list'){
				    $trackingColumn = $this->getTrackingNumber($item);
                    $dispatchedOn = $this->getDispatchedOnDate($item);
                    $item->setData('trackingColumn', $trackingColumn);
                    $item->setData('dispatched_on', $dispatchedOn);
					$stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
			    } elseif($component->getName() == 'prrma_returns_listing'){
                    $stream->writeCsv($this->metadataProvider->getRowDataMine($item, $fields, $options));
				} elseif($component->getName() == 'retailinsights_optionalbooksreports_list'){
                    $stream->writeCsv($this->metadataProvider->getRowDataOptionalMine($item, $fields, $options));
                } elseif ($component->getName() == 'retailinsights_willbegiven_list') {
					$value = $item->getData('given_options'); // raw value (1/2)
					$labels = [
						1 => 'Will be Given',
			            2 => 'School Given',
					];

					$label = $labels[$value] ?? $value; // fallback if unknown
					$item->setData('given_options', $label);

					$stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));

				}else{
                    $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
                }
            }
            $searchCriteria->setCurrentPage(++$i);
            $totalCount = $totalCount - $this->pageSize;
        }
        $stream->unlock();
        $stream->close();
        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }

    public function getTrackingNumber($item)
    {
        $trackingColumn = '';
        $drivers = $this->driverCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', $item->getEntityId());
        if($drivers){
            if($drivers->getFirstItem()){
                $driverId = $drivers->getFirstItem()->getData('driver_id');
                $deliveryboyId = $drivers->getFirstItem()->getData('deliveryboy_id');
                if(!empty($driverId)){
                    $autodrivers = $this->autoDriverCollection->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('id', $driverId);
					if($autodrivers->getFirstItem()->getData()){
                        $info = $autodrivers->getFirstItem();
                        $trackingColumn = $info->getData('driver_name')." : ".
                        $info->getData('driver_mobile')."  Auto:".
                        $info->getData('auto_number');
                      }
                }elseif(!empty($drivers->getFirstItem()->getData('tracking_title'))){

                    if($drivers->getFirstItem()->getData('tracking_title') == 'Clickpost'){
                        $tracking_number = $drivers->getFirstItem()->getData('tracking_number');
						$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
						$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
						$connection = $resource->getConnection();
						$salesOrder = $resource->getTableName('sales_order');
						$salesOrdersql = "SELECT clickpost_courier_name FROM ".$salesOrder." WHERE shipsy_reference_numbers = "."'$tracking_number'";
						$queryResult = $connection->fetchRow($salesOrdersql);
						
                       $courier_name = $queryResult['clickpost_courier_name'];
					   $trackingColumn = $courier_name." : ".$tracking_number;
					} else {
                        $name = $drivers->getFirstItem()->getData('tracking_title');
						$number = $drivers->getFirstItem()->getData('tracking_number');
						$trackingColumn = $name." : ".$number;
					}
                } elseif(!empty($deliveryboyId)) {
                    $deliveryboy = $this->deliveryboy->load($deliveryboyId);
					$deliveryboyName = $deliveryboy->getName();
					$deliveryboyContact = $deliveryboy->getMobileNumber();
					$deliveryvehicleNumber = $deliveryboy->getVehicleNumber();
					$trackingColumn =$deliveryboyName." : ".
					$deliveryboyContact." Auto:".
					$deliveryvehicleNumber;
				 }
            }
        }
        return $trackingColumn;
    }

	private function mapGivenOptions($value)
	{
		$options = [
			1 => 'Will be Given',
			2 => 'School Given',
			// add more if needed
		];

		return $options[$value] ?? $value;
	}


    public function getDispatchedOnDate($item)
    {
        $dispatchedOn = '';
        $drivers = $this->driverCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', $item->getEntityId());
        if($drivers){
            $dispatchedOn = $drivers->getFirstItem()->getData('created_at');
           
        }
        return $dispatchedOn;
    }
}