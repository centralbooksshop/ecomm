<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Lof
 * @package   Lof\PincodeChecker
 * @author    Landofcoder <landofcoder@gmail.com>
 * @copyright 2020 Landofcoder
 * @license   https://landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\PincodeChecker\Model\Import;

use Lof\PincodeChecker\Model\Import\Pincode\RowValidatorInterface as ValidatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\App\ResourceConnection;

class Pincode extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{

    const PINCODE = 'pincode';
    const PINCODE_PRICE = 'pincode_price';
    const ENTITY_ID_COLUMN = 'pincode_id';
    const TABLE_Entity = 'lof_pincodechecker';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_PINCODE_IS_EMPTY => 'PINCODE is empty',
    ];

     protected $_permanentAttributes = [self::PINCODE];
    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;
    protected $groupFactory;
    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [
        self::ENTITY_ID_COLUMN,
        self::PINCODE,
        self::PINCODE_PRICE,
    ];

    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

    protected $_validators = [];


    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_connection;
    protected $_resource;

    /**
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Customer\Model\GroupFactory $groupFactory
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->groupFactory = $groupFactory;
    }
    public function getValidColumnNames()
    {
        return $this->validColumnNames;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'lof_pincodechecker';
    }

    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {

        $pincode = false;

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;
        // BEHAVIOR_DELETE use specific validation logic
       // if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (!isset($rowData[self::PINCODE]) || empty($rowData[self::PINCODE])) {
                $this->addRowError(ValidatorInterface::ERROR_PINCODE_IS_EMPTY, $rowNum);
                return false;
            }
            if (!isset($rowData[self::PINCODE_PRICE]) || empty($rowData[self::PINCODE_PRICE])) {
                $this->addRowError(ValidatorInterface::ERROR_PINCODE_PRICE_IS_EMPTY, $rowNum);
                return false;
            }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }


    /**
     * Create Advanced price data from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     */
    protected function _importData()
    {
    if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteEntity();
	} elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
		$this->replaceEntity();
	} elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
		$this->saveEntity();
	}

        return true;
    }
    /**
     * Save newsletter subscriber
     *
     * @return $this
     */
    public function saveEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }
    /**
     * Replace newsletter subscriber
     *
     * @return $this
     */
    public function replaceEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }
    /**
     * Deletes newsletter subscriber data from raw data.
     *
     * @return $this
     */
    public function deleteEntity()
    {
        $listpincode = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowTtile = $rowData[self::PINCODE];
                    $listpincode[] = $rowTtile;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        if ($listpincode) {
            $this->deleteEntityFinish(array_unique($listpincode),self::TABLE_Entity);
        }
        return $this;
    }
 /**
     * Save and replace newsletter subscriber
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_PINCODE_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $rowTtile= $rowData[self::ENTITY_ID_COLUMN];
//                $listpincode[] = $rowTtile;
//                $entityList[$rowTtile][] = [
//                  self::PINCODE => $rowData[self::PINCODE],
//                  self::PINCODE_PRICE => $rowData[self::PINCODE_PRICE],
//                ];
                $rows[] = $rowTtile;
                $columnValues = [];

                foreach ($this->getValidColumnNames() as $columnKey) {
                    $columnValues[$columnKey] = $rowData[$columnKey];
                }
                $entityList[$rowTtile][] = $columnValues;
                $this->countItemsCreated += (int) !isset($rowData[self::ENTITY_ID_COLUMN]);
                $this->countItemsUpdated += (int) isset($rowData[self::ENTITY_ID_COLUMN]);

            }
            //$logger->info('Array Log'.print_r($entityList, true)); // Array Log
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
                if ($rows) {
                    if ($this->deleteEntityFinish(array_unique(  $rows), self::TABLE_Entity)) {
                        $this->saveEntityFinish($entityList, self::TABLE_Entity);
                    }
                }
            } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
                $this->saveEntityFinish($entityList, self::TABLE_Entity);
            }
        }
        return $this;
    }
    /**
     * Save product prices.
     *
     * @param array $priceData
     * @param string $table
     * @return $this
     */
    protected function saveEntityFinish(array $entityData, $table)
    {
        //$logger->info('Array Log'.print_r($entityData, true)); // Array Log
        if ($entityData) {
            $tableName = $this->_connection->getTableName($table);
            $entityIn = [];
            foreach ($entityData as $id => $entityRows) {
                    foreach ($entityRows as $row) {
                        $entityIn[] = $row;
                    }
            }
            if ($entityIn) {
                $this->_connection->insertOnDuplicate($tableName, $entityIn,[
                self::PINCODE,
                self::PINCODE_PRICE
            ]);
            }
        }
        return $this;
    }
    protected function deleteEntityFinish(array $listPINCODE, $table)
    {
        if ($table && $listpincode) {
                try {
                    $this->countItemsDeleted += $this->_connection->delete(
                        $this->_connection->getTableName($table),
                        $this->_connection->quoteInto('pincode IN (?)', $listpincode)
                    );
                    return true;
                } catch (\Exception $e) {
                    return false;
                }

        } else {
            return false;
        }
    }
}
