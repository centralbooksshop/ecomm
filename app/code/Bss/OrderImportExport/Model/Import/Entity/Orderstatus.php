<?php
namespace Bss\OrderImportExport\Model\Import\Entity;

use Magento\ImportExport\Model\Import;

/**
 * Class Order
 *
 * @package Bss\OrderImportExport\Model\Import\Entity
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Orderstatus extends AbstractEntity
{
    /**
     * Order Table
     *
     * @var string
     */
    protected $mainTable = 'sales_order';

    /**
     * Customer Email Column
     *
     */
    const COLUMN_CUSTOMER_EMAIL = 'customer_email';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'increment_id',
        'status',
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'increment_id',
         'status',
    ];

    /**
     * All columns has base prefix on database
     *
     * @var array
     */
    protected $baseFields = [
        
    ];

    /**
     * All columns has incl_tax suffix on database
     *
     * @var array
     */
    protected $inclTaxFields = [
        'subtotal',
        'base_subtotal'
    ];

    /**
     * Custom Csv Column For Entity
     *
     * @var array
     */
    protected $customColumns = [
        'status_label'
    ];

    /**
     * List columns which has multiple value
     *
     * @var array
     */
    protected $multipleValueColumns = [
        'applied_rule_ids'
    ];

    /**
     * Validation Error Code
     *
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'orderEntityIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateOrderEntityId';
    const ERROR_ENTITY_ID_IS_NOT_EXIST = 'orderEntityIdIsNotExist';
	const ERROR_STATUS_IS_NOT_EXIST = 'orderStatusIsNotExist';
    const ERROR_INCREMENT_ID_IS_EXIST = 'orderIncrementIdIsExist';
    const ERROR_INCREMENT_ID_IS_NOT_EXIST = 'orderIncrementIdIsNotExist';
    const ERROR_STORE_ID_IS_NOT_EXIST = 'orderStoreIdIsNotExist';
    const ERROR_STATE_IS_NOT_EXIST = 'orderStateIsNotExist';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Order entity_id is duplicated in the import file',
        self::ERROR_DUPLICATE_INCREMENT_ID => 'Order increment_id is duplicated in the import file',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Order entity_id is empty',
        self::ERROR_INCREMENT_ID_IS_EMPTY => 'Order increment_id is empty',
        self::ERROR_ENTITY_ID_IS_NOT_EXIST => 'Order entity_id is not exist',
		self::ERROR_STATUS_IS_NOT_EXIST => 'Order status is not exist',
		
        self::ERROR_INCREMENT_ID_IS_EXIST => 'Order increment_id is exist',
        self::ERROR_INCREMENT_ID_IS_NOT_EXIST => 'Order increment_id is not exist',
        self::ERROR_STORE_ID_IS_NOT_EXIST => 'Order store_id is not exist',
        self::ERROR_STATE_IS_NOT_EXIST => 'Order state code is not exist',
    ];

    /**
     * Order ids of a bunch
     *
     * @var array
     */
    protected $bunchOrderIds = [];

    /**
     * Retrieve Data For Each Entity
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Zend_Db_Statement_Exception
     */
    public function extractRowData(array $rowData, $rowNumber = 0)
    {
        $rowData = parent::extractRowData($rowData);
        $rowData = $this->extractFields($rowData, $this->prefixCode);

        if ($this->getMultipleValueColumns()) {
            $multiSeparator = $this->getMultipleValueSeparator();
            foreach ($this->getMultipleValueColumns() as $multiValueCol) {
                if (!empty($rowData[$multiValueCol])) {
                    $values = [];
                    foreach (explode($multiSeparator, $rowData[$multiValueCol]) as $subValue) {
                        $values[] = $subValue;
                    }
                    $rowData[$multiValueCol] = implode(',', $values);
                }
            }
        }

        if (!empty($rowData['status'])) {
            $rowData['status'] = strtolower($rowData['status']);
        }

        return (count($rowData) && !$this->isEmptyRow($rowData)) ? $rowData : false;
    }

    /**
     * Retrieve Extracted Field
     *
     * @param array $rowData
     * @param string $prefix
     * @return array|bool
     */
    protected function extractFields($rowData, $prefix)
    {
        $data = [];
        foreach ($rowData as $field => $value) {
            if ($field == "tax:percent") {
                $rowData['tax_percent'] = $value;
            }

            if ($prefix && strpos($field, ':') !== false) {
                list($fieldPrefix, $field) = explode(':', $field);
                if ($fieldPrefix == $prefix) {
                    $data[$field] = $value;
                }
            } elseif (!$this->prefixCode && strpos($field, ':') === false) {
                $data[$field] = $value;
            }
        }
        return $data;
    }

    /**
     * Retrieve Entity Id By Increment ID on Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function checkExistIncrementId(array $rowData)
    {
        $orderIdsMapped = $this->getOrderIdsMapped();
        if (!empty($orderIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]])) {
            return $orderIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]];
        }
        return false;
    }

    protected function checkExistStatus(array $rowData)
    {
        if (empty($rowData['status'])) {
            return false;
        } 

		if (!empty($rowData['status'])) {
            return $rowData['status'];
        }

       
    }

    protected function checkExistEntityId(array $rowData)
    {
        if (empty($rowData[static::COLUMN_ENTITY_ID])) {
            return false;
        }

        $orderIdsMapped = $this->getOrderIdsMappedByEntityId();
        if (!empty($orderIdsMapped[$rowData[static::COLUMN_ENTITY_ID]])) {
            return $orderIdsMapped[$rowData[static::COLUMN_ENTITY_ID]];
        }
        return false;
    }

    /**
     * Delete entities for replacement.
     *
     * @return $this
     */
    public function deleteForReplacement()
    {
        $this->setParameters(
            array_merge(
                $this->getParameters(),
                ['behavior' => Import::BEHAVIOR_DELETE]
            )
        );
        $this->deleteAction();

        $this->setOrderIdsMapped([]);

        return $this;
    }

    /**
     * Delete List Of Entities
     *
     * @param array $idsToDelete Entities Id List
     * @return $this
     */
    protected function deleteEntities(array $idsToDelete)
    {
        parent::deleteEntities($idsToDelete);
        $this->removeTax($idsToDelete);
        $this->removeDownloadLink($idsToDelete);
        $this->removeGrid($idsToDelete);
        $this->removeShipmentGrid($idsToDelete);
        $this->removeInvoiceGrid($idsToDelete);
        $this->removeCreditmemoGrid($idsToDelete);
        return $this;
    }

    /**
     * Remove order tax
     *
     * @param $orderIdsToDelete
     */
    protected function removeTax($orderIdsToDelete)
    {
        $this->connection->delete(
            $this->resource->getTableName('sales_order_tax'),
            $this->connection->quoteInto(
                'order_id IN (?)',
                $orderIdsToDelete
            )
        );
    }

    /**
     * Remove order download link purchased
     *
     * @param $orderIdsToDelete
     */
    protected function removeDownloadLink($orderIdsToDelete)
    {
        $this->connection->delete(
            $this->resource->getTableName('downloadable_link_purchased'),
            $this->connection->quoteInto(
                'order_id IN (?)',
                $orderIdsToDelete
            )
        );
    }

    /**
     * Remove deleted entities on grid table
     *
     * @param $idsToDelete
     */
    protected function removeGrid($idsToDelete)
    {
        $this->connection->delete(
            $this->resource->getTableName('sales_order_grid'),
            $this->connection->quoteInto(
                self::COLUMN_ENTITY_ID . ' IN (?)',
                $idsToDelete
            )
        );
    }

    /**
     * Remove deleted entities on Shipment grid table
     *
     * @param $orderIdsToDelete
     */
    protected function removeShipmentGrid($orderIdsToDelete)
    {
        $this->connection->delete(
            $this->resource->getTableName('sales_shipment_grid'),
            $this->connection->quoteInto(
                'order_id IN (?)',
                $orderIdsToDelete
            )
        );
    }

    /**
     * Remove deleted entities on Invoice grid table
     *
     * @param $orderIdsToDelete
     */
    protected function removeInvoiceGrid($orderIdsToDelete)
    {
        $this->connection->delete(
            $this->resource->getTableName('sales_invoice_grid'),
            $this->connection->quoteInto(
                'order_id IN (?)',
                $orderIdsToDelete
            )
        );
    }

    /**
     * Remove deleted entities on Creditmemo grid table
     *
     * @param $orderIdsToDelete
     */
    protected function removeCreditmemoGrid($orderIdsToDelete)
    {
        $this->connection->delete(
            $this->resource->getTableName('sales_creditmemo_grid'),
            $this->connection->quoteInto(
                'order_id IN (?)',
                $orderIdsToDelete
            )
        );
    }



    /**
     * Update Entities
     *
     * @return $this
     * @throws \Zend_Db_Statement_Exception
     */
    protected function updateAction()
    {
        if ($bunch = $this->getCurrentBunch()) {
            $entitiesToUpdate = [];
            $orderStatusToUpdate = [];
            $orderStatusLabelToUpdate = [];

             //echo '<pre>';print_r($bunch);
			  foreach ($bunch as $rowNumber => $rowData) {
                $rowData = $this->extractRowData($rowData);

                // validate entity data
               /* if (!$rowData || !$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }*/

				
		    //echo '<pre>';print_r($rowData);
			$incrementId = $rowData['increment_id'];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		    $orderInfo = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);
            $orderId = $orderInfo->getId();
			$order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
            $state = $order->getState();
            $status = $rowData['status'];
            $comment = 'Order Status is '. $status;
            $isNotified = false;
            $order->setState($state);
            $order->setStatus($status);
            $order->addStatusToHistory($order->getStatus(), $comment);
            $order->save();


				//$rowData['status']

                //$processedData = $this->prepareDataToUpdate($rowData, $rowNumber);
				  

               /* if (!$processedData) {
                    continue;
                }

                // phpcs:disable Magento2.Performance.ForeachArrayMerge
                $entitiesToUpdate = array_merge($entitiesToUpdate, $processedData[self::ENTITIES_TO_UPDATE_KEY]);
                $orderStatusToUpdate = array_merge(
                    $orderStatusToUpdate,
                    $processedData[self::ORDER_STATUS_TO_UPDATE_KEY]
                );
                $orderStatusLabelToUpdate = array_merge(
                    $orderStatusLabelToUpdate,
                    $processedData[self::ORDER_STATUS_LABEL_TO_UPDATE_KEY]
                );*/
            }

            /*if ($entitiesToUpdate) {
                $this->updateEntities($entitiesToUpdate);
                $this->updateOrderStatus($orderStatusToUpdate);
            }*/
        }
		//die;

        return $this;
    }

    /**
     * Update Status For Order
     *
     * @param $statusData
     * @return $this
     */
    protected function updateOrderStatus($statusData)
    {
        if ($statusData) {
            $this->connection->insertOnDuplicate(
                $this->getOrderStatusTable(),
                $statusData,
                ['status', 'label']
            );
        }
        return $this;
    }

  

    /**
     * Prepare Data To Add Entities
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function prepareDataToAdd(array $rowData, $rowNumber)
    {
        $entitiesToCreate = [];
        $orderStatusToUpdate = [];
        $orderStatusLabelToUpdate = [];

        // entity table data
        $now = new \DateTime();
        if (empty($rowData['created_at'])) {
            $createdAt = $now;
        } else {
            $createdAt = (new \DateTime())->setTimestamp(strtotime($rowData['created_at']));
        }

        if (empty($rowData['updated_at'])) {
            $updateAt = $now;
        } else {
            $updateAt = (new \DateTime())->setTimestamp(strtotime($rowData['updated_at']));
        }

        $customerId = null;
        $customerGroupId = 0;

        if ($this->checkExistIncrementId($rowData)) {
            $this->addRowError(static::ERROR_INCREMENT_ID_IS_EXIST, $rowNumber);
            return false;
        }

        $entityId = $this->getNextEntityId();
        $this->newEntities[$rowData[self::COLUMN_INCREMENT_ID]] = $entityId;
        $this->mapOrderId($rowData[self::COLUMN_INCREMENT_ID], $entityId);

        if (!empty($rowData[self::COLUMN_CUSTOMER_EMAIL])) {
            $customerId = $this->getCustomerId(
                $rowData[self::COLUMN_CUSTOMER_EMAIL],
                $rowData[self::COLUMN_STORE_ID] ?: 0
            ) ?: null;
            if (!empty($rowData['customer_group_id'])) {
                $customerGroupId = $rowData['customer_group_id'];
            }
        }

        if (empty($this->getExistStatus()[$rowData['status']])) {
            $orderStatusToUpdate[] = [
                'status' => $rowData['status'],
                'label' => $rowData['status_label']
            ];
        } else {
            $orderStatusLabelToUpdate[] = [
                'status' => $rowData['status'],
                'store_id' => $rowData['store_id'],
                'label' => $rowData['status_label']
            ];
        }

        $rowData = $this->convertToInclTaxFields($rowData);
        $rowData = $this->convertToBaseFields($rowData);

        $this->bunchOrderIds[] = $entityId;

        $isVirtual = empty($rowData['is_virtual']) ? 0 : 1;
        $entityRowData = [
            'is_virtual' => $isVirtual,
            'customer_id' => $customerId,
            'customer_group_id' => $customerGroupId,
            'customer_is_guest' => $customerId ? 0 : 1,
            self::COLUMN_ENTITY_ID => $entityId,
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => $updateAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToCreate[] = $entityRowData;
        return [
            self::ENTITIES_TO_CREATE_KEY => $entitiesToCreate,
            self::ORDER_STATUS_TO_UPDATE_KEY => $orderStatusToUpdate,
            self::ORDER_STATUS_LABEL_TO_UPDATE_KEY => $orderStatusLabelToUpdate
        ];
    }

    /**
     * @return array
     */
    public function getBunchOrderIds()
    {
        return $this->bunchOrderIds;
    }

    /**
     * @return void
     */
    public function resetBunchOrderIds()
    {
        $this->bunchOrderIds = [];
    }

    /**
     * Prepare Data To Update Entities
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Zend_Db_Statement_Exception
     */
    protected function prepareDataToUpdate(array $rowData, $rowNumber)
    {
        $entitiesToUpdate = [];
        $orderStatusToUpdate = [];
        $orderStatusLabelToUpdate = [];

        // entity table data
        $now = new \DateTime();
        if (empty($rowData['created_at'])) {
            $createdAt = $now;
        } else {
            $createdAt = (new \DateTime())->setTimestamp(strtotime($rowData['created_at']));
        }

        $customerId = null;
        $customerGroupId = 0;

       

        if ($this->checkExistIncrementIdOnOtherOne($rowData)) {
            $this->addRowError(__("The order increment_id is exist on other one"), $rowNumber);
            return false;
        }

        $this->newEntities[$rowData[self::COLUMN_INCREMENT_ID]] = $entityId;
        $this->mapOrderId($rowData[self::COLUMN_INCREMENT_ID], $entityId);

        /*if (!empty($rowData[self::COLUMN_CUSTOMER_EMAIL])) {
            $customerId = $this->getCustomerId(
                $rowData[self::COLUMN_CUSTOMER_EMAIL],
                $rowData[self::COLUMN_STORE_ID] ?: 0
            ) ?: null;
            if ($customerId) {
                $customerGroupId = $this->getCustomerGroupId($rowData[self::COLUMN_CUSTOMER_EMAIL]);
            }
        }*/

        if (empty($this->getExistStatus()[$rowData['status']])) {
            $orderStatusToUpdate[] = [
                'status' => $rowData['status'],
                'label' => $rowData['status_label']
            ];
        } else {
            $orderStatusLabelToUpdate[] = [
                'status' => $rowData['status'],
                //'store_id' => $rowData['store_id'],
               // 'label' => $rowData['status_label']
            ];
        }

        //$rowData = $this->convertToInclTaxFields($rowData);
       //$rowData = $this->convertToBaseFields($rowData);

        $this->bunchOrderIds[] = $entityId;

        //$isVirtual = empty($rowData['is_virtual']) ? 0 : 1;
        $entityRowData = [
            //'is_virtual' => $isVirtual,
            //'customer_id' => $customerId,
            //'customer_group_id' => $customerGroupId,
            //'customer_is_guest' => $customerId ? 0 : 1,
            self::COLUMN_ENTITY_ID => $entityId,
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToUpdate[] = $entityRowData;
        return [
            self::ENTITIES_TO_UPDATE_KEY => $entitiesToUpdate,
            self::ORDER_STATUS_TO_UPDATE_KEY => $orderStatusToUpdate,
            self::ORDER_STATUS_LABEL_TO_UPDATE_KEY => $orderStatusLabelToUpdate
        ];
    }

    /**
     * Add order id to map array
     *
     * @param $incrementId
     * @param $entityId
     */
    public function mapOrderId($incrementId, $entityId)
    {
        $this->orderIdsMapped[$incrementId] = $entityId;
    }

    /**
     * Add base rate to map array
     *
     * @param $incrementId
     * @param $baseRate
     */
    public function mapBaseRate($incrementId, $baseRate)
    {
        $this->baseRatesMapped[$incrementId] = $baseRate;
    }

    /**
     * Add tax rate to map array
     *
     * @param $incrementId
     * @param $taxRate
     */
    public function mapTaxRate($incrementId, $taxRate)
    {
        $this->taxRatesMapped[$incrementId] = $taxRate;
    }

    /**
     * Validate Row Data For Delete Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function validateRowForDelete(array $rowData, $rowNumber)
    {
        if ($this->validateEntityId($rowData, $rowNumber)) {
            if (!$this->checkExistEntityId($rowData)) {
                $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
            }
        }
    }

    /**
     * Validate Row Data For Update Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function validateRowForUpdate(array $rowData, $rowNumber)
    {
        if ($this->validateIncrementId($rowData, $rowNumber)) {
            $incrementId = $rowData[self::COLUMN_INCREMENT_ID];
            if (isset($this->newEntities[$incrementId])) {
                $this->addRowError(self::ERROR_DUPLICATE_INCREMENT_ID, $rowNumber);
            } else {
                $this->newEntities[$incrementId] = true;
            }

            if (!empty($rowData[self::COLUMN_STORE_ID]) &&
                empty($this->getExistStores()[$rowData[self::COLUMN_STORE_ID]])
            ) {
                $this->addRowError(static::ERROR_STORE_ID_IS_NOT_EXIST, $rowNumber);
            }

            if (!empty($rowData[self::COLUMN_STATE]) &&
                !in_array($rowData[self::COLUMN_STATE], $this->getExistStates())
            ) {
                $this->addRowError(static::ERROR_STATE_IS_NOT_EXIST, $rowNumber);
            }

            if (!$this->checkExistStatus($rowData)) {
                $this->addRowError(static::ERROR_STATUS_IS_NOT_EXIST, $rowNumber);
            }

            foreach ($this->requiredValueColumns as $column) {
                if (isset($rowData[$column]) && '' == $rowData[$column]) {
                    $this->addRowError(static::ERROR_COLUMN_IS_EMPTY, $rowNumber, $column);
                }
            }
        }
    }

    /**
     * Validate Row Data For Add Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function validateRowForAdd(array $rowData, $rowNumber)
    {
        
    }

  
}
