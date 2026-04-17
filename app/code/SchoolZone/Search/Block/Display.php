<?php
namespace SchoolZone\Search\Block;
class Display extends \Magento\Framework\View\Element\Template
{
	protected $_productCollectionFactory;
	protected $postlistFactory;
	protected $postFactory;
	protected $eavConfig;
	protected $schoolConfig;
	protected $_storeManager;
	protected $postlistCollectionFactory;


	public function __construct(
		\SchoolZone\Addschool\Model\ResourceModel\Similarproductsattributes\CollectionFactory $schoolConfig,
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Eav\Model\Config $eavConfig,
		\SchoolZone\Search\Model\PostlistFactory $postlistFactory,
		\SchoolZone\Search\Model\ResourceModel\Postlist\CollectionFactory $postlistCollectionFactory,
		\SchoolZone\Search\Model\PostFactory $postFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory  )
	{
		$this->postlistCollectionFactory = $postlistCollectionFactory;
		$this->_storeManager = $storeManager;
		$this->schoolConfig = $schoolConfig;
		$this->eavConfig = $eavConfig;
		$this->postlistFactory = $postlistFactory;
		$this->postFactory = $postFactory;
		$this->_productCollectionFactory = $productCollectionFactory;
		parent::__construct($context);
	}

	public function getProductCollection($school_name) {
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

	public function getMediaUrl(){
		$mediaUrl = $this->_storeManager
                     ->getStore()
                     ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    		return $mediaUrl;
		
	}
    public function getDescription($school_name,$username,$password,$roll_numbers)
	{
		$collection_listing = $this->postFactory->create()->getCollection();
		
		$collection_listing->addFieldToFilter('school_name_text', $school_name);
		$collection_listing->getSelect()->group('school_name_text');
		return $collection_listing;
	}

	public function isPrebookingEnabled($school_name)
	{
		$collection = $this->schoolConfig->create()
				->addFieldToSelect('*')
				->addFieldToFilter('school_name_text', $school_name);
		
		return $collection->getFirstItem()->getData('enable_prebooking');
	}

	public function isDisplayBookstore($school_name)
	{
		$collection = $this->schoolConfig->create()
				->addFieldToSelect('*')
				->addFieldToFilter('school_name_text', $school_name);
		
		return $collection->getFirstItem()->getData('display_bookstore');
	}

	public function getImage($school_name)
	{
		$collection = $this->schoolConfig->create()
				->addFieldToSelect('*')
				->addFieldToFilter('school_name_text', $school_name);
		
		return $collection->getFirstItem()->getData('school_logo');
	}

	public function getPrebookingDescription($school_name)
	{
		$collection = $this->schoolConfig->create()
				->addFieldToSelect('*')
				->addFieldToFilter('school_name_text', $school_name);
		return $collection->getFirstItem()->getData('prebooking_description');
	}

	public function getClass($schoolName)
	{
		$collection = $this->schoolConfig->create()
				->addFieldToSelect('*')
				->addFieldToFilter('school_name_text', $schoolName);
		
		if($collection->getFirstItem()->getData('school_type') == 1){
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

		// return array with valid data
		$splitProductName = array();
		$newProduct = array();
		foreach($filterProduct as $product){
			// $name = 'AVS Class 2 II Language Hindi & III Language Telugu';
			if($product['type_id'] == 'bundle'){
				$productName = $product['name'];
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$productTest = $objectManager->create('Magento\Catalog\Model\Product')->load($product['entity_id']);

				$attribute = $productTest->getResource()->getAttribute('subject');
				$select_language_type = $productTest->getResource()->getAttribute('select_language_type');

				$splitProductName['label_name'] = $attribute->getFrontend()->getValue($productTest);
				$finalProduct['select_language_type'] = $select_language_type->getFrontend()->getValue($productTest);
				$splitProductName['name'] = $this->getLanguageString($productName);
				$splitProductName['class'] = $product['class_school'];
				$splitProductName['url'] = $product['url_key'];
				$newProduct[] = $splitProductName;
			}
		}

		$productFinal = array();
		$finalProduct = array();
		$prod = array();
		foreach($newProduct as $products){
			if(isset($products['name'])){
				// if($products['name']['code'] == 2 || $products['name']['code'] == 1){
					$prod['options'] = $products['name']['options'];
					$prod['url'] = $products['url'];
					$finalProduct['code'] = $products['name']['code'];
					$finalProduct['class'] = $products['class'];
					$finalProduct['label_name'] =  $attribute->getFrontend()->getValue($productTest);
					$finalProduct['select_language_type'] = $select_language_type->getFrontend()->getValue($productTest);

					$finalProduct['data'][] = $prod;
				// }
				$productFinal[] = $finalProduct;
			}
		}
		return $productFinal;
	}

	public function getSchoolType($schoolName)
	{
		$collection = $this->schoolConfig->create()
				->addFieldToSelect('*')
				->addFieldToFilter('school_name_text', $schoolName);
		return $collection->getFirstItem()->getData('school_type');		
	}

	public function getProductOfTypeTwo($schoolName, $username, $password)
	{
		$collection = $this->postlistCollectionFactory->create();
		$collection->addFieldToSelect('*');
		$collection->addFieldToFilter('school_name_text',$schoolName);
		$collection->addFieldToFilter('username',$username);
		$collection->addFieldToFilter('password',$password);

		$class = '';
		foreach($collection as $prod){
			$class = $prod->getData('class');
		}
		
		// $class = $collection->getFirstItem()->getData('class');

		$attribute = $this->eavConfig->getAttribute('catalog_product', 'class_school');
		$options = $attribute->getSource()->getAllOptions();

		$classid = '';
		$className = '';
		foreach ($options as $value) {
			// if($value['label'] == $classArray){
			if(strtolower($value['label']) == strtolower($class)){ 
				// $classid = $value['label'];
				$className = $value['value'];
				// $classLabel[] = $value['value'];
			}
		}
		$prodCollection = $this->getProductCollection($schoolName);
		$filterProduct=array();

		foreach($prodCollection as $products){
			if($products['type_id'] == 'bundle'){
				foreach ($products as $key => $value) {
					if(isset($value['class_school'])){
						if($value['class_school'] == $className){
							$filterProduct[] = $value;
						}
					}
				}
			}
		}


		// return array with valid data
		$splitProductName = array();
		$newProduct = array();
		foreach($filterProduct as $product){
			// $name = 'AVS Class 2 II Language Hindi & III Language Telugu';
			if($product['type_id'] == 'bundle'){
				$productName = $product['name'];

				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$productTest = $objectManager->create('Magento\Catalog\Model\Product')->load($product['entity_id']);

				$attribute = $productTest->getResource()->getAttribute('subject');

				$splitProductName['label_name'] = $attribute->getFrontend()->getValue($productTest);

				$splitProductName['name'] = $this->getLanguageString($productName);
				$splitProductName['class'] = $product['class_school'];
				$splitProductName['url'] = $product['url_key'];
				$newProduct[] = $splitProductName;
			}
		}
		$productFinal = array();
		$finalProduct = array();
		$prod = array();
		foreach($newProduct as $products){
			if(isset($products['name'])){
				// if(($products['name']['code'] == 3) || ($products['name']['code'] == 2) || ($products['name']['code'] == 1)){
					$prod['options'] = $products['name']['options'];
					$prod['url'] = $products['url'];
					$finalProduct['code'] = $products['name']['code'];
					$finalProduct['class'] = $products['class'];
					$finalProduct['label_name'] =  $attribute->getFrontend()->getValue($productTest);

					$finalProduct['data'][] = $prod;
				// }
				$productFinal[] = $finalProduct;
			}
		}
		return $productFinal;
	}

	public function getProductOfTypeThree($schoolName, $admission_id)
	{
		$collection = $this->postlistCollectionFactory->create();
		$collection->addFieldToSelect('*');
		$collection->addFieldToFilter('school_name_text',$schoolName);
		$collection->addFieldToFilter('admission_id',$admission_id);
		
		$class = $collection->getFirstItem()->getData('class');

		$attribute = $this->eavConfig->getAttribute('catalog_product', 'class_school');
		$options = $attribute->getSource()->getAllOptions();

		$classid = '';
		$className = '';
		foreach ($options as $value) {
			if(strtolower($value['label']) == strtolower($class)){ 
				$classid = $value['label'];
				$className = $value['value'];
			}
		}

		$prodCollection = $this->getProductCollection($schoolName);
		$filterProduct=array();

		foreach($prodCollection as $products){
			if($products['type_id'] == 'bundle'){
				foreach ($products as $key => $value) {
					if(isset($value['class_school'])){
						if($value['class_school'] == $className){
							$filterProduct[] = $value;
						}
					}
				}	
			}
		}
		// return array with valid data
		$splitProductName = array();
		$newProduct = array();
		foreach($filterProduct as $product){
			// $name = 'AVS Class 2 II Language Hindi & III Language Telugu';
			
			
			if($product['type_id'] == 'bundle'){
				$productName = $product['name'];

				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$productTest = $objectManager->create('Magento\Catalog\Model\Product')->load($product['entity_id']);

				$attribute = $productTest->getResource()->getAttribute('subject');

				$splitProductName['label_name'] = $attribute->getFrontend()->getValue($productTest);

				$splitProductName['name'] = $this->getLanguageString($productName);
				$splitProductName['class'] = $product['class_school'];
				$splitProductName['url'] = $product['url_key'];
				$newProduct[] = $splitProductName;
			}
		}
		$productFinal = array();
		$finalProduct = array();
		$prod = array();
		foreach($newProduct as $products){
			if(isset($products['name'])){
				// if(($products['name']['code'] == 3) || ($products['name']['code'] == 2) || ($products['name']['code'] == 1)){
					$prod['options'] = $products['name']['options'];
					$prod['url'] = $products['url'];
					$finalProduct['code'] = $products['name']['code'];
					$finalProduct['class'] = $products['class'];
					$finalProduct['label_name'] =  $attribute->getFrontend()->getValue($productTest);
					
					$finalProduct['data'][] = $prod;
				// }
				$productFinal[] = $finalProduct;
			}
		}
		return $productFinal;
	}

	public function getAdmissionId($school_name,$username,$password)
	{
		$admission_id = '';
		$collection = $this->postlistCollectionFactory->create();
		$collection->addFieldToSelect('*');
		$collection->addFieldToFilter('school_name_text',$school_name);
		$collection->addFieldToFilter('username',$username);
		$collection->addFieldToFilter('password',$password);

		if($collection->getFirstItem()->getData('admission_id')){
			$admission_id = $collection->getFirstItem()->getData('admission_id');
		}
		return $admission_id;
	}

	public function getLanguageString($productName)
	{
		$language = array();
		// $productName = 'AVS Class 2 II Language Hindi & III Language Telugu';
		// $pos = strpos($productName,"II Language");
			// Set the regex.
			$regex = '/(?<=\bLanguage\s)(?:[\w-]+)/is';
	
			// Run the regex with preg_match_all.
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

	public function getClassName($class)
	{
		$attribute = $this->eavConfig->getAttribute('catalog_product', 'class_school');
		$options = $attribute->getSource()->getAllOptions();

		$classid = '';
		$className = '';
		foreach ($options as $value) {
			if(strtolower($value['value']) == strtolower($class)){
				$className = $value['label'];
				$classid = $value['value'];
			}
		}
		return $className;
	}


	
}
