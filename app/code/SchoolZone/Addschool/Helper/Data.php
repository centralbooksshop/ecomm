<?php
namespace SchoolZone\Addschool\Helper;
 
use SchoolZone\Addschool\Model\SimilarproductsattributesFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes;


class Data extends AbstractHelper
{
    protected $customModelFactory;
    protected $connection;
    protected $_checkoutSession;
    protected $_storeManager;
    protected $schoolsCollection;

    public function __construct(
        Context $context,
        SimilarproductsattributesFactory $customModelFactory,
        Similarproductsattributes $resourceConnection,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolsCollection
    )     {
        $this->schoolsCollection = $schoolsCollection;
        $this->customModelFactory = $customModelFactory;
        $this->connection = $resourceConnection->getConnection('default');
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }
    public function getCollection()
    {
        return $this->customModelFactory->create()->getCollection();
    }

    public function isProductFromSameSchool($newProductId)
    {
        $flag = true;
        // // Get product from Cart
        if($this->getWebsiteCode() == 'schools'){
            $cartData = $this->_checkoutSession->getQuote()->getAllVisibleItems();
            foreach ($cartData as $key => $value) {
                $product = $value->getProduct();
                $productId[]=$product->getId();
            }

            if($cartData){
                try{
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $productFactory = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
                    $products = $productFactory->create()                              
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter('entity_id',['eq'=>$productId[0]])
                            ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched
                            
                }catch(\Exception $e){
                    $this->logger->error($e->getMessage());
                }
                $data = $products->getFirstItem();
                $catData = $data->getData();
                // product school id from cart
                $schoolId = $catData['school_name'];
                

                // New product school id
                try{
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $productFactory = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
                    $products = $productFactory->create()                              
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter('entity_id',['eq'=>$newProductId])
                            ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched
                            
                }catch(\Exception $e){
                    $this->logger->error($e->getMessage());
                }
                $newProduct = $products->getFirstItem();
                $newProductData = $newProduct->getData();

                // product school id from cart
                $newSchoolId = $newProductData['school_name'];

                if($schoolId != $newSchoolId)
                {
                    $flag = false;   
                }
            }
        }
        return $flag;
    }

	public function isItemFromSameSchool($newProductId)
    {
        $flag = false;
        if($this->getWebsiteCode() == 'schools') {
			$quote_product_id = [];
            $cartData = $this->_checkoutSession->getQuote()->getAllVisibleItems();
            foreach ($cartData as $key => $value) {
				$quote_product_id[] = $value->getProductId();
            }
      
			if (in_array($newProductId, $quote_product_id))
			{
			    $flag = true;  
			}
        }
        return $flag;
    }

    public function getWebsiteCode()
    {
        return $this->_storeManager->getWebsite()->getCode();
    }

    public function isPreviewModeOn($newProductId)
    {
        if($this->getWebsiteCode() == 'schools'){
            try{
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $productFactory = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
                $products = $productFactory->create()                              
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('entity_id',['eq'=>$newProductId])
                        ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched     
            }catch(\Exception $e){
                $this->logger->error($e->getMessage());
            }
			$enable_school_preview = $products->getFirstItem()->getData('enable_school_preview');
            /*$school_name = $products->getFirstItem()->getData('school_name');
            $collection = $this->schoolsCollection->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('school_name', $school_name);
            return $collection->getFirstItem()->getData('enable_preview');*/
			return $enable_school_preview;
        }
        return '0';
    }

    public function getPreviewMessage($newProductId)
    {
        if($this->getWebsiteCode() == 'schools'){
            try{
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $productFactory = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
                $products = $productFactory->create()                              
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('entity_id',['eq'=>$newProductId])
                        ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched
                        
            }catch(\Exception $e){
                $this->logger->error($e->getMessage());
            }
            $school_name = $products->getFirstItem()->getData('school_name');

            $collection = $this->schoolsCollection->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('school_name', $school_name);
            return $collection->getFirstItem()->getData('preview_description');
        }
        return '0';
    }
}