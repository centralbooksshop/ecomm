<?php
namespace SchoolZone\Review\Block;

use SchoolZone\Review\Model\SchooldataFactory;
use Magento\Framework\App\ResourceConnection;

class Display extends \Magento\Framework\View\Element\Template
{
	protected $_productCollectionFactory;
	protected $postlistFactory;
	protected $postFactory;
	protected $eavConfig;
	protected $schoolConfig;
	protected $_storeManager;
	protected $postlistCollectionFactory;
	protected $registry;
	protected $productLinkManagement;
	protected $schooldataFactory;
	protected $resource;

	public function __construct(
		\SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolConfig,
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Eav\Model\Config $eavConfig,
		\SchoolZone\Search\Model\PostlistFactory $postlistFactory,
		\SchoolZone\Search\Model\ResourceModel\Postlist\CollectionFactory $postlistCollectionFactory,
		\SchoolZone\Search\Model\PostFactory $postFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Framework\Registry $registry,
		\Magento\Bundle\Api\ProductLinkManagementInterface $productLinkManagement,
		SchooldataFactory $schooldataFactory,
    	        ResourceConnection $resource,
      	)
	{
		$this->postlistCollectionFactory = $postlistCollectionFactory;
		$this->_storeManager = $storeManager;
		$this->schoolConfig = $schoolConfig;
		$this->eavConfig = $eavConfig;
		$this->postlistFactory = $postlistFactory;
		$this->postFactory = $postFactory;
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->registry = $registry;
		$this->productLinkManagement = $productLinkManagement;
		$this->schooldataFactory = $schooldataFactory;
		$this->resource = $resource;
		parent::__construct($context);
	}


    public function getSchoolData($schoolName)
    {
        $model = $this->schooldataFactory->create();
        $model->load($schoolName, 'school_name');
        if (!$model->getId()) {
            return [];
        }
        return json_decode($model->getData('data_json'), true) ?: [];
     }

     public function getCustomClass($schoolName)
     {
	    $data = $this->getSchoolData($schoolName);
	    $classes = [];
	    foreach ($data as $classKey => $classData) {
	        $classes[] = ['label' => $classData['class']];
	    }
	    return $classes;
     }

     public function getCustomProductsByClass($schoolName, $class)
     {
	    $data = $this->getSchoolData($schoolName);
	    $classLabel = $class['label'];
	    if (!isset($data[$classLabel])) {
	        return [];
	    }
	    $bundles = $data[$classLabel]['bundles'];
	    $result = [];
	    foreach ($bundles as $bundle) {
	        $result[] = [
	            'data' => [
	                [
			    'options' => $bundle['name'],
                            'org_name' => $bundle['org_name'],
	                    'entity_id' => $bundle['entity_id']
	                ]
	              ]
	        ];
	    }
	    return $result;
      }

    public function getCustomBooksByBundleId($bundleId)
    {
	    $data = $this->getSchoolData($this->getRequest()->getParam('schoolname'));
	    foreach ($data as $classData) {
	        foreach ($classData['bundles'] as $bundle) {
	            if ($bundle['entity_id'] === $bundleId) {
	                return $bundle['books'];
	            }
	        }
	    }
	    return [];
    }

    public function getSchoolName()
    {
        return $this->registry->registry('current_school_name');
    }

    public function getProductCollection($school_name) 
    {
            $attribute = $this->eavConfig->getAttribute('catalog_product', 'school_name');
            $options = $attribute->getSource()->getAllOptions();

            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addStoreFilter();

            $school_id = '';
            foreach ($options as $value) {
                if ($value['label'] == $school_name) {
                    $school_id = $value['value'];
                }
            }
	    $collection->addAttributeToFilter('school_name',$school_id);
            return $collection;
     }

    public function getClass($schoolName)
    {
		$collection = $this->schoolConfig->create()
				->addFieldToSelect('*')
				->addFieldToFilter('school_name_text', $schoolName);
		
			$prodCollection = $this->getProductCollection($schoolName);
			$classes=array();
			foreach($prodCollection as $products){
				if($products['type_id'] == 'bundle'){
					foreach ($products as $key => $value) {
						if(isset($value['class_school'])){
							$classes[] = $value['class_school'];
						}
					}
				}
			}
		$attribute = $this->eavConfig->getAttribute('catalog_product', 'class_school');
		$options = $attribute->getSource()->getAllOptions();

		$className = array();
		foreach ($options as $value) {
			if(in_array($value['value'], $classes)){ 
				$className[] = $value;
			}
		}
		return $className;
    }

    public function getProductsByClass($schoolName, $className)
    {
		$prodCollection = $this->getProductCollection($schoolName);
		$filterProduct=array();
		foreach($prodCollection as $products){
			if($products['type_id'] == 'bundle'){
				foreach ($products as $key => $value) {
					if(isset($value['class_school'])){
						if($value['class_school'] == $className['value']){
							$filterProduct[] = $value;
						}
					}
				}
			}
		}

		$splitProductName = array();
		$newProduct = array();
		foreach($filterProduct as $product){
			if($product['type_id'] == 'bundle'){
				$productName = $product['name'];
				$entityId = $product['entity_id'];
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$productTest = $objectManager->create('Magento\Catalog\Model\Product')->load($product['entity_id']);

				$attribute = $productTest->getResource()->getAttribute('subject');
				$select_language_type = $productTest->getResource()->getAttribute('select_language_type');

				$splitProductName['label_name'] = $attribute->getFrontend()->getValue($productTest);
				$finalProduct['select_language_type'] = $select_language_type->getFrontend()->getValue($productTest);
				$splitProductName['name'] = $this->getLanguageString($productName);
				$splitProductName['class'] = $product['class_school'];
				$splitProductName['url'] = $product['url_key'];
				$splitProductName['entity_id'] = $entityId;
				$splitProductName['org_name'] = $productName;
				$splitProductName['sku'] = $productTest->getSku();
		                $splitProductName['isbn'] = $productTest->getIsbn();
				$newProduct[] = $splitProductName;
			}
		}

		$productFinal = array();
		$finalProduct = array();
		$prod = array();
		foreach($newProduct as $products){
			if(isset($products['name'])){
					$prod['options'] = $products['name']['options'];
					$prod['url'] = $products['url'];
					$prod['entity_id'] = $products['entity_id'];
					$prod['org_name'] = $products['org_name'];
					$prod['sku'] = $products['sku'];
				        $prod['isbn'] = $products['isbn'];
					$finalProduct['code'] = $products['name']['code'];
					$finalProduct['class'] = $products['class'];
					$finalProduct['label_name'] =  $attribute->getFrontend()->getValue($productTest);
					$finalProduct['select_language_type'] = $select_language_type->getFrontend()->getValue($productTest);

					$finalProduct['data'][] = $prod;
				$productFinal[] = $finalProduct;
			}
		}
		return $productFinal;
    }

    public function getLanguageString($productName)
    {
		$language = array();
			$regex = '/(?<=\bLanguage\s)(?:[\w-]+)/is';
			preg_match_all($regex, $productName, $matches);
			if(count($matches[0]) == 1){
				// select 2nd language
				$language['code'] = 2;
				$language['options'] = $matches[0][0];
			}elseif(count($matches[0]) == 2){
				// select 2nd & 3rd language
				$language['code'] = 3;
				$language['options'] = $matches[0][0].' & '.$matches[0][1];
			}elseif(count($matches[0]) == 0){
				// (?<=\bclass\s\d{1,2}\s)(?:[\w-]+)
				$regexGrp = '/(?<=\bclass\s[\d{1,2}]\s)(?:[\w-]+)/is';
		
				// Run the regex with preg_match_all.
				preg_match_all($regexGrp, $productName, $matchesNew);
				if(count($matchesNew[0]) == 1){
					// select grp
					$language['code'] = 1;
					$language['options'] = $matchesNew[0][0];
				}else{
					$language['code'] = 0;
					$language['options'] = $productName;
				}

			}
		return $language;
   }	

   public function getBooksByBundleId($bundleId)
   {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($bundleId);
    $books = [];
    $options = $product->getTypeInstance(true)->getOptionsCollection($product);
    $selections = $product->getTypeInstance(true)->getSelectionsCollection($options->getAllIds(), $product);   
    $grouped = [];

    foreach ($options as $option) {
        $optionId = $option->getOptionId();
        $optionTitle = $option->getDefaultTitle();

        foreach ($selections as $selection) {
            if ($selection->getOptionId() == $optionId) {
		    $childProduct = $objectManager->create('Magento\Catalog\Model\Product')->load($selection->getProductId());
		    $attribute = $childProduct->getResource()->getAttribute('publisher');
		    $publisherName = $attribute->getFrontend()->getValue($childProduct);
		    $attribute = $childProduct->getResource()->getAttribute('isbn');
                    $isbnName = $attribute->getFrontend()->getValue($childProduct);

		    if ($childProduct && $childProduct->getId()) {
			$price = $childProduct->getPrice();
			$specialPrice = $childProduct->getSpecialPrice();
			if ($specialPrice !== null && $specialPrice < $price) {
			    $price = $specialPrice;
			}
                    $grouped[$optionTitle][] = [
  		        'name' => $childProduct->getName(),
			'sku' => $childProduct->getSku(),
                        'price' => $price,
                        'qty' => $selection->getSelectionQty(),
                        'total' => $selection->getSelectionQty() * $price,
			'image_url' => $this->getImageUrl($childProduct),
			'publisher_name' => $publisherName,
			'isbn' => $isbnName
                    ];
                }
            }
	}
    }

    return $grouped;
   }

  public function getImageUrl($product)
  {
	  $getNavsionItemNumber = $product->getResource()->getAttribute('navision_item_number');
	  $navNumber = $getNavsionItemNumber->getFrontend()->getValue($product);
	  $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	  $fileUrl = $mediaUrl."school_review/".$navNumber.".jpg";
	  return $fileUrl;
  }
 
  public function getBundleOrderCount($bundleId, $fromDate, $toDate)
  {
    try {
        $connection = $this->resource->getConnection();
        $salesTable = $this->resource->getTableName('sales_order_item');
        $orderTable = $this->resource->getTableName('sales_order');
        // Query to count distinct orders that contain this bundle within date range
        $select = $connection->select()
            ->from(['soi' => $salesTable], ['order_count' => new \Zend_Db_Expr('COUNT(DISTINCT soi.order_id)')])
            ->join(
                ['so' => $orderTable],
                'so.entity_id = soi.order_id',
                []
            )
	    ->where('soi.product_id = ?', $bundleId)
            ->where("so.created_at BETWEEN '{$fromDate}' AND '{$toDate}'")
	    ->where('so.state NOT IN (?)', ['canceled'])
            ->where('so.status = ?', 'order_delivered');
	$result = $connection->fetchOne($select);
        return (int)$result;
    } catch (\Exception $e) {
        return 0; // fallback if error occurs
    }
 }

}
