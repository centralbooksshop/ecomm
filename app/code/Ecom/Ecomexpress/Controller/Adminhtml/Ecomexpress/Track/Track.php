<?php

namespace Ecom\Ecomexpress\Controller\Adminhtml\Ecomexpress\Track;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Track
 */
class Track extends \Magento\Backend\App\Action {
	
	/**
	 *
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;
	
	/**
	 *
	 * @param \Magento\Framework\App\Action\Context $context        	
	 * @param
	 *        	\Magento\Framework\View\Result\PageFactory resultPageFactory
	 */
	public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory) {
		parent::__construct ( $context );
		$this->resultPageFactory = $resultPageFactory;
	}
	/**
	 * Default track shipment page
	 *
	 * @return void
	 */
	public function execute() {
		$configvalue = $this->_objectManager->get ( '\Magento\Framework\App\Config\ScopeConfigInterface' );
		if($configvalue->getValue('carriers/ecomexpress/active')!="0"){
			$params = array ();
			$shipment_ids = $this->getRequest ()->getParam ( 'shipment_ids' );
			if ($shipment_ids) {
				$model = $this->_objectManager->get ( 'Ecom\Ecomexpress\Model\Awb' );
				$track_awb = $model->getCollection ()->addFieldToFilter ( 'shipment_id', $shipment_ids )->getData ();
				$type = 'post';
				$params ['username'] = $configvalue->getValue ( 'carriers/ecomexpress/username' );
				$params ['password'] = $configvalue->getValue ( 'carriers/ecomexpress/password' );
				$params_info = array ();
				foreach ( $track_awb as $awb ) {
					$params_info ['awb'] [] = $awb ['awb'];
					$params_info ['orderid'] [] = $awb ['orderid'];
				}
				$params ['awb'] = implode ( ",", $params_info ['awb'] );
				$params ['orderid'] = implode ( ",", $params_info ['orderid'] );
				if ($configvalue->getValue ( 'carriers/ecomexpress/sanbox' ) == 1) {
					$url = 'https://clbeta.ecomexpress.in/track_me/api/mawbd/';
				} else {
					$url = 'https://plapi.ecomexpress.in/track_me/api/mawbd/';
				}
				if ($params) {
					$helper = $this->_objectManager->get ( 'Ecom\Ecomexpress\Helper\Data' );
					$retValue = $helper->execute_curl ( $url, $type, $params );
				if (!$retValue){
					$this->messageManager->addError(__('Ecom service is currently Unavilable , please try after sometime'));
					$this->_redirect('ecomexpress/ecomexpress/pincode');
					return;
				} else {
						$xml = simplexml_load_string ( $retValue );
						
						$array = json_decode ( json_encode ( $xml,true ), true );
						//echo '<pre>';print_r($xml);die;
						echo "<h2> Track Order Status</h2>
	                        <table border='5' cellpadding='5' cellspacing='0' style='border-collapse: collapse' bordercolor='#808080' width='100&#37' id='AutoNumber2' bgcolor='#C0C0C0'>
	                                 <tr>
	                                     <th>S.No</th>
	                                     <th>AWB</th>
	                                     <th>OrderId</th>
	                                     <th>Weight</th>
	                                     <th>Destination</th>
	                                     <th>Current Location</th>
	                                     <th>State</th>
	                                     <th>City</th>
	                                     <th>Zip Code</th>
	                                     <th>Shipping Name</th>
	                                     <th>Consignee Name</th>
	                                     <th>Pick up Date</th>
	                                     <th>Status</th>
	                                     <th>Uploaded Date</th>
	                                     <th>Delivery Date</th>
	                                </tr>";
						$i = 1;
						foreach ( $array as $val ) {
							foreach ( $val as $value ) {
								if (is_array ( $value )) {
									$shippingIdsFlag = false;

									if (is_array( $shipment_ids)) {
										if (count ( $shipment_ids ) == 1) {
											$shippingIdsFlag = true;
										}
									}


									if ($shippingIdsFlag) {
										echo "<tr>
											<td>1</td>
											<td>" . $val ['field'] [0] . "</td>
											<td>" . $val ['field'] [1] . "</td>
											<td>" . $val ['field'] [2] . "</td>
											<td>" . $val ['field'] [3] . "</td>
											<td>" . $val ['field'] [4] . "</td>
											<td>" . $val ['field'] [5] . "</td>
											<td>" . $val ['field'] [6] . "</td>
											<td>" . $val ['field'] [25] . "</td>
											<td>" . $val ['field'] [7] . "</td>
											<td>" . $val ['field'] [8] . "</td>
											<td>" . $val ['field'] [9] . "</td>
											<td>" . $val ['field'] [10] . "</td>
											<td>" . $val ['field'] [17] . "</td>
											<td>" . $val ['field'] [16] . "</td>
										  </tr>";
										break;
									} else {
										foreach ( $value as $k ) {
											if (array_key_exists ( "field", $value )) {
												if (count ( $k ) > 2) {
													echo "<tr>
													<td> $i</td>
													<td> $k[0]</td>
													<td> $k[1]</td>
													<td> $k[2]</td>
													<td> $k[3]</td>
													<td> $k[4]</td>
													<td> $k[5]</td>
													<td> $k[6]</td>
													<td> $k[25]</td>
													<td> $k[7]</td>
													<td> $k[8]</td>
													<td> $k[9]</td>
													<td> $k[10]</td>
													<td> $k[17]</td>
													<td> $k[18]</td>";
													$i = $i + 1;
												}
											}
										}
									}
								}
							}
						}
						echo"</tr></table>";
						echo "<br> AWB Tracked Successfully";
					}
				}
			} else {
				echo "AWB is not Tracked";
			}
		}
	}
}