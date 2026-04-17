<?php 
namespace Retailinsights\Orders\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
 
class locationCode extends Column
{
 
    protected $_orderRepository;
    protected $_searchCriteria;
    protected $_customfactory;
    private $schoolsCollection;
 
    public function __construct(
       \SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolsCollection,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\OrderFactory $orderFactory,
		 array $components = [], array $data = [])
    {
        $this->schoolsCollection = $schoolsCollection;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->resource = $resource;
        $this->orderFactory = $orderFactory;
	     parent::__construct($context, $uiComponentFactory, $components, $data);
    }

      public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as &$item) {
                $name = $this->getData("name");
                $schoolsCollection = $this->schoolsCollection->create();
                $schoolsCollection->getSelect();
                $schoolsCollection->addFieldToFilter('school_name_text', $item['school_name']);
                $schoolcoll = $schoolsCollection->getFirstItem();
                $schoollocationcode = $schoolcoll->getLocationCode();
                if(!empty($schoollocationcode)) {

					$item[$name] = $schoollocationcode;
                } 
                
            }
        }
        return $dataSource;
    }
    
}
