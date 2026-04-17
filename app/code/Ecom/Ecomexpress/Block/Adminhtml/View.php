<?php


namespace Ecom\Ecomexpress\Block\Adminhtml;

class View extends \Magento\Backend\Block\Widget\Form\Container {

	protected $_coreRegistry = null;
	
	public function __construct(\Magento\Backend\Block\Widget\Context $context, \Magento\Framework\Registry $registry, array $data = []) {
		$this->_coreRegistry = $registry;
		parent::__construct ( $context, $data );
	}

	protected function _construct() { //die('--------');
		$this->_objectId = 'shipment_id';
		$this->_mode = 'view';	
		parent::_construct ();	
		$this->buttonList->remove ( 'reset' );
		$this->buttonList->remove ( 'delete' );
		if (! $this->getShipment ()) {
			return;
		}		
		if ($this->_authorization->isAllowed ( 'Magento_Sales::emails' )) {
			$this->buttonList->update ( 'save', 'label', __ ( 'Send Tracking Information' ) );
			$this->buttonList->update ( 'save', 'onclick', "deleteConfirm('" . __ ( 'Are you sure you want to send a Shipment email to customer?' ) . "', '" . $this->getEmailUrl () . "')" );
		}
		if ($this->getShipment ()->getId ()) {	
			$objectmanager = \Magento\Framework\App\ObjectManager::getInstance ();
			$tracks = $objectmanager->create('\Magento\Sales\Model\Order\Shipment\Track')
						->getCollection()
						->addFieldToFilter('parent_id', $this->getShipment()->getId())
						->addFieldToFilter('order_id', $this->getShipment()->getOrder()->getId());
			//print_r($tracks->getData());die('---');
			if(count($tracks->getData())){
				foreach ($tracks as $track){
					$carrierCode = $track['carrier_code'];		
				}
		
				$this->buttonList->add ( 'print', [ 
						'label' => __ ( 'Print' ),
						'class' => 'save',
						'onclick' => 'setLocation(\'' . $this->getPrintUrl () . '\')' 
				] );
				if($carrierCode == "ecomexpress") {
				
					$url = $this->getUrl ( 'ecomexpress/ecomexpress/track_track/', array (
							'shipment_ids' => $this->getShipment ()->getId () 
					));
					$this->buttonList->add ( 'shipping_label', [
							'label' => __ ( 'Shipping Label' ),
							'class' => 'save',
							'onclick' => 'setLocation(\'' . $this->getShippingPrintUrl () . '\')'
					]);
					$this->buttonList->add ( 'track_order', array (
							'label' => __ ( 'Track Order' ),
							'class' => 'save',
	            			 'onclick' => 'popWin("' . $url . '","trackorder","width=800,height=600, resizable=yes,scrollbars=yes")' 
					));
					$this->buttonList->add ( 'cancel_order', array (
							'label' => __ ( 'Cancel Order' ),
							'class' => 'save',
							'onclick' => 'setLocation(\'' . $this->getUrl ( 'ecomexpress/ecomexpress/cancel_cancel/', array (
									'shipment_ids' => $this->getShipment ()->getId () 
							) ) . '\')' 
					));
				}
			}
		}
	}
	
	/**
	 * Retrieve shipment model instance
	 *
	 * @return \Magento\Sales\Model\Order\Shipment
	 */
	public function getShipment() {
		return $this->_coreRegistry->registry ( 'current_shipment' );
	}
	
	/**
	 *
	 * @return \Magento\Framework\Phrase
	 */
	public function getHeaderText() {
		if ($this->getShipment ()->getEmailSent ()) {
			$emailSent = __ ( 'the shipment email was sent' );
		} else {
			$emailSent = __ ( 'the shipment email is not sent' );
		}
		return __ ( 'Shipment #%1 | %3 (%2)', $this->getShipment ()->getIncrementId (), $emailSent, $this->formatDate ( $this->_localeDate->date ( new \DateTime ( $this->getShipment ()->getCreatedAt () ) ), \IntlDateFormatter::MEDIUM, true ) );
	}
	
	/**
	 *
	 * @return string
	 */
	public function getBackUrl() {
		return $this->getUrl ( 'sales/order/view', [ 
				'order_id' => $this->getShipment () ? $this->getShipment ()->getOrderId () : null,
				'active_tab' => 'order_shipments' 
		] );
	}
	
	/**
	 *
	 * @return string
	 */
	public function getEmailUrl() {
		return $this->getUrl ( 'adminhtml/order_shipment/email', [ 
				'shipment_id' => $this->getShipment ()->getId () 
		] );
	}
	
	/**
	 *
	 * @return string
	 */
	public function getPrintUrl() {
		return $this->getUrl ( 'sales/shipment/print', [ 
				'shipment_id' => $this->getShipment ()->getId () 
		] );
	}
	public function getShippingPrintUrl () {
		return $this->getUrl ( 'ecomexpress/ecomexpress/shippinglabel', [
				'shipment_id' => $this->getShipment ()->getId ()
		] );
	}
	
	/**
	 *
	 * @param bool $flag        	
	 * @return $this
	 */
	public function updateBackButtonUrl($flag) {
		if ($flag) {
			if ($this->getShipment ()->getBackUrl ()) {
				return $this->buttonList->update ( 'back', 'onclick', 'setLocation(\'' . $this->getShipment ()->getBackUrl () . '\')' );
			}
			return $this->buttonList->update ( 'back', 'onclick', 'setLocation(\'' . $this->getUrl ( 'sales/shipment/' ) . '\')' );
		}
		return $this;
	}
}
