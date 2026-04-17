<?php
namespace SchoolZone\Search\Controller\Index;

class ResaveProducts extends \Magento\Framework\App\Action\Action
{
	/**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;
	protected $_pageFactory;
	protected $registry;
	public function __construct(
		\Magento\Framework\Registry $registry,
		 \Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		 $this->registry = $registry;
		 $this->productRepository = $productRepository;
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		ini_set('memory_limit','2000M');
		ini_set('max_execution_time', 8000000);

		$this->registry->register('isSecureArea', true);

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
		$productcollection = $productCollectionFactory->create()
                        ->addAttributeToSelect('*')
                        ->load();
		foreach ($productcollection as $product) {
	    if($product->getTypeId() == 'bundle'){
	    
	    	// if($product->getId()== 62148 || $product->getId()== 62419 || $product->getId()==62420 || $product->getId()==62618 || $product->getId()==62421 || $product->getId()==62425 || $product->getId()== 62426 || $product->getId()==62422 || $product->getId()==62423 || $product->getId()==62433 || $product->getId()==62636 || $product->getId()==62620 || $product->getId()==62643 || $product->getId()==62427 || $product->getId()==62431 || $product->getId()==62622  || $product->getId()==62630 || $product->getId()==62628 || $product->getId()==62627){


				// $data = $this->productRepository->getById($product->getId());
				// $data->setClassSchool("Class 1");

				// 	if($this->productRepository->save($data)){
				// 		echo "saved";
				// 		$logger->info("saved :". $product->getId()); 
				// 	}else{
				// 		echo "fail";
				// 		$logger->info("FAIL :". $product->getId()); 
				// 	}
  // $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
  //   $registry = $objectManager->get('\Magento\Framework\Registry');

  //   $registry->register('isSecureArea', true);


							
	    		
	    		if($product->getSku()!="TestClass1Diana"){



							
	    			 	$product = $this->productRepository->getById($product->getId());
				
    					
    					$this->productRepository->delete($product);
						

	    		}



			
			}
		}
		echo "done";
		// return "done";
	}
}