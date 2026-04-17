<?php

namespace Centralbooks\Freshchat\Model;

use Magento\Framework\Model\Context;
use Psr\Log\LoggerInterface as PsrLogger;

class Itemmaster {

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        PsrLogger $logger,
        \Centralbooks\Freshchat\Helper\Data $helperData, 
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Tax\Api\TaxClassManagementInterface $taxClassManagementInterface,
        \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
         \Magento\Framework\App\State $state,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel
    ) {
        $this->_curl = $curl;
        $this->_logger = $logger;
        $this->helper = $helperData;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->taxClassManagementInterface = $taxClassManagementInterface;
        $this->taxClassKeyDataObjectFactory = $taxClassKeyDataObjectFactory;
        $this->state = $state;
        
        if (!$this->state->validateAreaCode()) {
         $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        }
        $this->productResourceModel = $productResourceModel;
    }
    
     //tokenn generation based on admin configuration
    
    public function GenerateToken() {

        try {
            $this->helper->getNavisionlogging("generate api call started");
            $postfields = array("username" => $this->helper->getUsername(), //"MAGENTO",
                "password" => $this->helper->getPassword(), //"1234P@ssw0rd",
                "grant_type" => "password");

            $fields_string = http_build_query($postfields);
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->helper->getApiurl() . 'CBSUATAPI/api/GetToken',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => $fields_string, //'username=MAGENTO&password=1234P%40ssw0rd&grant_type=password',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded'
                ),
            ));

            $response = curl_exec($curl);
            if ($e = curl_error($curl)) {
                $this->helper->getNavisionlogging($e);
                return "error";
            } else {
                $rep = json_decode($response, TRUE);

                if (isset($rep["access_token"])) {
                    $this->helper->getNavisionlogging("NAvision token generation successfull");
                    return $rep["access_token"];
                } else {
                    $this->helper->getNavisionlogging($rep["Message"]);
                    return "error";
                }
            }
            curl_close($curl);
        } catch (Exception $exc) {
            $this->helper->getNavisionlogging($exc->getMessage());
            return "error";
        }
    }
    
    public function Itemmastersynch() {
        try {
            $this->helper->getNavisionlogging("itemsynch started");
            $accesstoken = $this->GenerateToken();
            if ($accesstoken != "error") {
                $this->helper->getNavisionlogging("itemsynch started token got");
//                echo $accesstoken;
                $curl = curl_init();

                $apiidentifier = date('Y-m-d\TH:i:s.u\Z');
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $this->helper->getApiurl() . 'CBSUATAPI/api/MasterItems', //$this->helper->getApiurl() . 'CBSUATAPI/api/MasterItems',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_POSTFIELDS => '{
                                               "newerthan":"' . $apiidentifier . '",
                                               "fields":"no,isbn,description,unit_price,vendor_no,newerthan"
                                            }',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization:' . $accesstoken,
                        'username:' . $this->helper->getUsername(),
                        'password:' . $this->helper->getPassword(),
                        'version: 1',
                        'Content-Type: application/json'
                    ),
                ));

                $response = curl_exec($curl);
                print_r($response);
                curl_close($curl);

                $rep = json_decode($response);
                if ($rep->Status == "success") {
                    $this->helper->getNavisionlogging("itemsynch success reposnce" . $response);
                    $itemnumbers = array();
                    foreach ($rep->data as $value) {
                        $itemnumbers[]=$value->no;
                    }
                } else {
                    $this->helper->getNavisionlogging($rep);
                }
            } else {
//                echo "error came";
                $this->helper->getNavisionlogging($rep);
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    public function loadMyProduct($sku) {

        try {
            $product = $this->productRepository->get($sku);
            $isNew = false;

            
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            //$product = false;
            $isNew = true;
        }
        return $isNew;
    }

    public function getAttributeoptionid($product,$attributecode,$optionlabel){
        $attr = $product->getResource()->getAttribute($attributecode);
       return $avid = $attr->getSource()->getOptionId($optionlabel); //name in Default Store View
    }
    
    public function getTaxClassId($clasName){
        $taxClassId = $this->taxClassManagementInterface->getTaxClassId(
            $this->taxClassKeyDataObjectFactory->create()
                ->setType(\Magento\Tax\Api\Data\TaxClassKeyInterface::TYPE_NAME)
                ->setValue($clasName)
        );
        return $taxClassId;
    }
    
    public function createNavisionitem(){
        if ($rep->Status == "success") {
                    $this->helper->getNavisionlogging("itemsynch success reposnce".$response);
                    foreach ($rep->data as $value) {


                        try {
                            $product = $this->productRepository->get($value->No);
                            
                            $this->helper->getNavisionlogging("update started existing product");
                            
                            $isNew = false;
//                            print_r($product->getId());

//                            $attr = $this->getAttributeoptionid($product, 'class_school', "Class 6");
//                            $product->setData('class_school', $attr);
                            $product->setName($value->Description);
                            $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                            $product->setVisibility(4);
                            $product->setPrice($value->Unit_Price);
                            $product->setHsn($value->HSN_SAC_Code);
                            $attr = $product->getResource()->getAttribute('publisher');
                            $avid = $attr->getSource()->getOptionId($value->Publisher); //name in Default Store View
                            $product->setData('publisher', $avid);
                            $product->setAttributeSetId(4); // Default attribute set for products
                            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                            $product->setData('isbn', $value->ISBN);
                            $attr = $this->getAttributeoptionid($product, 'class_school', "UKG");
                            $product->setData('class_school', $attr);
                            $product->setStockData(
                                    array(
                                        'use_config_manage_stock' => 0,
                                        // checkbox for 'Use config settings' 
                                        'manage_stock' => 1, // manage stock
                                        'min_sale_qty' => 1, // Shopping Cart Minimum Qty Allowed 
                                        'max_sale_qty' => 2, // Shopping Cart Maximum Qty Allowed
                                        'is_in_stock' => 1, // Stock Availability of product
                                        'qty' => $value->Inventory,//100//$data['qty'] // qty of product
                                    )
                            );
                            $taxclassid = $this->getTaxClassId($value->GST_Group_Code);
                            $product->setCustomAttribute('tax_class_id', $taxclassid);
                            
                            $product = $this->productRepository->save($product);
                            $this->helper->getNavisionlogging("updated successfully");
                        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                            //$product = false;
                            $this->helper->getNavisionlogging("New product started creation");
                            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
                            $product = $this->productFactory->create();
                            $product->setSku($value->No);
                            $product->setName($value->Description);
                            $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                            $product->setVisibility(4);
                            $product->setPrice($value->Unit_Price);
                            $product->setHsn($value->HSN_SAC_Code);
                            $attr = $product->getResource()->getAttribute('publisher');
                            $avid = $attr->getSource()->getOptionId($value->Publisher); //name in Default Store View
                            $product->setData('publisher', $avid);
                            $product->setAttributeSetId(4); // Default attribute set for products
                            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                            $product->setData('isbn', $value->ISBN);
                            $attr = $this->getAttributeoptionid($product, 'class_school', "UKG");
                            $product->setData('class_school', $attr);
                            $product->setStockData(
                                    array(
                                        'use_config_manage_stock' => 0,
                                        // checkbox for 'Use config settings' 
                                        'manage_stock' => 1, // manage stock
                                        'min_sale_qty' => 1, // Shopping Cart Minimum Qty Allowed 
                                        'max_sale_qty' => 2, // Shopping Cart Maximum Qty Allowed
                                        'is_in_stock' => 1, // Stock Availability of product
                                        'qty' => $value->Inventory,//100//$data['qty'] // qty of product
                                    )
                            );
                            $taxclassid = $this->getTaxClassId($value->GST_Group_Code);
                            $product->setCustomAttribute('tax_class_id', $taxclassid);
                            //                        print_r($product->getData());
                            //                        die("herere");
                            $product = $this->productRepository->save($product); // This is important - the version provided and the version returned will be different objects
                            $this->helper->getNavisionlogging("New product created successfully");
                        }
                    }
                } else {
                   
                    $this->helper->getNavisionlogging($rep);
                }
    }

}
