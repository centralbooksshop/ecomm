<?php

namespace Centralbooks\ClickpostExtension\Controller\Adminhtml\Awb;

class UpdateAwbStatus extends \Magento\Backend\App\Action
{
    
	/**
     * Redirect result factory
     * 
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
	 * @var Curl
     */
    protected $resultForwardFactory;
	protected $helper;
	protected $curl;
    /**
     * constructor
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
		\Centralbooks\ClickpostExtension\Model\ResourceModel\Awb\CollectionFactory $collectionFactory,
		\Magento\Ui\Component\MassAction\Filter $filter,
		\Magento\Framework\HTTP\Client\Curl $curl,
		\Centralbooks\ClickpostExtension\Helper\Data $helper
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
		$this->helper = $helper;
		$this->filter = $filter;
		$this->curl = $curl;
		$this->collectionFactory = $collectionFactory;
        parent::__construct($context);
		$this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    /**
     * forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
		$awbArray=array();
		$collection = $this->filter->getCollection($this->collectionFactory->create());
		
		foreach ($collection as $awbModel) {
					$awbArray[]=$awbModel->getId();
				}

	//echo '<pre>';print_r($awbArray);
  $clickpost_username = $this->getScopeConfig('clickpost/clickpostservices/clickpost_username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
  $clickpost_key = $this->getScopeConfig('clickpost/clickpostservices/clickpost_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
  $post_data=$this->getRequest()->getParams();
		if($clickpost_username && $clickpost_key && $awbArray)
		{
			$awbno ="";
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$userModel = $this->_objectManager->create('Centralbooks\ClickpostExtension\Model\Awb');
			$userModel=$userModel->getCollection()->addFieldToFilter('awb_id', array('in' => $awbArray));
								//->addFieldToFilter('status', array('in' => array('Assigned','AwbRegistered')));
			//echo '<pre>';print_r($userModel->getData());die;
			if(count($userModel)){					
				$awbno = '';
				$courier_partner_id = '';
				foreach($userModel as $waybill) {
					if($waybill->getWaybill()){
						if($waybill->getState() == 1)
						{
					   		$awbno = $waybill->getWaybill();
							$courier_partner_id = $waybill->getCourierPartnerId();
						}
					}

             $trackingapiresponse = $this->submitAwbTrackingApi($clickpost_username,$clickpost_key,$awbno,$courier_partner_id);
			  if (array_key_exists('result', $trackingapiresponse) && array_key_exists($awbno, $trackingapiresponse['result'])) 
			  {
				$awbstatus = $trackingapiresponse['result'][$awbno]['latest_status']['clickpost_status_description'];

			   $lmawb = $this->_objectManager->create('Centralbooks\ClickpostExtension\Model\Awb')
										->getCollection()->addFieldToFilter('waybill',$awbno)->getFirstItem();
			    
				if($lmawb->getAwbId())
				{
				   $model = $this->_objectManager->create('Centralbooks\ClickpostExtension\Model\Awb')->load($lmawb->getAwbId());
				   $model->setStatus(preg_replace('/\s+/', '', $awbstatus))->save();
				}

			   } else {
				if (array_key_exists('meta', $trackingapiresponse) && array_key_exists('message', $trackingapiresponse['meta'])) {
                    $errorMessage = $trackingapiresponse['meta']['message'];
                } elseif (array_key_exists('error', $trackingapiresponse)) {
                    $errorMessage = $trackingapiresponse['meta']['message'];
                }

				 $this->messageManager->addErrorMessage('Failed to sync order - '. $errorMessage);
                 $resultRedirect = $this->resultRedirectFactory->create();
                 return $resultRedirect->setRefererOrBaseUrl();
                //throw new \Magento\Framework\Exception\LocalizedException(__($errorMessage));
                 }
                   }

		    $this->messageManager->addSuccessMessage(__(count($collection).' Waybill(s) Updated Successfully'));
			   
			} else
			{
				
					$this->messageManager->addErrorMessage(__('0 AWBs are updated')); 
					$resultRedirect = $this->resultRedirectFactory->create();
					$resultRedirect->setPath('clickpost/awb');
					return $resultRedirect;
			}
		} else
		{
			$this->messageManager->addErrorMessage(__('Please add valid Key and Gateway URL in plugin configuration'));
		}
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setPath('clickpost/awb');
		return $resultRedirect;
    }

	public function submitAwbTrackingApi($clickpost_username,$clickpost_key,$awbno,$courier_partner_id)
	{
       	try {
	          $trackinga_api_url = 'https://api.clickpost.in/api/v2/track-order/?username='.$clickpost_username.'&key='.$clickpost_key.'&waybill='.$awbno.'&cp_id='.$courier_partner_id;
              $this->curl->setOption(CURLOPT_HEADER, 0);
			  $this->curl->setOption(CURLOPT_TIMEOUT, 60);
			  $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
			  $this->curl->get($trackinga_api_url);
			  $result = $this->curl->getBody();
			  $trackingapiresponseres = json_decode($result, true);
			  return $trackingapiresponseres;

             } catch(Exception $e)
			   {
				$this->messageManager->addErrorMessage(__('Something went wrong. please connect to support.'));
				$resultRedirect = $this->resultRedirectFactory->create();
				$resultRedirect->setPath('clickpost/awb');
				return $resultRedirect;
			  }
	    }
	public function getScopeConfig($configPath)
	 { 
	  return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }
}
