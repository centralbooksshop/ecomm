<?php


namespace Ecom\Ecomexpress\Model;

class Automaticawb extends \Magento\Framework\Model\AbstractModel {
	
	public function authenticateAwb($order, $pay_type, $awbno,$postItem) 
	{ 
		$params = array ();
		$type = 'post';
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
		$configvalue = $objectManager->get ( '\Magento\Framework\App\Config\ScopeConfigInterface' );
		$params ['username'] = $configvalue->getValue ( 'carriers/ecomexpress/username' );
		$params ['password'] = $configvalue->getValue ( 'carriers/ecomexpress/password' );
		$params ['json_input'] ['AWB_NUMBER'] = $awbno;
		$params ['json_input'] ['ORDER_NUMBER'] = $order ['increment_id'];
		$params ['json_input'] ['PRODUCT'] = $pay_type;
		//$objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
		$address = $objectManager->get('\Magento\Sales\Model\Order\Address')->load($order['shipping_address_id']);
		$params ['json_input'] ['CONSIGNEE'] = $address ['firstname'];
		$params ['json_input'] ['CONSIGNEE_ADDRESS1'] = $address ['street'];
		$params ['json_input'] ['CONSIGNEE_ADDRESS2'] = $address ['postcode'];
		$params ['json_input'] ['CONSIGNEE_ADDRESS3'] = $address ['city'];
		$params ['json_input'] ['DESTINATION_CITY'] = $address ['city'];
		$params ['json_input'] ['PINCODE'] = $address ['postcode'];
		$params ['json_input'] ['STATE'] = $address ['region'];
		$params ['json_input'] ['MOBILE'] = $address ['telephone'];
		$params ['json_input'] ['TELEPHONE'] = $address ['telephone'];		
		$item = $order->getAllItems ();
		$description = array ();
		$item_weight = 0;
		$total_qty = 0;
		$prodctIds = array();
		foreach ($item as $item_description ) {
			//print_r($item_description->getData());die;
			if($qty = array_search($item_description->getItemId(),array_flip($postItem))){
				$params ['json_input'] ['ITEM_DESCRIPTION'] = $item_description->getName ();
				$item_weight += $item_description['weight'];
				$total_qty += $qty;
				$prodctIds [] = $item_description->getProductId();
			}
		}	
		if(!$item_weight){
			$item_weight = 1;
			//$this->messageManager->addError(__('Product weight must be greater than zero.'));
			//throw new \Exception('Product weight must be greater than zero.',1);
		}	
		$params ['json_input'] ['ACTUAL_WEIGHT'] = $item_weight;
		$params ['json_input'] ['PIECES'] = $total_qty;
		$params ['json_input'] ['COLLECTABLE_VALUE'] = 0;
		if($pay_type!= "PPD")
			$params ['json_input'] ['COLLECTABLE_VALUE'] = $order ['grand_total'];

		$params ['json_input'] ['DECLARED_VALUE'] = $order ['grand_total'];
		//if($params ['json_input'] ['ACTUAL_WEIGHT'] > 1)
			//$params ['json_input'] ['ACTUAL_WEIGHT'] = $params ['json_input'] ['ACTUAL_WEIGHT'] * 1000;
		$params ['json_input'] ['VOLUMETRIC_WEIGHT'] = 0;

		$package_collection = $objectManager->get('Magento\Catalog\Model\Product')->getCollection()->addAttributeToSelect('*')
			 //->addAttributeToFilter('ecom_length', array('notnull' => true))
			 //->addAttributeToFilter('ecom_height',array('notnull' => true))
			 //->addAttributeToFilter('ecom_breadth',array('notnull' => true))
			 ->addAttributeToFilter('sku', array('in' => $prodctIds));
		//print_r($package_collection->getData());die;
		$params['json_input']['LENGTH']  = 0;
		$params['json_input']['BREADTH'] =  0;
		$params['json_input']['HEIGHT'] = 0;
		if(!count($package_collection)>0){
		 	$params['json_input']['LENGTH']  = '10';
		 	$params['json_input']['BREADTH'] =  '10';
		 	$params['json_input']['HEIGHT'] = '10';
		}else{ 
			foreach($package_collection as $packge_dimension){
				//print_r($packge_dimension->getEcomLength());die;
				 $params['json_input']['LENGTH']  += $packge_dimension->getEcomLength();			 
				 $params['json_input']['BREADTH'] +=  $packge_dimension->getEcomBreadth();
				 $params['json_input']['HEIGHT'] += $packge_dimension->getEcomHeight();
			} 
		}
		//print_r($params);die;
		$params ['json_input'] ['PICKUP_NAME'] = $configvalue->getValue ( 'general/store_information/name' );
		$params ['json_input'] ['PICKUP_ADDRESS_LINE1'] = $configvalue->getValue ( 'shipping/origin/street_line1' );
		$params ['json_input'] ['PICKUP_ADDRESS_LINE2'] = $configvalue->getValue('shipping/origin/street_line2') ? $configvalue->getValue('shipping/origin/street_line2') : $configvalue->getValue('shipping/origin/street_line1');
		$params ['json_input'] ['PICKUP_PINCODE'] = $configvalue->getValue ( 'shipping/origin/postcode' );
		$params ['json_input'] ['PICKUP_PHONE'] = $configvalue->getValue ( 'general/store_information/phone' );
		$params ['json_input'] ['PICKUP_MOBILE'] = $configvalue->getValue ( 'general/store_information/phone' );
		$params ['json_input'] ['RETURN_PINCODE'] = $configvalue->getValue('shipping/origin/postcode');
		$params ['json_input'] ['RETURN_NAME'] = $configvalue->getValue('general/store_information/name');
		$params ['json_input'] ['RETURN_ADDRESS_LINE1'] = $configvalue->getValue('shipping/origin/street_line1');
		$params ['json_input'] ['RETURN_ADDRESS_LINE2'] = $configvalue->getValue('shipping/origin/street_line2') ? $configvalue->getValue('shipping/origin/street_line2') : $configvalue->getValue('shipping/origin/street_line1');
		$params ['json_input'] ['RETURN_PHONE'] = $configvalue->getValue ( 'general/store_information/phone' );
		$params ['json_input'] ['RETURN_MOBILE'] = $configvalue->getValue ( 'general/store_information/phone' );
		
		if(!$params['json_input']['PICKUP_NAME'] || !$params['json_input']['PICKUP_PHONE']){ 
			$this->messageManager->addError(__('Kindly fill the General Store Information.'));
			throw new \Exception('Kindly fill the General Store Information.',1);
		}
		if(!$params['json_input']['PICKUP_PINCODE'] || !$params['json_input']['PICKUP_ADDRESS_LINE1']){
			$this->messageManager->addError(__('Fill the shipping setting details first'));
			throw new \Exception('Kindly fill the Sales Shipping Setting.',1);
		}
		$url = 'https://api.ecomexpress.in/apiv3/manifest_awb/';
		if ($configvalue->getValue('carriers/ecomexpress/sanbox'))
			$url = 'https://clbeta.ecomexpress.in/apiv2/manifest_awb/';
		if($params){
			$params ['json_input'] = json_encode ( $params ['json_input'], true );
			$params ['json_input'] = "[ " . $params ['json_input'] . "]";
			
			$helper = $objectManager->get ( 'Ecom\Ecomexpress\Helper\Data' );
			$retValue = $helper->execute_curl ( $url, $type, $params );
			$awb_codes = json_decode( $retValue,'true' );	
			//print_r($awb_codes);die('----');
			if (empty ( $awb_codes )) {
				$this->messageManager->addError(__('Please add valid Username,Password and Count in plugin configuration' ) );
			}
			return $awb_codes;
		}else{
			$this->messageManager->addError(__('Please add valid Username and Password in plugin configuration' ) );
			throw new \Exception('Please add valid Username and Password in plugin configuration',1);
		}
	}
}