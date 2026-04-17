<?php
namespace Retailinsights\Orders\Observer;

class UpdateCartQtyItem implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        \SchoolZone\Addschool\Model\SimilarproductsattributesFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->productloader = $_productloader;
        $this->_storeManager = $storeManager;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productNew = $observer->getProduct(); 
        $items = $observer->getCart()->getQuote()->getItems();
        $info = $observer->getInfo()->getData();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        if($this->getWebsiteCode() == 'schools'){
            foreach ($items as $item) {
                $id = $item->getProduct()->getId();
                $currentproduct = $objectManager->create('Magento\Catalog\Model\Product')->load($id);
                $schooolId = $currentproduct->getData('school_name');

                $schoolCollection = $this->collectionFactory->create(); 
                $filter = $schoolCollection->getCollection()
                    ->addFieldToFilter('school_name', $schooolId);
                $schoolFilterData =  $filter->getFirstItem()->getData();
                if($filter->getFirstItem()->getData('id')){
                    if($filter->getFirstItem()->getData('school_type') != 1){
                        if($info[$item->getId()]['qty'] != 1){
                            throw new \Magento\Framework\Exception\LocalizedException(__("only 1 product permitted per class"));
                        }
                    }
                }
           }
        }
    }
    public function getWebsiteCode()
    {
        return $this->_storeManager->getWebsite()->getCode();
    }
}