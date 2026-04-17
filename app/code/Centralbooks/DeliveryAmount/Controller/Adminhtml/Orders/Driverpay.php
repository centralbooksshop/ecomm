<?php

namespace Centralbooks\DeliveryAmount\Controller\Adminhtml\Orders;

use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface;
use Webkul\DeliveryBoy\Controller\RegistryConstants;

class Driverpay extends \Webkul\DeliveryBoy\Controller\Adminhtml\Deliveryboy
{

   public function execute()
   {  
     $deliveryboyId = $this->initCurrentDeliveryboy();
     $deliveryboy = $this->deliveryboyDataFactory->create()->load($deliveryboyId);
     $deliveryboyName = $deliveryboy->getName();
     $resultRedirect = $this->resultRedirectFactory->create();
     $driverType = $deliveryboy->getDriverType();
     $driverPartnerType = $deliveryboy->getPartnerType();
     $currentDateTime = $this->date->date();
     //$formattedDate = $currentDateTime->format('Y-m-d H:i:s');	

     if($driverType == "Parent" && $driverPartnerType == 2){
	     $data = $this->apiDataCustom->getTotalAmount($deliveryboyId);
	     $totalCovers = $this->apiDataCustom->getNoOfCovers($deliveryboyId);
	     $totalBoxes = $this->apiDataCustom->getNoOfBoxes($deliveryboyId);
	     $totalOrders = ($totalCovers + $totalBoxes);
     	     $totalAmount = $data[0];
	     $incrementIds = $data[1];
	     $driverName = $deliveryboy->getName();
     }else{
	     $parentData = $this->apiDataCustom->getParentAmount($driverPartnerType); 
	     $childIds = $parentData[2];
	     $parentID = $parentData[1]; 
	     foreach($childIds as $childId){
		     $childIncrementId = $this->apiDataCustom->getTotalAmount($childId);
		     $incrementIds = array_merge($incrementIds ?? [], $childIncrementId[1]);
	     }

	     $totalAmount = $parentData[0];
	     $totalBoxes = $parentData[3];
	     $totalCovers = $parentData[4];
	     $totalOrders = $parentData[5];
	     $driverName = $deliveryboy->getName();
     }
     if($this->apiDataCustom->erpEnable()) {
	     $erpToken = $deliveryboy->getDriverErpId();
	     if(!empty($erpToken)){

				//$headers = ['Total No Of Boxes', 'Total No Of Covers', 'Total Orders', 'Total Amount'];
				$headers = ['AUTO/BIKER BILL'];
				$csvRows = [ ["Driver Name : ".$driverName],["Total No Of Boxes : ".$totalBoxes], ["Total No Of Covers : ".$totalCovers], ["Total Orders : ".$totalOrders] , ["Total Amount : ".$totalAmount], ["CreatedAT : ".$currentDateTime]];
				$filename = 'indent.csv'; 
				//$directory = '/var/www/magento24-cbs/var/'.$filename;
				$varDirectory = $this->getVarDirectoryPath();
				$directory =$varDirectory."/".$filename;
				$base64Encode = $this-> writeIndent($headers, $csvRows, $directory);
				$amountData = array( "vendorID" => $erpToken, "vendorInvoiceAmount" => $totalAmount, "attachmentFile" => $base64Encode);
                                $amountData = json_encode($amountData);
			//	print_r("json encode".$amountData);
			//	echo "base64 data".$pdfBase64;
			//	print_r("64 decode". base64_decode($pdfBase64));

				$erp_key = "apiCreateExpenseIndent";
			   	$indentUrl = $this->apiDataCustom->getLedgerApiURL();
			    	$indentUrl = $indentUrl.$erp_key;
			    	$access_token = $this->apiDataCustom->getErpToken();
				$headers = array(
				   'Content-Type: application/json',
				   'Authorization: Bearer '. $access_token
				);
				$curl = curl_init($indentUrl);
                		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt( $curl, CURLOPT_FAILONERROR, true);
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $amountData);
				$response = curl_exec( $curl );
			//	print_r($response); die;
				if(!empty($response)){
					$response = json_decode($response,true);
					$indentNo = $response['no'];
					if(!empty($indentNo)){
					foreach($incrementIds as $incrementId){
						$setPamentStatus = $this->deliveryboyOrderFactory->create()->load($incrementId)->setPaymentStatus("Paid")->save();
					}
					if(!empty($totalAmount)){
				   		 $this->messageManager->addSuccessMessage(__('Delivery Boy '.$deliveryboyName.' Payment is Succesfull.'));	
            			 		return $resultRedirect->setPath('expressdelivery/deliveryboy');
					}
				  }
				}
		     }else{
			     $this->messageManager->addSuccessMessage(__('User '.$deliveryboyName.' Not Found In ERP.'));
			     return $resultRedirect->setPath('expressdelivery/deliveryboy');
		     }
     }else{
     	$this->messageManager->addSuccessMessage(__('Enable The ERP And Then Try Again Later.'));
     	return $resultRedirect->setPath('expressdelivery/deliveryboy');
     }
   }


public function getVarDirectoryPath() {
    return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
}

/**
 * Write data to CSV file using fputcsv.
 *
 * @param array $headers Headers for the CSV file
 * @param array $data Data rows to be written to the CSV file
 * @param string $filename Name of the CSV file (e.g., 'data.csv')
 * @param string $directory Directory path to save the CSV file (e.g., 'export')
 * @return bool
 */
function writeIndent($headers, $data, $directory)
{
    $handle = fopen($directory, 'w');
    if ($handle === false) {
        return false;
    }
    fputcsv($handle, $headers);
    foreach ($data as $row) {
	    fputcsv($handle, $row);
    }
    fclose($handle);
    $fileContent = file_get_contents($directory);
    $base64EncodedContent = base64_encode($fileContent);
    return $base64EncodedContent;
}


    /**
     * Get Deliveryboy Id from Request.
     *
     * @return int|null
     */
    public function initCurrentDeliveryboy()
    {
        $deliveryboyId = (int)$this->getRequest()->getParam("id");
        if ($deliveryboyId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_DELIVERYBOY_ID, $deliveryboyId);
        }
        return $deliveryboyId;
    }
}


