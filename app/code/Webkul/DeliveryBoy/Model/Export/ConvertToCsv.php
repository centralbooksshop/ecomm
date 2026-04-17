<?php

namespace Webkul\DeliveryBoy\Model\Export;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConvertToCsv extends \Magento\Ui\Model\Export\ConvertToCsv
{
	protected $scopeConfig;
	const XML_DELIVERYBOY_HEADERS = "deliveryboy/configuration/deliveryboy_order_headers";
	const XML_DELIVERYBOY_VALUES = "deliveryboy/configuration/deliveryboy_order_values";
	const XML_CBO_HEADERS = "deliveryboy/configuration/cbo_order_headers";
	const XML_CBO_VALUES = "deliveryboy/configuration/cbo_order_values";
    /**
     * ConvertToCsv constructor.
     *
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param int $pageSize
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
	MetadataProvider $metadataProvider,
	ScopeConfigInterface $scopeConfig,
        $pageSize = 200
    )
    {
	    parent::__construct($filesystem, $filter, $metadataProvider, $pageSize);
	    $this->scopeConfig = $scopeConfig;
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCsvFile()
    {
	   $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE; 
	   $deliveryboyHeaders = $this->scopeConfig->getValue(self::XML_DELIVERYBOY_HEADERS, $storeScope);
	   $deliveryboyValues = $this->scopeConfig->getValue(self::XML_DELIVERYBOY_VALUES, $storeScope);
	   $cboHeaders = $this->scopeConfig->getValue(self::XML_CBO_HEADERS, $storeScope);
	   $cboValues = $this->scopeConfig->getValue(self::XML_CBO_VALUES, $storeScope);
	   $excludeDOH = explode(',',$deliveryboyHeaders);
	   $excludeDOV = explode(',',$deliveryboyValues);
	   $excludeCOH = explode(',',$cboHeaders);
	   $excludeCOV = explode(',',$cboValues);

        $component = $this->filter->getComponent();
        $name = md5(microtime());
        $file = 'export/' . $component->getName() . $name . '.csv';
        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        if ($component->getName() === 'expressdelivery_order_list') {
            
            $fieldsVal = $this->metadataProvider->getFields($component);
            foreach ($fieldsVal as $key => $val) {
                if (in_array($val, $excludeDOV)) {
                    unset($fieldsVal[$key]);
                }
            }
            $fields = array_values($fieldsVal);
	}else if($component->getName() === 'retailinsights_processcboorders_processedcboorderslist_listing') {
	     $fieldsVal = $this->metadataProvider->getFields($component);
            foreach ($fieldsVal as $key => $val) {
                if (in_array($val, $excludeCOV)) {
                    unset($fieldsVal[$key]);
                }
            }
            $fields = array_values($fieldsVal);
	} else {
            $fields = $this->metadataProvider->getFields($component);
        }
        $options = $this->metadataProvider->getOptions();
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $header = $this->metadataProvider->getHeaders($component);
        if ($component->getName() === 'expressdelivery_order_list') {
            foreach ($header as $key => $val) {
                if (in_array($val, $excludeDOH)) {
                    unset($header[$key]);
                }
            }
            $header = array_values($header);
	}else if ($component->getName() === 'retailinsights_processcboorders_processedcboorderslist_listing'){
		foreach ($header as $key => $val) {
                if (in_array($val, $excludeCOH)) {
                    unset($header[$key]);
                }
            }
            $header = array_values($header);
	}
        $stream->writeCsv($header);
        $i = 1;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setCurrentPage($i)
            ->setPageSize($this->pageSize);
        $totalCount = (int)$dataProvider->getSearchResult()->getTotalCount();
        while ($totalCount > 0) {
            $items = $dataProvider->getSearchResult()->getItems();
            foreach ($items as $item) {
                $this->metadataProvider->convertDate($item, $component->getName());               
                $rowData = $this->metadataProvider->getRowData($item, $fields, $options);
                if ($component->getName() === 'expressdelivery_order_list') {
                    $rowData = array_filter($rowData, function($key) use ($excludeDOH) {
                        return !in_array($key, $excludeDOH);
                    }, ARRAY_FILTER_USE_KEY);
		}else if ($component->getName() === 'retailinsights_processcboorders_processedcboorderslist_listing'){
                    $rowData = array_filter($rowData, function($key) use ($excludeCOH) {
                        return !in_array($key, $excludeCOH);
                    }, ARRAY_FILTER_USE_KEY);	
		}
                $stream->writeCsv($rowData);
            }
	    $searchCriteria->setCurrentPage(++$i);
            $totalCount = $totalCount - $this->pageSize;
        }
        $stream->unlock();
        $stream->close();
        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true 
        ];
    }
}

