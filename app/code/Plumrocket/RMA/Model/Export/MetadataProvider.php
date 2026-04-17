<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Plumrocket\RMA\Model\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Model\Export\MetadataProvider as MetadataProviderParent;
use Magento\Framework\App\ResourceConnection;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MetadataProvider extends MetadataProviderParent
{
    protected $resourceConnection;
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $dateFormat;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param Filter $filter
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param string $dateFormat
     * @param array $data
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Filter $filter,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        $dateFormat = 'M j, Y h:i:s A',
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->filter = $filter;
        $this->localeDate = $localeDate;
        $this->locale = $localeResolver->getLocale();
        $this->dateFormat = $dateFormat;
        $this->data = $data;
    }

    /**
     * Returns Columns component
     *
     * @param UiComponentInterface $component
     * @return UiComponentInterface
     * @throws \Exception
     */
    //protected function getColumnsComponent(UiComponentInterface $component)
	protected function getColumnsComponent(UiComponentInterface $component): UiComponentInterface
    {
        foreach ($component->getChildComponents() as $childComponent) {
            if ($childComponent instanceof Columns) {
                return $childComponent;
            }
        }
        throw new \Exception('No columns found');
    }

    /**
     * Returns columns list
     *
     * @param UiComponentInterface $component
     * @return UiComponentInterface[]
     */
    //protected function getColumns(UiComponentInterface $component)
	protected function getColumns(UiComponentInterface $component): array
    {
        if (!isset($this->columns[$component->getName()])) {
            $columns = $this->getColumnsComponent($component);
            foreach ($columns->getChildComponents() as $column) {
                if ($column->getData('config/label') && $column->getData('config/dataType') !== 'actions') {
                    $this->columns[$component->getName()][$column->getName()] = $column;
                }
            }
        }
        return $this->columns[$component->getName()];
    }

    /**
     * Retrieve Headers row array for Export
     *
     * @param UiComponentInterface $component
     * @return string[]
     */
    //public function getHeaders(UiComponentInterface $component)
	public function getHeaders(UiComponentInterface $component): array
    {
        $row = [];
        foreach ($this->getColumns($component) as $column) {
            $row[] = $column->getData('config/label');
        }

        array_walk($row, function (&$header) {
            if (mb_strpos($header, 'ID') === 0) {
                $header = '"' . $header . '"';
            }
        });

        return $row;
    }

    /**
     * Returns DB fields list
     *
     * @param UiComponentInterface $component
     * @return array
     */
    //public function getFields(UiComponentInterface $component)
	public function getFields(UiComponentInterface $component): array
    {
        $row = [];
        foreach ($this->getColumns($component) as $column) {
            $row[] = $column->getName();
        }
        return $row;
    }

    /**
     * Returns row data
     *
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     * @return array
     */
	 public function getRowDataOptionalMine(DocumentInterface $document, $fields, $options): array
     {
        $row = [];
		foreach ($fields as $column) {
			
            if($column == 'optional') {
				$incrementId = $document->getCustomAttribute('increment_id')->getValue();
				if(is_numeric($incrementId)){
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);
					$orderId = $orderInfo->getId();

					if(!empty($orderId)) {
						$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
						$connection = $resource->getConnection();
						$sales_order_item_table = $resource->getTableName('sales_order_item'); 
						$sales_order_item_sql = "select product_id,name,optional_selected_items from " . $sales_order_item_table." where order_id =".$orderId;
						$sales_order_item_result = $connection->fetchAll($sales_order_item_sql); 
						$optionalItems = array();
						$optItems= array();
						$optItemsval= array();
						$optItemsIds= array();
						$productname = '';
						if(!empty($sales_order_item_result)) {
							foreach ($sales_order_item_result as $key => $sales_order_item_value) {
							   if(!empty($sales_order_item_value['optional_selected_items'])) {
								  $optionalItems = explode(',', $sales_order_item_value['optional_selected_items']);
								}

								$product_id = $sales_order_item_value['product_id'];

								if (!in_array($product_id, $optionalItems)) {
									 $optItems[]=$sales_order_item_value['name'];
								}
							}

							$productname = implode(", ", $optItems);
						}
					}

			    }
				if(isset($productname)) {
					$row[] = $productname;
				} 
		    } else {
			    $row[] = $document->getCustomAttribute($column)->getValue();
			}
        }
        return $row;
	 }


	  /**
     * Returns row data
     *
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     *
     * @return string[]
     */
  
	public function getRowDataMine(DocumentInterface $document, $fields, $options): array
    {
        $row = [];
        foreach ($fields as $column) {
            $inc_id = $document->getCustomAttribute($column)->getValue();
            if(is_numeric($inc_id)) {
				$res_id='';
				$item = 0;
				$order_id = 0;
				$entity_id = 0;
				$product_name = '';
				$resolution_id = '';
				$reason_id = '';
				$connection = $this->resourceConnection->getConnection();
				$plumrocket_rma_returns_table = $connection->getTableName('plumrocket_rma_returns');
				$plumrocket_rma_returns_query = "SELECT `entity_id`,`order_id` FROM `" . $plumrocket_rma_returns_table . "` WHERE entity_id = $inc_id";

				$plumrocket_rma_returns_result = $connection->fetchRow($plumrocket_rma_returns_query);
                //foreach ($plumrocket_rma_returns_result as $val) {
				if($plumrocket_rma_returns_result){
				   $order_id = $plumrocket_rma_returns_result['order_id'];
				   $entity_id = $plumrocket_rma_returns_result['entity_id'];
				}


				$plumrocket_rma_returns_item_table = $connection->getTableName('plumrocket_rma_returns_item');
				$plumrocket_rma_returns_item_query = "SELECT `order_item_id`,`resolution_id`,`reason_id` FROM `" . $plumrocket_rma_returns_item_table . "` WHERE parent_id = $entity_id";
				$plumrocket_rma_returns_item_result = $connection->fetchAll($plumrocket_rma_returns_item_query);
				$orderdata = '';
				$resolution_data = '';
				foreach ($plumrocket_rma_returns_item_result as $key => $value) {
					if($key >0){
					$orderdata .= ',';
					}

					if($key >0){ $resolution_data .= ','; }
					$order_item_id = $value['order_item_id'];
					$resolution_id = $value['resolution_id'];
					$reason_id = $value['reason_id'];
                    $sales_order_item_table = $connection->getTableName('sales_order_item');
					$querySales = "SELECT `name` FROM `" . $sales_order_item_table . "` WHERE item_id = $order_item_id";
					$result = $connection->fetchAll($querySales);
					foreach ($result as $key => $valueName) {
					    $orderdata .= $valueName['name'];
					}

					if($reason_id !='') {
					    $tableReason = $connection->getTableName('plumrocket_rma_reason');
					    $queryReason = "SELECT `title` FROM `" . $tableReason . "` WHERE id = $reason_id";
					    $result = $connection->fetchAll($queryReason);
						foreach ($result as $key => $valueName) {
							$res_id = $valueName['title'];
						}
					}

                    if($resolution_id !=''){
						$tableResolution = $connection->getTableName('plumrocket_rma_resolution');
						$queryReason = "SELECT `title` FROM `" . $tableResolution . "` WHERE id = $resolution_id";
						$result = $connection->fetchAll($queryReason);
						foreach ($result as $reskey => $resvalueName) {
						$resolution_data .= $resvalueName['title'];
						}
					}
				}
            }
			
			if (isset($options[$column])) {
                $key = $document->getCustomAttribute($column)->getValue();
                if (isset($options[$column][$key])) {
                    $row[] = $options[$column][$key];
                } else {
                    $row[] = '';
                }
            } else {
				if($column == 'rma_reason'){
                    $row[] = $res_id;
                } elseif($column == 'returned_items'){
                    $row[] = $orderdata;
                } elseif($column == 'resolution_id'){
                    $row[] = $resolution_data;
                } elseif($column == 'status'){
						$status = $document->getCustomAttribute($column)->getValue();
						if($status == 'authorized'){
						   $row[] = 'Approved';
						} else if($status == 'new'){ 
						   $row[] = 'Pending';
						} else if($status == 'closed'){ 
						   $row[] = 'Cancelled';
						} else if($status == 'processed_closed'){ 
						   $row[] = 'Resolved';
						} else if($status == 'authorized_part'){ 
						   $row[] = 'Partially Approved';
						} else if($status == 'operations'){ 
						   $row[] = 'Pending with Operations';
						} else if($status == 'received'){ 
						   $row[] = 'In Transit';
						} else if($status == 'nostock'){ 
						   $row[] = 'No Stock';
						} else if($status == 'handedover') { 
						   $row[] = 'Handed over to outward';
						}  else if($status == 'notresponding') { 
						   $row[] = 'Customer Not Responding';
						}
                } else {
                        $row[] = $document->getCustomAttribute($column)->getValue();
                }

            }
        }
        return $row;
    }

    /**
     * Returns complex option
     *
     * @param array $list
     * @param string $label
     * @param array $output
     * @return void
     */
    //protected function getComplexLabel($list, $label, &$output)
     protected function getComplexLabel($list, $label, &$output): void
    {
        foreach ($list as $item) {
            if (!is_array($item['value'])) {
                $output[$item['value']] = $label . $item['label'];
            } else {
                $this->getComplexLabel($item['value'], $label . $item['label'], $output);
            }
        }
    }

    /**
     * Returns array of Select options
     *
     * @param Select $filter
     * @return array
     */
    //protected function getFilterOptions(Select $filter): array
	/*protected function getFilterOptions($filter): array
    {
        $options = [];
        foreach ($filter->getData('config/options') as $option) {
            if (!is_array($option['value'])) {
                $options[$option['value']] = $option['label'];
            } else {
                $this->getComplexLabel(
                    $option['value'],
                    $option['label'],
                    $options
                );
            }
        }
        return $options;
    } */

	protected function getFilterOptions(): array
    {
        $options = [];
        $component = $this->filter->getComponent();
        $childComponents = $component->getChildComponents();
        $listingTop = $childComponents['listing_top'];
        foreach ($listingTop->getChildComponents() as $child) {
            if ($child instanceof Filters) {
                foreach ($child->getChildComponents() as $filter) {
                    if ($filter instanceof Select) {
                        $options[$filter->getName()] = $this->getOptionsArray($filter->getData('config/options'));
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Returns Filters with options
     *
     * @return array
     */
    //public function getOptions()
	 public function getOptions(): array
    {
        $options = [];
        $component = $this->filter->getComponent();
        $childComponents = $component->getChildComponents();
        $listingTop = $childComponents['listing_top'];
        foreach ($listingTop->getChildComponents() as $child) {
            if ($child instanceof Filters) {
                foreach ($child->getChildComponents() as $filter) {
                    if ($filter instanceof Select) {
                        $options[$filter->getName()] = $this->getFilterOptions($filter);
                    }
                }
            }
        }
        return $options;
    }

    /**
     * Convert document date(UTC) fields to default scope specified
     *
     * @param \Magento\Framework\Api\Search\DocumentInterface $document
     * @param string $componentName
     * @return void
     */
	 public function convertDate($document, $componentName): void
    //public function convertDate($document, $componentName)
    {
        if (!isset($this->data[$componentName])) {
            return;
        }
        foreach ($this->data[$componentName] as $field) {
            $fieldValue = $document->getData($field);
            if (!$fieldValue) {
                continue;
            }
            $convertedDate = $this->localeDate->date(
                new \DateTime($fieldValue, new \DateTimeZone('UTC')),
                $this->locale,
                true
            );
            $document->setData($field, $convertedDate->format($this->dateFormat));
        }
    }
}
