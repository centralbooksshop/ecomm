<?php

namespace Retailinsights\CourierAvailability\Model\Import;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;

/**
 * Courier Pincode Import
 */
class PincodeList extends AbstractEntity
{
    const ENTITY_CODE = 'courierpincodeimport';
    const TABLE = 'retailinsights_courieravailability_courier';

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * Permanent entity columns.
     *
     * @var array
     */
    protected $_permanentAttributes = [];

    /**
     * Valid column names (must match CSV header)
     *
     * @var array
     */
    protected $validColumnNames = [
        'courier_name',
        'pincode',
        'is_available',
    ];

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Constructor
     */
    public function __construct(
        JsonHelper $jsonHelper,
        ImportHelper $importExportData,
        Data $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->resource = $resource;
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;

        $this->initMessageTemplates();
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return static::ENTITY_CODE;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    public function getValidColumnNames(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Row validation
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum): bool
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $courierName = $rowData['courier_name'] ?? '';
        $pincode = $rowData['pincode'] ?? '';
        $isAvailable = $rowData['is_available'] ?? '';

        if (!$courierName) {
            $this->addRowError('CourierNameIsRequired', $rowNum);
        }

        if (!$pincode) {
            $this->addRowError('PincodeIsRequired', $rowNum);
        }

        if ($pincode && (!is_numeric($pincode) || strlen((string)$pincode) != 6)) {
            $this->addRowError('PincodeIsNotValid', $rowNum);
        }

        if ($isAvailable !== '' && !in_array((int)$isAvailable, [0, 1], true)) {
            $this->addRowError('IsAvailableIsInvalid', $rowNum);
        }

        // Check for duplicates within the same import file
        $rowKey = $courierName . '_' . $pincode;
        if (isset($this->_uniqueRecords[$rowKey])) {
            $this->addRowError('DuplicateRow', $rowNum);
        } else {
            $this->_uniqueRecords[$rowKey] = true;
        }

        $this->_validatedRows[$rowNum] = true;

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Import data
     *
     * @return bool
     * @throws Exception
     */
    protected function _importData(): bool
    {
        // Reset unique records for each import
        $this->_uniqueRecords = [];

        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->deleteEntity();
                break;

            case Import::BEHAVIOR_REPLACE:
                $this->replaceEntity();
                break;

            case Import::BEHAVIOR_APPEND:
                $this->saveAndReplaceEntity();
                break;
        }

        return true;
    }

    /**
     * Init Error Messages
     */
    private function initMessageTemplates()
    {
        $this->addMessageTemplate(
            'CourierNameIsRequired',
            __('Courier Name cannot be empty.')
        );
        $this->addMessageTemplate(
            'PincodeIsRequired',
            __('Pincode cannot be empty.')
        );
        $this->addMessageTemplate(
            'PincodeIsNotValid',
            __('Pincode must be a 6 digit number.')
        );
        $this->addMessageTemplate(
            'IsAvailableIsInvalid',
            __('is_available must be 0 or 1.')
        );
        $this->addMessageTemplate(
            'DuplicateRow',
            __('Duplicate entry for courier_name and pincode combination.')
        );
    }

    /**
     * Delete entities (by courier_name + pincode)
     *
     * @return bool
     */
    private function deleteEntity(): bool
    {
        $rows = [];

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);

                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rows[] = [
                        'courier_name' => $rowData['courier_name'],
                        'pincode' => $rowData['pincode'],
                    ];
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }

        if ($rows) {
            return $this->deleteEntityFinish($rows);
        }

        return false;
    }

    /**
     * Replace entities - clear table and import new data
     */
    private function replaceEntity()
    {
        $rows = [];

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $row) {
                if (!$this->validateRow($row, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $rows[] = [
                    'courier_name' => $row['courier_name'],
                    'pincode' => $row['pincode'],
                    'is_available' => (int)$row['is_available'],
                ];
            }
        }

        if ($rows) {
            // Truncate table first for replace behavior
            $this->connection->truncateTable($this->connection->getTableName(static::TABLE));
            $this->saveEntityFinish($rows);
        }
    }

    /**
     * Save and replace entities (upsert by courier_name + pincode)
     */
    private function saveAndReplaceEntity()
    {
        $rows = [];

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $bunchRows = [];
            
            foreach ($bunch as $rowNum => $row) {
                if (!$this->validateRow($row, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $bunchRows[] = [
                    'courier_name' => $row['courier_name'],
                    'pincode' => $row['pincode'],
                    'is_available' => (int)$row['is_available'],
                ];
            }

            if ($bunchRows) {
                $this->saveEntityFinish($bunchRows);
            }
        }
    }

    /**
     * Save entities
     *
     * @param array $rows
     *
     * @return bool
     */
    private function saveEntityFinish(array $rows): bool
    {
        if (!$rows) {
            return false;
        }

        $tableName = $this->connection->getTableName(static::TABLE);

        try {
            // Use insertOnDuplicate to handle duplicates based on unique constraint
            $this->connection->insertOnDuplicate(
                $tableName,
                $rows,
                ['is_available'] // Fields to update on duplicate
            );
            
            $this->countItemsCreated += count($rows);
            return true;
        } catch (Exception $e) {
            // Log the error and add to error aggregator
            $this->getErrorAggregator()->addError(
                \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::ERROR_LEVEL_CRITICAL,
                __('Database error: %1', $e->getMessage())
            );
            return false;
        }
    }

    /**
     * Delete entities by courier_name + pincode
     *
     * @param array $rows
     *
     * @return bool
     */
    private function deleteEntityFinish(array $rows): bool
    {
        if (!$rows) {
            return false;
        }

        $tableName = $this->connection->getTableName(static::TABLE);

        try {
            foreach ($rows as $row) {
                $where = [
                    'courier_name = ?' => $row['courier_name'],
                    'pincode = ?' => $row['pincode'],
                ];
                
                $affectedRows = $this->connection->delete($tableName, $where);
                if ($affectedRows > 0) {
                    $this->countItemsDeleted++;
                }
            }

            return true;
        } catch (Exception $e) {
            $this->getErrorAggregator()->addError(
                \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::ERROR_LEVEL_CRITICAL,
                __('Database error during deletion: %1', $e->getMessage())
            );
            return false;
        }
    }
}