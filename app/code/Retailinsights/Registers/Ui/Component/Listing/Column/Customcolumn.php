<?php
namespace Retailinsights\Registers\Ui\Component\Listing\Column;
 
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
 
class Customcolumn extends Column
{
    protected $_customerRepository;
    protected $_searchCriteria;
 
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $criteria,
        array $components = [],
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_customerRepository = $customerRepository;
        $this->_searchCriteria  = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $customer  = $this->_customerRepository->getById($item["entity_id"]);
 
                $customer_id = $customer->getId();
 
                $collection = $this->_customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addAttributeToFilter("entity_id", $customer_id);
                $data = $collection->getFirstItem();
                
                $item[$this->getData('name')] = $data->getData('mobile_number');
            }
        }
        return $dataSource;
    }
}