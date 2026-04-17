<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Ui\DataProvider\Returns;

class ReturnsDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Plumrocket\RMA\Model\ResourceModel\Returns\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter
     */
    private $fulltextFilter;

    /**
     * @var \Plumrocket\RMA\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteria
     */
    protected $searchCriteria;

    /**
     * @var \Magento\Framework\Api\Search\ReportingInterface
     */
    protected $reportingInterface;

	/**
     * @var Magento\Variable\Model\Variable $variable
     */
	protected $variable;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter $fulltextFilter
     * @param \Plumrocket\RMA\Helper\Data $dataHelper
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\Search\ReportingInterface $reportingInterface
	 * @param \Magento\Variable\Model\Variable $variable
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter $fulltextFilter,
        \Plumrocket\RMA\Helper\Data $dataHelper,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\Search\ReportingInterface $reportingInterface,
		\Magento\Variable\Model\Variable $variable,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->dataHelper = $dataHelper;
        $this->fulltextFilter = $fulltextFilter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->reportingInterface = $reportingInterface;
		$this->variable = $variable;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $collection = $this->getCollection();
        $filterMap = $this->getFilterMap();
        $storeIds = $this->dataHelper->getStoreIds();

		$startDate = '';
		$endDate = '';
		
		$rmastartDatevalue = $this->variable->loadByCode('rma-request-startdate', 'admin');
		$startDate = $rmastartDatevalue->getPlainValue();
		$rmaendDatevalue = $this->variable->loadByCode('rma-request-enddate', 'admin');
		$endDate = $rmaendDatevalue->getPlainValue();

		if(empty($endDate)) {
		   $endDate = date("Y-m-d h:i:s");
	    }
		
		//$collection->addFieldToFilter('status', array('nin' => array('new'))); 
	    if(!empty($startDate)) {
		    $collection->addFieldToFilter('main_table.created_at', array('from'=>$startDate, 'to'=>$endDate));
		}

        $collection->addOrderData()
            ->addCustomerData()
            ->addAdminData()
			->addReturnsItemData()
             ->addProductName()
             ->addRmaItems()
             ->addSchoolCode()
            ->addLastReplyData();

        if (false === $this->isArchive()) {
            $collection->addNotArchiveFilter();
        } else {
            $collection->addArchiveFilter();
        }

        foreach ($filterMap as $filter => $alias) {
            $collection->addFilterToMap($filter, $alias);
        }

        if (!empty($storeIds)) {
            $collection->addFieldToFilter('o.store_id', ['in' => $storeIds]);
        }
        return $collection->toArray();
    }

    /**
     * @return bool|false|int
     */
    public function isArchive()
    {
        return mb_strpos((string) $this->request->getServerValue('HTTP_REFERER'), 'returnsarchive');
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     * @return $this
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ('fulltext' === $filter->getConditionType()) {
            $this->fulltextFilter->apply($this->getCollection(), $filter);
        } else {
            $filterMap = $this->getFilterMap();

            if (isset($filterMap[$filter->getField()])) {
                $filter->setField($filterMap[$filter->getField()]);
            }

            parent::addFilter($filter);
        }

        $this->searchCriteriaBuilder->addFilter($filter);

        return $this;
    }

    /**
     * @return array
     */
    private function getFilterMap()
    {
        return [
            'increment_id'       => 'main_table.increment_id',
            'created_at'         => 'main_table.created_at',
            'order_increment_id' => 'o.increment_id',
            'order_date'         => 'o.updated_at',
            'entity_id'          => 'main_table.entity_id',
            'customer_name'      => 'cgf.name',
            'reply_at'           => 'rm.created_at',
            'status'             => 'main_table.status',
			'note'             => 'main_table.note',
            'store_id'           => 'o.store_id',
			'customer_address'   => 'cgf.shipping_full',
			'product_name'       => 'soi.name',
			'rma_reason'         => 'rs.title',
			'school_name'         => 'o.school_name',
            'manager_name'       => new \Zend_Db_Expr('CONCAT(au.`firstname`, " ", au.`lastname`)')
        ];
    }

    /**
     * @return \Magento\Framework\Api\Search\SearchCriteria
     */
    public function getSearchCriteria()
    {
        if (!$this->searchCriteria) {
            $this->searchCriteria = $this->searchCriteriaBuilder->create();
            $this->searchCriteria->setRequestName($this->name);
        }
        return $this->searchCriteria;
    }

    /**
     * Returns Search result
     *
     * @return \Magento\Framework\Api\Search\SearchResultInterface
     */
    public function getSearchResult()
    {
        return $this->reportingInterface->search($this->getSearchCriteria());
    }
}
