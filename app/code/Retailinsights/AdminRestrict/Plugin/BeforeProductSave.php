<?php
namespace Retailinsights\AdminRestrict\Plugin;

class BeforeProductSave {
   
	protected $_catalogSession;
    protected $_customerSession;
    protected $_checkoutSession;
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
		 \Magento\Catalog\Api\ProductRepositoryInterface $product_repo,
        array $data = []
    )
    {        
        $this->catalogSession = $catalogSession;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
		$this->product_repo = $product_repo;
    }

    public function beforeSave($subject,$object){
        $sku = $object->getData('sku');
		$dublicated = $this->catalogSession->getMyValue();
         if(!isset($dublicated)) {
        //if(!isset($_SESSION['dublicated'])) {
          $oldsku = explode("-1",$sku);
        if(count($oldsku) > 1){
            $product = $this->product_repo->get($oldsku[0]);
            if($product->getId()){
                throw new \Magento\Framework\Exception\LocalizedException(__("Cross check the SKU this DUPLICATED product"));   
                return;
            }
        }
        $price = $object->getData('price');
        $type = $object->getData('type_id');

        if(($type == 'simple') && ($price > 100000)){
            throw new \Magento\Framework\Exception\LocalizedException(__("Maximum price Rs.100000/- only."));   
                return;
        }

        }

    }
}