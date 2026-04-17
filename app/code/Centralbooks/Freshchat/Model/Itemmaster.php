<?php /** @noinspection ALL */

namespace Centralbooks\Freshchat\Model;

use Magento\Framework\Model\Context;
use Magento\Tax\Api\Data\TaxClassKeyInterface;


/**
 *
 */
class Itemmaster
{


    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */

    protected $_curl;

    /**
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Centralbooks\Freshchat\Helper\Data $helperData
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Tax\Api\TaxClassManagementInterface $taxClassManagementInterface
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory
     * @param \Magento\Framework\App\State $state
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Framework\HTTP\Client\Curl               $curl,
//        \Centralbooks\Freshchat\Helper\Data                $helperData,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface   $productRepository,
        \Magento\Tax\Api\TaxClassManagementInterface      $taxClassManagementInterface,
        \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
        \Magento\Framework\App\State                      $state
    )
    {
        $this->_curl = $curl;
        $this->helper = $helperData;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->taxClassManagementInterface = $taxClassManagementInterface;
        $this->taxClassKeyDataObjectFactory = $taxClassKeyDataObjectFactory;
        $this->state = $state;
        if (!$this->state->validateAreaCode()) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        }
    }

    /**
     * @return mixed|string
     */
    public function GenerateToken()
    {

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

    /**
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function Itemmastersynch()
    {
        try {
            $this->helper->getNavisionlogging("itemsynch started");
            $accesstoken = $this->GenerateToken();
            if ($accesstoken != "error") {
                $this->helper->getNavisionlogging("itemsynch started token got");
//                echo $accesstoken;
                $curl = curl_init();

                $apiidentifier = date('Y-m-d\TH:i:s.u\Z');
                //"' . $apiidentifier . '",
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $this->helper->getApiurl() . 'CBSUATAPI/api/MasterItems', //$this->helper->getApiurl() . 'CBSUATAPI/api/MasterItems',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_POSTFIELDS => '{
                                               "newerthan": "' . $apiidentifier . '",
                                               "fields":"itemid,isbn,desc,mrp,vendorid,vendor,newerthan"
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
//              print_r($response);
                curl_close($curl);

                $rep = json_decode($response);
                if ($rep->status == "success") {
                    $this->helper->getNavisionlogging("itemsynch success reposnce" . $response);
                    $itemnumbers = array();
                    foreach ($rep->data as $value) {
                        $itemnumbers[] = $value->itemid;
                    }
                    $itemsid = array_chunk($itemnumbers, 3, true);
                    foreach ($itemsid as $item) {
                        $List = implode(', ', $item);
//                        print_r($List);
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $this->helper->getApiurl() . 'CBSUATAPI/api/MasterItemDetails',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                            CURLOPT_POSTFIELDS => '{
                                               "itemid":"' . $List . '",
                                               "fields":"itemid,isbn,desc,itemcategorycode,publisher,gstgroupcode,hsncode,mrp,newerthan,inventory"
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
//                        print_r($response);
                        curl_close($curl);

                        $rep = json_decode($response);
                        if ($rep->status == "success") {
                            $this->helper->getNavisionlogging("itemsynch success reposnce" . $response);
                            $this->createNavisionProduct($rep);
//                            print_r($rep);
                        } else {
                            $this->helper->getNavisionlogging($rep);
                        }
                    }
                } else {
                    $this->helper->getNavisionlogging("error " . $accesstoken);
                }
            } else {
                $this->helper->getNavisionlogging($accesstoken);
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    /**
     * @param $itemnumbers
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function createNavisionProduct($itemnumbers)
    {

        foreach ($itemnumbers->data as $value) {

            $product = $this->productRepository->get($value->itemid);
            if ($product) {
                $this->helper->getNavisionlogging("update started existing product");
                try {
                    $product->setName($value->desc);
                    $product->setVisibility(4);
                    $product->setPrice($value->mrp);
                    $product->setHsn($value->hsncode);
                    $attr = $product->getResource()->getAttribute('publisher');
                    $avid = $attr->getSource()->getOptionId($value->publisher); //name in Default Store View
                    $product->setData('publisher', $avid);
                    $product->setAttributeSetId(4); // Default attribute set for products
                    $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                    $product->setData('isbn', $value->isbn);
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
                            'qty' => $value->inventory,//100//$data['qty'] // qty of product
                        )
                    );
                    $taxclassid = $this->getTaxClassId($value->gstgroupcode);
                    $product->setCustomAttribute('tax_class_id', $taxclassid);

                    $product = $this->productRepository->save($product);
                    $this->helper->getNavisionlogging("updated successfully" . $product->getId());
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->helper->getNavisionlogging("updated" . $e->getMessage());
                }
            } else {
                try {
                    //$product = false;
                    $this->helper->getNavisionlogging("New product started creation");
                    /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
                    $product = $this->productFactory->create();
                    $product->setSku($value->No);
                    $product->setName($value->Description);
                    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                    $product->setVisibility(4);
                    $product->setPrice($value->mrp);
                    $product->setHsn($value->hsncode);
                    $attr = $product->getResource()->getAttribute('publisher');
                    $avid = $attr->getSource()->getOptionId($value->publisher); //name in Default Store View
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
                            'qty' => $value->inventory,//100//$data['qty'] // qty of product
                        )
                    );
                    $taxclassid = $this->getTaxClassId($value->gstgroupcode);
                    $product->setCustomAttribute('tax_class_id', $taxclassid);
                    $product = $this->productRepository->save($product); // This is important - the version provided and the version returned will be different objects
                    $this->helper->getNavisionlogging("New product created successfully" . $product->getId());
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->helper->getNavisionlogging("updated" . $e->getMessage());
                }

            }
        }

    }

    /**
     * @param $sku
     * @return bool
     */
    public function loadMyProduct($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
            $product = true;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $product = false;
        }
        return $product;
    }

    /**
     * @param $product
     * @param $attributecode
     * @param $optionlabel
     * @return mixed
     */
    public function getAttributeoptionid($product, $attributecode, $optionlabel)
    {
        $attr = $product->getResource()->getAttribute($attributecode);
        return $attr->getSource()->getOptionId($optionlabel); //name in Default Store View
    }

    /**
     * @param $clasName
     * @return int|null
     */
    public function getTaxClassId($clasName): ?int
    {
        return $this->taxClassManagementInterface->getTaxClassId(
            $this->taxClassKeyDataObjectFactory->create()
                ->setType(TaxClassKeyInterface::TYPE_NAME)
                ->setValue($clasName)
        );
    }
}
