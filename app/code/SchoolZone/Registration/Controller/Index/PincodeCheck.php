<?php
 
namespace SchoolZone\Registration\Controller\Index;
 
use Magento\Framework\App\Action\Context;
 
class PincodeCheck extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $scopeConfig;
    protected $pincodes;

    public function __construct(
        \SchoolZone\Registration\Model\SimilarproductsattributesFactory $pincodes,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Context $context, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->pincodes = $pincodes;
        $this->scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $shippable = '';
        $pincode = $this->getRequest()->getPost('pincode');
        
        $collection = $this->pincodes->create(); 

        $filter = $collection->getCollection()
                ->addFieldToFilter('postcode', $pincode);

        foreach ($filter as $value) {
         $shippable=$value['is_shippable'];
        }
        if($shippable == 1){
            echo 'yes';   
        }else{
            echo 'no';
        }

    }
}