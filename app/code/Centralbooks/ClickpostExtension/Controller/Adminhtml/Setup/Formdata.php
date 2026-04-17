<?php

namespace Centralbooks\ClickpostExtension\Controller\Adminhtml\Setup;

class Formdata extends \Magento\Framework\App\Action\Action
{
    protected $_messageManager;
    protected $urlInterface;
    protected $_cookieManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Centralbooks\ClickpostExtension\Helper\Data $dataHelper
    ) {
        parent::__construct($context);
        $this->_messageManager = $messageManager;
        $this->urlInterface = $urlInterface;
        $this->_cookieManager = $cookieManager;
        $this->scopeConfig = $scopeConfig;
        $this->dataHelper = $dataHelper;
    }
    
    public function execute()
    {
       
        try {
            $postRequest = $this->getRequest()->getPostValue();
            if (isset($postRequest['useForwardCheck']) && $postRequest['useForwardCheck'] === 'true') {
                $useForwardAddress = true;
                $reverseAddress =  [
                    'name' => $postRequest['forward-name'],
					'email' => $postRequest['forward-email'],
					'tin' => $postRequest['forward-tin'],
                    'phone' => $postRequest['forward-phone'],
                    'alternate_phone' => (!empty($postRequest['forward-alt-phone'])) ? $postRequest['forward-alt-phone'] : $postRequest['forward-phone'],
                    'address_line_1' => $postRequest['forward-line-1'],
                    'address_line_2' => $postRequest['forward-line-2'],
                    'pincode' => $postRequest['forward-pincode'],
                    'city' => $postRequest['forward-city'],
                    'state' => $postRequest['forward-state'],
                    'country' => $postRequest['forward-country']
                ];
            } else {
                $useForwardAddress = false;
                $reverseAddress = [
                    'name' => $postRequest['reverse-name'],
                    'phone' => $postRequest['reverse-phone'],
                    'alternate_phone' => (!empty($postRequest['reverse-alt-phone'])) ? $postRequest['reverse-alt-phone'] : $postRequest['reverse-phone'],
                    'address_line_1' => $postRequest['reverse-line-1'],
                    'address_line_2' => $postRequest['reverse-line-2'],
                    'pincode' => $postRequest['reverse-pincode'],
                    'city' => $postRequest['reverse-city'],
                    'state' => $postRequest['reverse-state'],
                    'country' => $postRequest['reverse-country']
                ];
            }
            $dataToSendArray = [
                'forwardAddress' => [
                    'name' => $postRequest['forward-name'],
				     'email' => $postRequest['forward-email'],
					'tin' => $postRequest['forward-tin'],
                    'phone' => $postRequest['forward-phone'],
                    'alternate_phone' => (!empty($postRequest['forward-alt-phone'])) ? $postRequest['forward-alt-phone'] : $postRequest['forward-phone'],
                    'address_line_1' => $postRequest['forward-line-1'],
                    'address_line_2' => $postRequest['forward-line-2'],
                    'pincode' => $postRequest['forward-pincode'],
                    'city' => $postRequest['forward-city'],
                    'state' => $postRequest['forward-state'],
                    'country' => $postRequest['forward-country']
                ],
                'reverseAddress' => $reverseAddress,
                
                'useForwardAddress' => $useForwardAddress,
                
            ];

            //$dataToSendJson = json_encode($dataToSendArray);
            $name = $dataToSendArray['forwardAddress']["name"];
			$email = $dataToSendArray['forwardAddress']["email"];
			$tin = $dataToSendArray['forwardAddress']["tin"];
			$phone = $dataToSendArray['forwardAddress']["phone"];
			$alternate_phone = $dataToSendArray['forwardAddress']["alternate_phone"];
			$address_line_1 = $dataToSendArray['forwardAddress']["address_line_1"];
			$address_line_2 = $dataToSendArray['forwardAddress']["address_line_2"];
			$pincode = $dataToSendArray['forwardAddress']["pincode"];
			$city = $dataToSendArray['forwardAddress']["city"];
			$state = $dataToSendArray['forwardAddress']["state"];
			$country = $dataToSendArray['forwardAddress']["country"];

			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $clickpost_pickup_address = $resource->getTableName('clickpost_pickup_address');

		     $addresssql = "SELECT id FROM " . $clickpost_pickup_address . " WHERE forward_state = "."'$state'";
		     $addressres = $connection->fetchRow($addresssql);
		     if(!empty($addressres['id']))
			 {
			   $address_id = $addressres['id'];
			   $sqlq = "UPDATE ".$clickpost_pickup_address." SET forward_name='".$name."',forward_email='".$email."',
			forward_phone='".$phone."',forward_alt_phone='".$alternate_phone."',forward_line_1='".$address_line_1."',forward_line_2='".$address_line_2."',forward_city='".$city."',forward_state='".$state."',forward_country='".$country."',forward_pincode='".$pincode."',forward_tin='".$tin."'  WHERE id="."'$address_id'";
              $uquery = $connection->query($sqlq);
			 }
			//echo '<pre>';print_r($dataToSendArray['forwardAddress']);die;
           
               $this->_messageManager->addSuccessMessage('data successfully updated');
			   $resultRedirect = $this->resultRedirectFactory->create();
			   return $resultRedirect->setRefererOrBaseUrl();
              
        } catch (\Exception $e) {
            $this->_messageManager->addErrorMessage('Error' . $e->getMessage());
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setRefererOrBaseUrl();
    }
}
