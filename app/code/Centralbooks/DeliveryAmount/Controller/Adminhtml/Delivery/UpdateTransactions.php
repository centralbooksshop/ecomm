<?php

namespace Centralbooks\DeliveryAmount\Controller\Adminhtml\Delivery;

class UpdateTransactions extends \Magento\Backend\App\Action
{
	protected $deliveryboyTransactions;
    protected $helper;

	public function __construct(
		\Centralbooks\DeliveryAmount\Helper\Data $data,
		\Magento\Backend\App\Action\Context $context,
		\Centralbooks\DeliveryAmount\Model\TransactionsFactory $deliveryboyTransactions
	)
	{
		$this->deliveryboyTransactions = $deliveryboyTransactions;
		$this->helper = $data;
		parent::__construct($context);
	}

	public function execute()
	{
		if($this->helper->erpEnable()) {
			   $resultRedirect = $this->resultRedirectFactory->create();
			   $erp_key = "apiDeliveryBoyPayment";
		           $erp_base_apiUrl = $this->helper->getLedgerApiURL();
               		   $erp_trans_url = $erp_base_apiUrl.$erp_key;
               $access_token = $this->helper->getErpToken(); 
               $headers = array(
                        'Content-Type: application/json',
                        'Authorization: Bearer '. $access_token
               );
               $curl = curl_init($erp_trans_url);
               curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
               curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
               curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
               curl_setopt( $curl, CURLOPT_FAILONERROR, true);
               $response = curl_exec( $curl );
			   $erpResponse = json_decode($response, true);
		//		echo "<pre>"; print_r($erpResponse); echo "</pre>";die;
				if (!empty($erpResponse)) {
					$tData = $erpResponse["value"];
                    foreach($tData as $kdata){
						$existingData = $this->deliveryboyTransactions->create()->load($kdata["no"]);
						$vId =  $kdata["no"];
                        			$vName = $kdata["vendorName"];
                        			$totalRequestedAmount = $kdata["totalRequestedAmount"];
                        			$totalAmountPaid = $kdata["totalAmountPaid"];
                        			$totalRemainingAmount = $kdata["totalRemainingAmount"];
						$lastpaymentdate = $kdata["lastpaymentdate"];
						$lastpaymentdate = substr($lastpaymentdate,0,4);
						$driver_erp_token = $kdata["deliveryBoyID"];

						if($lastpaymentdate != "0001"){
							$lastpaymentdate = $kdata["lastpaymentdate"];
						}else{
							$lastpaymentdate = "";
						}
						if(!empty($existingData->getVid())){		 
							$updateData = $existingData->setVid($vId)
							                           ->setVname($vName)
											    	   ->setTotalRequestedAmount($totalRequestedAmount)
											           ->setTotalAmountPaid($totalAmountPaid)
												   	   ->setTotalRemainingAmount($totalRemainingAmount)
													   ->setCreatedAt($lastpaymentdate)
													   ->setDriverErpToken($driver_erp_token)->save();					
						}else{
							$insertData = $this->deliveryboyTransactions->create()->setVid($vId)
                                                                     ->setVname($vName)
                                                                     ->setTotalRequestedAmount($totalRequestedAmount)
                                                                     ->setTotalAmountPaid($totalAmountPaid)
                                                                     ->setTotalRemainingAmount($totalRemainingAmount)
								     ->setCreatedAt($lastpaymentdate)
								     ->setDriverErpToken($driver_erp_token)->save();
						}
						$message = 'Transaction Updation is Success.';
					}
					$this->messageManager->addSuccessMessage(__($message));
					return $resultRedirect->setPath('*/*/transactions');
				}else{
					 $message = 'Transaction Updation Failed.';
					 $this->messageManager->addErrorMessage(__($message));
                     return $resultRedirect->setPath('*/*/transactions');				
				}
           }
	}
}


