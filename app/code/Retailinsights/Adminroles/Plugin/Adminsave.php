<?php

namespace Retailinsights\Adminroles\Plugin;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Api\ExtensionAttributesInterface\Config;

 class Adminsave
{
    protected $postFactory;
    protected $_responseFactory;
    protected $_url;
    protected $userCollectionFactory;
    protected $resultFactory;

    public function __construct(
        \Retailinsights\Adminroles\Model\PostFactory $postFactory,
        ResultFactory $resultFactory,
        Context $context, 
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
         \Magento\Framework\App\Response\RedirectInterface $redirect,
        array $data = []
    ) {
        $this->redirect = $redirect;
        $this->postFactory = $postFactory;
        $this->resultFactory = $resultFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
    }

    public function afterexecute(\Magento\User\Controller\Adminhtml\User\Save $save)
    {
        $post = $save->getRequest()->getPostValue();
		//echo '<pre>';print_r($post);die;
        if(isset($post['user_id'])) {
		    $usercollection = $this->userCollectionFactory->create();
			foreach ($usercollection as $usercollection_value) {
				if($post['user_id']==$usercollection_value['user_id']){
					$user_id = $post['user_id'];
					$schoolfilter = implode(',',$post['schoolfilter']);
					$schoolwise = $post['schoolwise'];
					$pickupstore = $post['pickupstore'];
					$partnername = $post['partnername'];
					$schoolhubname = $post['schoolhubname'];
				} else {}
			}
            $setadminroles = $this->postFactory->create();
			$setadminroles->load($user_id);
			$setadminroles->setSchool($schoolfilter);
			$setadminroles->setSchoolwise($schoolwise);
			$setadminroles->setPickupstore($pickupstore);
			$setadminroles->setDeliveryboyPartnerType($partnername);
			$setadminroles->setSchoolhub($schoolhubname);
			$setadminroles->save();
        }
      
    }
}