<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */

namespace Infomodus\Fedexlabel\Helper;

use DVDoug\BoxPacker\Packer;
use Infomodus\Fedexlabel\Model\Packer\TestBox as PackerBox;
use Infomodus\Fedexlabel\Model\Packer\TestItem as PackerItem;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ShipmentFactory;

class Handy extends AbstractHelper
{
    protected $_attributeFactory;

    public $_context;
    public $_objectManager;
    public $_conf;
    public $_registry;
    public $order = null;
    public $shipment;
    public $shipment_id = null;
    public $type;
    public $type2;
    public $paymentmethod;
    public $shipmentTotalPrice;
    public $shippingAddress;
    public $defConfParams;
    public $defPackageParams = [];
    public $shipByFedex;
    public $shipByFedexCode;
    public $fedexAccounts;
    public $label = [];
    public $label2 = [];
    public $storeId;
    public $error;

    public $sku;
    public $isPassDispatch = true;
    public $isPassDispatchBreak = false;

    protected $shipmentFactory;
    protected $shipmentSender;
    protected $shipmentLoaderFactory;

    public $rates_tax;
    public $totalWeight;
    protected $messageManager;

    protected $_currencyFactory;
    protected $addresses;
    protected $allowedCurrencies = [];
    protected $defaultdimensionsset;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;
    /**
     * @var \Magento\Sales\Model\Order\ShipmentRepository
     */
    private $shipmentRepository;
    /**
     * @var \Magento\Sales\Model\Order\CreditmemoRepository
     */
    private $creditmemoRepository;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;
    /**
     * @var \Infomodus\Fedexlabel\Model\ResourceModel\Account\CollectionFactory
     */
    private $accountsCollectionFactory;
    /**
     * @var \Infomodus\Fedexlabel\Model\ResourceModel\Conformity\CollectionFactory
     */
    private $conformityCollectionFactory;
    /**
     * @var \Infomodus\Fedexlabel\Model\ItemsFactory
     */
    private $labelModel;
    /**
     * @var \Infomodus\Fedexlabel\Model\AccountFactory
     */
    private $accountFactory;
    /**
     * @var \Infomodus\Fedexlabel\Model\FedexFactory
     */
    private $fedexModelFactory;
    private $configOptions;

    /**
     * Handy constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Config $config
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoaderFactory $shipmentLoaderFactory
     * @param ShipmentSender $shipmentSender
     * @param ShipmentFactory $shipmentFactory
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository
     * @param \Magento\Sales\Model\Order\CreditmemoRepository $creditmemoRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Infomodus\Fedexlabel\Model\Config\Defaultaddress $addresses
     * @param \Infomodus\Fedexlabel\Model\Config\Defaultdimensionsset $defaultdimensionsset ,
     * @param \Infomodus\Fedexlabel\Model\ResourceModel\Account\CollectionFactory $accountsCollectionFactory
     * @param \Infomodus\Fedexlabel\Model\ResourceModel\Conformity\CollectionFactory $conformityCollectionFactory
     * @param \Infomodus\Fedexlabel\Model\ItemsFactory $labelModel
     * @param \Infomodus\Fedexlabel\Model\AccountFactory $accountFactory
     * @param \Infomodus\Fedexlabel\Model\FedexFactory $fedexModelFactory ,
     * @param \Infomodus\Fedexlabel\Model\Config\Options $configOptions
     */
    public function __construct(
        \Retailinsights\FedExCustom\Model\FedexResponseDataFactory $fedexResponseModel,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Infomodus\Fedexlabel\Helper\Config $config,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoaderFactory $shipmentLoaderFactory,
        ShipmentSender $shipmentSender,
        ShipmentFactory $shipmentFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Sales\Model\Order\CreditmemoRepository $creditmemoRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Infomodus\Fedexlabel\Model\Config\Defaultaddress $addresses,
        \Infomodus\Fedexlabel\Model\Config\Defaultdimensionsset $defaultdimensionsset,
        \Infomodus\Fedexlabel\Model\ResourceModel\Account\CollectionFactory $accountsCollectionFactory,
        \Infomodus\Fedexlabel\Model\ResourceModel\Conformity\CollectionFactory $conformityCollectionFactory,
        \Infomodus\Fedexlabel\Model\ItemsFactory $labelModel,
        \Infomodus\Fedexlabel\Model\AccountFactory $accountFactory,
        \Infomodus\Fedexlabel\Model\FedexFactory $fedexModelFactory,
        \Infomodus\Fedexlabel\Model\Config\Options $configOptions
    )
    {
        $this->fedexResponseModel = $fedexResponseModel;
        $this->_attributeFactory = $attributeFactory;
        $this->shipmentSender = $shipmentSender;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentLoaderFactory = $shipmentLoaderFactory;
        $this->_registry = $registry;
        parent::__construct($context);
        $this->_context = $context;
        $this->_objectManager = $objectManager;
        $this->_conf = $config;
        $this->messageManager = $messageManager;
        $this->_currencyFactory = $currencyFactory;
        $this->addresses = $addresses;
        $this->defaultdimensionsset = $defaultdimensionsset;
        $this->orderRepository = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->productRepository = $productRepository;
        $this->accountsCollectionFactory = $accountsCollectionFactory;
        $this->conformityCollectionFactory = $conformityCollectionFactory;
        $this->labelModel = $labelModel;
        $this->accountFactory = $accountFactory;
        $this->fedexModelFactory = $fedexModelFactory;
        $this->configOptions = $configOptions;
    }


    public function intermediate($order, $type, $shipment_id = null)
    {
        $this->defConfParams = [];
        $this->shipment_id = $shipment_id;
        unset($shipment_id);
        if ($order !== null) {
            if (!is_numeric($order)) {
                $this->order = $order;
            } else {
                $this->order = $this->orderRepository->get($order);
            }
        } elseif ($this->shipment_id !== null) {
            if ($type !== 'refund') {
                $this->order = $this->shipmentRepository->get($this->shipment_id)->getOrder();
            } else {
                $this->order = $this->creditmemoRepository->get($this->shipment_id)->getOrder();
            }
        }

        unset($order);
        $this->type = $type;
        unset($type);
        $this->storeId = $this->order->getStoreId();

        $this->paymentmethod = "";
        if (is_object($this->order->getPayment())) {
            $this->paymentmethod = $this->order->getPayment()->getData();
            $this->paymentmethod = $this->paymentmethod['method'];
        }

        $this->shippingAddress = $this->order->getShippingAddress();
        if (!$this->shippingAddress) {
            $this->shippingAddress = $this->order->getBillingAddress();
        }

        if ($this->shipment_id !== null) {
            if ($this->type != 'refund' || $this->order->hasCreditmemos() == 0) {
                $this->shipment = $this->shipmentRepository->get($this->shipment_id);
            } else {
                $creditmemo = $this->creditmemoRepository->get($this->shipment_id);
                if ($creditmemo && $creditmemo->getOrderId() == $this->order->getId()) {
                    $this->shipment = $creditmemo;
                } else {
                    $this->shipment = $this->shipmentRepository->get($this->shipment_id);
                }
            }

            $shipmentAllItems = $this->shipment->getAllItems();
        } else {
            $shipmentAllItems = $this->order->getAllVisibleItems();
        }

        $totalPrice = 0;
        $this->totalWeight = 0;
        $totalShipmentQty = 0;
        $this->sku = [];
        $pi = 1;
        $itemDeclaredTotalPrice = 0;
        $this->defConfParams['invoice_product'] = [];
        $productRepository = $this->productRepository;
        $this->allowedCurrencies = $this->_currencyFactory->create()->getConfigAllowCurrencies();
        $baseCurrencyCode = $this->_conf->getStoreConfig('currency/options/base', $this->storeId);
        $baseOrderBaseCurrencyCode = $this->order->getBaseCurrencyCode();
        $responseCurrencyCode = $this->_conf->getStoreConfig('fedexlabel/ratepayment/currencycode', $this->storeId);
        $currencyKoef = 1;
        if ($responseCurrencyCode != $baseOrderBaseCurrencyCode && $baseCurrencyCode == $baseOrderBaseCurrencyCode) {
            if (in_array($responseCurrencyCode, $this->allowedCurrencies)) {
                $currencyKoef = $this->_getBaseCurrencyKoef($baseCurrencyCode, $responseCurrencyCode);
            }
        }

        foreach ($shipmentAllItems as $item) {
            if (!$item->isDeleted() && !$item->getParentItemId()) {
                if ($item->getOrderItemId()) {
                    $item = $this->order->getItemById($item->getOrderItemId());
                }

                if (!$item) {
                    continue;
                }

                $originProductObj = $productRepository->getById($item->getProductId());
                $originProduct = $originProductObj->getData();
                $itemData = $item->getData();
                $this->sku[$itemData['product_id']] = $itemData['sku'];
                if (!isset($itemData['qty'])) {
                    $itemData['qty'] = $itemData['qty_ordered'];
                }

                $itemPrice = $item->getBasePrice() /*- $item->getBaseDiscountAmount() / $itemData['qty']*/
                ;
                $itemPrice = (double)$itemPrice * $currencyKoef;
                $totalPrice += $itemPrice * $itemData['qty'];
                $this->totalWeight += $itemData['weight'] * $itemData['qty'];
                $totalShipmentQty += $itemData['qty'];
                /*$itemName = isset($originProduct['declared_name']) && strlen(trim($originProduct['declared_name'])) > 0 ? $originProduct['declared_name'] : $itemData['name'];*/

                $product_attribute_harmonized = "";
                $harmonizedAttributeCode = $this->_conf->getStoreConfig('fedexlabel/paperless/product_attribute_harmonized', $this->storeId);
                if ($harmonizedAttributeCode && isset($originProduct[$harmonizedAttributeCode])) {
                    $attributes = $this->_attributeFactory->create()->getAttributeCodesByFrontendType('select');
                    if (!in_array($harmonizedAttributeCode, $attributes)) {
                        $product_attribute_harmonized = $originProduct[$harmonizedAttributeCode];
                    } else {
                        $product_attribute_harmonized = $this->getAttributeContent($originProductObj, $harmonizedAttributeCode);
                    }
                }

                $itemDeclaredTotalPrice += $itemPrice * $itemData['qty'];
                $this->defConfParams['invoice_product'][] = [
                    'enabled' => 1,
                    'qty' => $itemData['qty'],
                    'description' => $this->_conf->getStoreConfig('fedexlabel/paperless/product_attribute_name', $this->storeId) == '' ? $originProduct['name'] : $this->getAttributeContent($originProductObj, $this->_conf->getStoreConfig('fedexlabel/paperless/product_attribute_name', $this->storeId)),
                    'country_code' => (isset($productOrigin['country_of_manufacture']) && $originProduct['country_of_manufacture'] != '') ? $originProduct['country_of_manufacture'] : $this->_conf->getStoreConfig('fedexlabel/paperless/product_origin_country', $this->storeId),
                    'currency' => $this->_conf->getStoreConfig('fedexlabel/ratepayment/currencycode', $this->storeId),
                    'weight' => $itemData['weight'],
                    'price' => round($itemPrice + (((float)$item->getTaxPercent()) / 100 * $itemPrice), 2),
                    'sku' => $itemData['sku'],
                    'id' => $itemData['product_id'],
                    'harmonized' => $product_attribute_harmonized,
                    'tax_percent' => $item->getTaxPercent(),
                ];
                $pi++;
            }
        }

        $totalQty = 0;
        foreach ($this->order->getAllVisibleItems() as $item) {
            $itemData = $item->getData();
            $totalQty += $itemData['qty_ordered'];
        }

        $this->fedexAccounts = ["S" => "Shipper", "R" => "Recipient"];
        $fedexAcctModel = $this->accountsCollectionFactory->create()->load();
        foreach ($fedexAcctModel as $u1) {
            $this->fedexAccounts[$u1->getId()] = $u1->getCompanyname();
        }

        if (count($shipmentAllItems) != count($this->order->getAllVisibleItems())) {
            $this->shipmentTotalPrice = $totalPrice + $this->order->getBaseTaxAmount() * $currencyKoef;
        } else {
            $this->shipmentTotalPrice = ($this->order->getBaseGrandTotal() - $this->order->getBaseShippingAmount()) * $currencyKoef;
        }

        $this->defConfParams['fedexaccount'] = $this->_conf
            ->getStoreConfig('fedexlabel/ratepayment/third_party', $this->storeId);

        $ship_method = $this->order->getShippingMethod();
        $address = $this->addresses->getAddressesById($this->_conf->getStoreConfig('fedexlabel/shipping/defaultshipper', $this->storeId));

        if (empty($address)) {
            return false;
        }

        $shippingInternational = ($this->shippingAddress->getCountryId() == $address->getCountry()) ? 0 : 1;
        $this->shipByFedex = preg_replace("/^fedex_.{1,45}$/", 'fedex', $ship_method);

        if ($this->shipByFedex == 'fedex') {
            $this->shipByFedexCode = preg_replace("/^fedex_(.{1,30})$/", '$1', $ship_method);
            $this->defConfParams['serviceCode'] = $this->shipByFedexCode;
        } elseif ($this->shipByFedex = preg_replace("/^caship_.{1,100}$/", 'caship', $ship_method) == 'caship') {
            $this->shipByFedex = 'fedex';
            $this->shipByFedexCode = explode("_", $ship_method);
            $apModel = $this->_objectManager->get('Infomodus\Caship\Model\Items')->load($this->shipByFedexCode[1]);
            if ($apModel && ($apModel->getCompanyType() == 'fedex' || $apModel->getCompanyType() == 'fedexinfomodus')) {
                $this->shipByFedexCode = $apModel->getFedexmethodId();
                $this->defConfParams['serviceCode'] = $this->shipByFedexCode;
            }
        } elseif ($this->_conf->getStoreConfig('fedexlabel/shipping/shipping_method_native', $this->storeId) == 1) {
            $modelConformity = $this->conformityCollectionFactory->create()
                ->addFieldToFilter('method_id', $ship_method)->addFieldToFilter('store_id',
                    $this->storeId ? $this->storeId : 1)
                ->getSelect()->where('CONCAT(",", country_ids, ",") LIKE "%,' .
                    $this->shippingAddress->getCountryId() . ',%"')->query()->fetch();
            if ($modelConformity && count($modelConformity) > 0) {
                $this->defConfParams['serviceCode'] = $modelConformity["fedexmethod_id"];
            }
        }

        if (!isset($this->defConfParams['serviceCode'])) {
            if ($this->type !== 'refund') {
                $this->defConfParams['serviceCode'] = $shippingInternational == 0 ?
                    $this->_conf->getStoreConfig('fedexlabel/shipping/defaultshipmentmethod', $this->storeId) :
                    $this->_conf->getStoreConfig('fedexlabel/shipping/defaultshipmentmethodworld', $this->storeId);
            } else {
                $this->defConfParams['serviceCode'] = $shippingInternational == 0 ?
                    $this->_conf->getStoreConfig('fedexlabel/return/default_return_method', $this->storeId) :
                    $this->_conf->getStoreConfig('fedexlabel/return/default_return_method_inter', $this->storeId);
            }
        }

        if ($this->totalWeight <= 0) {
            $this->totalWeight = (float)str_replace(',', '.',
                $this->_conf->getStoreConfig('fedexlabel/weightdimension/defweigth', $this->storeId));
            if ($this->totalWeight == '' || $this->totalWeight <= 0) {
                $this->messageManager->addErrorMessage("Some of the products are missing their weight information. Please fill the weight for all products or enter a default value from the \"Weight and Dimensions\" section of the FedEx module configuration.");
            }
        }

        if ($this->_conf->getStoreConfig('fedexlabel/weightdimension/max_weight', $this->storeId)) {
            $this->defConfParams['max_weight'] = (float)str_replace(",", ".", $this->_conf->getStoreConfig('fedexlabel/weightdimension/max_weight', $this->storeId)) + 0.0001;
        }

        $attributeCodeWidth = $this->_conf->getStoreConfig('fedexlabel/packaging/multipackes_attribute_width', $this->storeId) ?
            $this->_conf->getStoreConfig('fedexlabel/packaging/multipackes_attribute_width', $this->storeId) : 'width';
        $attributeCodeHeight = $this->_conf->getStoreConfig('fedexlabel/packaging/multipackes_attribute_height', $this->storeId) ?
            $this->_conf->getStoreConfig('fedexlabel/packaging/multipackes_attribute_height', $this->storeId) : 'height';
        $attributeCodeLength = $this->_conf->getStoreConfig('fedexlabel/packaging/multipackes_attribute_length', $this->storeId) ?
            $this->_conf->getStoreConfig('fedexlabel/packaging/multipackes_attribute_length', $this->storeId) : 'length';

        /* Multi package */
        $dimensionSets = $this->defaultdimensionsset->toOptionObjects();

        if ($this->type == 'shipment'
            && $this->_conf->getStoreConfig('fedexlabel/packaging/frontend_multipackes_enable',
                $this->storeId) == 1
        ) {
            $i = 0;
            $defParArr_1 = [];
            foreach ($shipmentAllItems as $item) {
                if (!$item->isDeleted() && !$item->getParentItemId()) {
                    $itemData = $item->getData();
                    if (!isset($itemData['qty'])) {
                        $itemData['qty'] = $itemData['qty_ordered'];
                    }

                    if (!isset($itemData['weight'])) {
                        foreach ($this->order->getAllVisibleItems() as $w) {
                            if ($w->getProductId() == $itemData["product_id"]) {
                                $itemData['weight'] = $w->getWeight();
                            }
                        }
                    }

                    $myproduct = $this->productRepository->getById($itemData['product_id'])->getData();
                    if (!empty($itemData['qty'])) {
                        for ($ik = 0; $ik < $itemData['qty']; $ik++) {
                            $is_attribute = 0;
                            if ($this->_conf
                                    ->getStoreConfig('fedexlabel/packaging/packages_by_attribute_enable',
                                        $this->storeId) == 1) {
                                if (isset($myproduct[$this->_conf
                                        ->getStoreConfig('fedexlabel/packaging/packages_by_attribute_code',
                                            $this->storeId)])) {
                                    $attribute = explode(";",
                                        trim($myproduct[$this->_conf
                                            ->getStoreConfig(
                                                'fedexlabel/packaging/packages_by_attribute_code',
                                                $this->storeId)], ";"));
                                    if (count($attribute) > 1) {
                                        $rvaPrice = $item->getBasePrice() * $currencyKoef;
                                        foreach ($attribute as $v) {
                                            $itemData['weight'] = $v;
                                            $itemData['sku'][0] = $itemData['sku'];
                                            $itemData['price'] = round($rvaPrice / count($attribute), 2);
                                            $defParArr_1[$i] = $this->setPackageDefParams($itemData);
                                            $i++;
                                        }

                                        $is_attribute = 1;
                                    }
                                }
                            }

                            if ($is_attribute !== 1) {
                                $countProductInBox = 0;
                                try {
                                    if ($this->_conf->getStoreConfig('fedexlabel/weightdimension/dimensions_type', $this->storeId) == 0) {
                                        $this->isPassDispatch = true;
                                        $this->isPassDispatchBreak = false;

                                        $this->_eventManager->dispatch('infomodus_fedlab_handy_each_product_to_box', ['item' => $item, 'obj' => $this]);
                                        if ($this->isPassDispatch === true) {
                                            $packer = new Packer();
                                            $myproduct = $this->productRepository->getById($itemData['product_id'])->getData();
                                            if ($item->getWeight()) {
                                                $myproduct['weight'] = $item->getWeight();

                                                $myproduct = $this->getProductSizes(
                                                    $item,
                                                    $itemData,
                                                    $myproduct,
                                                    $packer,
                                                    $attributeCodeWidth,
                                                    $attributeCodeHeight,
                                                    $attributeCodeLength,
                                                    $currencyKoef
                                                );
                                                if ($myproduct === false) {
                                                    $this->messageManager->addWarningMessage("Product " . $item->getName() . " does not have width or height or length");
                                                    break;
                                                } else {
                                                    $countProductInBox++;
                                                }

                                                if ($countProductInBox > 0) {
                                                    $packer->addBox(new PackerBox(
                                                        'def_box',
                                                        1000000,
                                                        1000000,
                                                        1000000,
                                                        0,
                                                        1000000,
                                                        1000000,
                                                        1000000,
                                                        150000
                                                    ));

                                                    $packedBoxes = $packer->pack();
                                                    if (count($packedBoxes) > 0) {
                                                        foreach ($packedBoxes as $packedBox) {
                                                            $itemDataTwo = [];
                                                            $itemDataTwo['product_id'] = $itemData['product_id'];
                                                            $itemDataTwo['width'] = round($packedBox->getUsedWidth()/1000, 2);
                                                            $itemDataTwo['length'] = round($packedBox->getUsedLength()/1000, 2);
                                                            $itemDataTwo['height'] = round($packedBox->getUsedDepth()/1000, 2);
                                                            $itemDataTwo['weight'] = $packedBox->getWeight()/1000;
                                                            $itemsInTheBox = $packedBox->getItems();
                                                            $itemDataTwo['price'] = 0;
                                                            $itemDataTwo['sku'] = [];
                                                            foreach ($itemsInTheBox as $itemBox) {
                                                                $itemDataTwo['price'] += $itemBox->getItem()->getDescription();
                                                                $itemDataTwo['sku'][] = $itemBox->getItem()->getProductOptions()['sku'];
                                                            }

                                                            $defParArr_1[$i] = $this->setPackageDefParams($itemDataTwo);
                                                            $i++;
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            if($this->isPassDispatchBreak === true){
                                                break;
                                            }
                                        }
                                    } else {
                                        $defaultBox = $this->_conf->getStoreConfig('fedexlabel/weightdimension/default_dimensions_box', $this->storeId);
                                        if (!empty($defaultBox) && !empty($dimensionSets[$defaultBox])) {
                                            $dimDefSet = $dimensionSets[$defaultBox];
                                            $itemDataTwo = array();
                                            $itemDataTwo['width'] = $dimDefSet->getOuterWidth();
                                            $itemDataTwo['length'] = $dimDefSet->getOuterLengths();
                                            $itemDataTwo['height'] = $dimDefSet->getOuterHeight();
                                            $itemData['sku'] = $this->sku;
                                            $defParArr_1[$i] = $this->setPackageDefParams($itemDataTwo);
                                            $i++;
                                        }
                                    }
                                } catch (\DVDoug\BoxPacker\ItemTooLargeException $e) {
                                    $this->_logger->error($e->getMessage());
                                }

                            }
                        }
                    } else {
                        $this->messageManager->addWarningMessage("Product " . $item->getName() . " of this order contains \"0\" quantity");
                    }
                }
            }
            $this->defPackageParams += $defParArr_1;
        } else {
            $this->defPackageParams = [];
            $i = 0;
            if ($this->_conf->getStoreConfig('fedexlabel/packaging/packages_by_attribute_enable',
                    $this->storeId) == 1 && $this->type == 'shipment') {
                foreach ($shipmentAllItems as $item) {
                    if (!$item->isDeleted() && !$item->getParentItemId()) {
                        $itemData = $item->getData();
                        if (!isset($itemData['qty'])) {
                            $itemData['qty'] = $itemData['qty_ordered'];
                        }

                        if (!isset($itemData['weight'])) {
                            foreach ($this->order->getAllVisibleItems() as $w) {
                                if ($w->getProductId() == $itemData["product_id"]) {
                                    $itemData['weight'] = $w->getWeight();
                                }
                            }
                        }

                        $itemData2 = $itemData;
                        $myproduct = $this->productRepository->getById($itemData['product_id'])->getData();
                        for ($ik = 0; $ik < $itemData['qty']; $ik++) {
                            if (isset($myproduct[$this->_conf
                                    ->getStoreConfig('fedexlabel/packaging/packages_by_attribute_code',
                                        $this->storeId)])) {
                                $attribute = explode(";", trim($myproduct[$this->_conf
                                    ->getStoreConfig('fedexlabel/packaging/packages_by_attribute_code',
                                        $this->storeId)], ";"));
                                if (count($attribute) > 1) {
                                    foreach ($attribute as $v) {
                                        $this->totalWeight = $this->totalWeight - $itemData2['weight'];
                                        $itemData['price'] = round($item->getBasePrice() * $currencyKoef / count($attribute), 2);
                                        $itemData['weight'] = $v;
                                        $itemData['sku'][0] = $itemData['sku'];
                                        $this->defPackageParams[$i] = $this->setPackageDefParams($itemData);
                                        $i++;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($this->totalWeight > 0) {
                $countProductInBox = 0;
                $countProductWithoutBox = 0;
                if ($this->type == 'shipment' || $this->type == 'invert') {
                    if (count($dimensionSets) > 0) {
                        try {
                            if ($this->_conf->getStoreConfig('fedexlabel/weightdimension/dimensions_type', $this->storeId) == 0) {
                                $packer = new Packer();
                                foreach ($shipmentAllItems as $item) {
                                    $itemData = $item->getData();
                                    if (!isset($itemData['qty'])) {
                                        $itemData['qty'] = $itemData['qty_ordered'];
                                    }

                                    $myproduct = $this->productRepository->getById($itemData['product_id'])->getData();

                                    if ($item->getWeight() && (!isset($myproduct['weight']) || !$myproduct['weight'])) {
                                        $myproduct['weight'] = $item->getWeight();
                                    }

                                    if (!empty($itemData['qty'])) {
                                        for ($ik = 0; $ik < $itemData['qty']; $ik++) {
                                            if ($this->_conf->getStoreConfig('fedexlabel/packaging/product_without_box', $this->storeId)
                                                && isset($myproduct[$this->_conf->getStoreConfig('fedexlabel/packaging/product_without_box', $this->storeId)])
                                                && $myproduct[$this->_conf->getStoreConfig('fedexlabel/packaging/product_without_box', $this->storeId)] == 1) {
                                                $packerWithoutBox = new Packer();
                                                $myproduct = $this->getProductSizes(
                                                    $item,
                                                    $itemData,
                                                    $myproduct,
                                                    $packerWithoutBox,
                                                    $attributeCodeWidth,
                                                    $attributeCodeHeight,
                                                    $attributeCodeLength,
                                                    $currencyKoef
                                                );
                                                $packerWithoutBox->addBox(new PackerBox(
                                                    'def_box',
                                                    1000000,
                                                    1000000,
                                                    1000000,
                                                    0,
                                                    1000000,
                                                    1000000,
                                                    1000000,
                                                    150000
                                                ));
                                                $packedBoxes = $packerWithoutBox->pack();
                                                if (count($packedBoxes) > 0) {
                                                    foreach ($packedBoxes as $packedBox) {
                                                        $itemDataTwo = [];
                                                        $itemDataTwo['width'] = round($packedBox->getUsedWidth()/1000, 2);
                                                        $itemDataTwo['length'] = round($packedBox->getUsedLength()/1000, 2);
                                                        $itemDataTwo['height'] = round($packedBox->getUsedDepth()/1000, 2);
                                                        $itemDataTwo['weight'] = $packedBox->getWeight()/1000;
                                                        $itemsInTheBox = $packedBox->getItems();
                                                        $itemDataTwo['price'] = 0;
                                                        $itemDataTwo['sku'] = [];
                                                        foreach ($itemsInTheBox as $itemBox) {
                                                            $itemDataTwo['price'] += $itemBox->getItem()->getDescription();
                                                            $itemDataTwo['sku'][] = $itemBox->getItem()->getProductOptions()['sku'];
                                                        }
                                                        $this->defPackageParams[$i] = $this->setPackageDefParams($itemDataTwo);
                                                        $i++;
                                                    }
                                                }
                                                $countProductInBox++;
                                                $countProductWithoutBox++;
                                                continue;
                                            } else {
                                                $myproduct = $this->getProductSizes(
                                                    $item,
                                                    $itemData,
                                                    $myproduct,
                                                    $packer,
                                                    $attributeCodeWidth,
                                                    $attributeCodeHeight,
                                                    $attributeCodeLength,
                                                    $currencyKoef
                                                );
                                            }
                                            if ($myproduct === false) {
                                                $countProductInBox = 0;
                                                $this->messageManager->addWarningMessage("Product " . $item->getName() . " does not have width or height or length");
                                                break;
                                            } else {
                                                $countProductInBox++;
                                            }
                                        }
                                    } else {
                                        $countProductInBox = 0;
                                        $this->messageManager->addWarningMessage("Product " . $item->getName() . " of this order contains \"0\" quantity");
                                    }

                                    if ($countProductInBox == 0) {
                                        break;
                                    }
                                }

                                if ($countProductInBox > 0) {
                                    foreach ($dimensionSets as $v) {
                                        if (!empty($v)) {
                                            $packer->addBox(new PackerBox(
                                                $v->getId(),
                                                $v->getOuterWidth()*1000,
                                                $v->getOuterLengths()*1000,
                                                $v->getOuterHeight()*1000,
                                                $v->getEmptyWeight()*1000,
                                                $v->getWidth()*1000,
                                                $v->getLengths()*1000,
                                                $v->getHeight()*1000,
                                                $v->getMaxWeight()*1000
                                            ));
                                        }
                                    }

                                    $packedBoxes = $packer->pack();
                                    if (count($packedBoxes) > 0) {
                                        foreach ($packedBoxes as $packedBox) {
                                            $itemData = [];
                                            $boxType = $packedBox->getBox();
                                            $itemData['width'] = round($boxType->getOuterWidth()/1000, 2);
                                            $itemData['length'] = round($boxType->getOuterLength()/1000, 2);
                                            $itemData['height'] = round($boxType->getOuterDepth()/1000, 2);
                                            $itemData['weight'] = $packedBox->getWeight()/1000;
                                            $itemsInTheBox = $packedBox->getItems();
                                            $itemData['price'] = 0;
                                            $itemData['sku'] = [];
                                            foreach ($itemsInTheBox as $itemBox) {
                                                $itemData['price'] += $itemBox->getItem()->getDescription();
                                                $itemData['sku'][] = $itemBox->getItem()->getProductOptions()['sku'];
                                            }
                                            $this->defPackageParams[$i] = $this->setPackageDefParams($itemData);
                                            $i++;
                                        }
                                    } else if ($countProductWithoutBox != $countProductInBox) {
                                        $countProductInBox = 0;
                                    }
                                }
                            } else {
                                $defaultBox = $this->_conf->getStoreConfig('fedexlabel/weightdimension/default_dimensions_box', $this->storeId);
                                if (!empty($defaultBox) && !empty($dimensionSets[$defaultBox])) {
                                    $dimDefSet = $dimensionSets[$defaultBox];
                                    $itemDataTwo = array();
                                    $itemDataTwo['width'] = $dimDefSet->getOuterWidth();
                                    $itemDataTwo['length'] = $dimDefSet->getOuterLengths();
                                    $itemDataTwo['height'] = $dimDefSet->getOuterHeight();
                                    $itemDataTwo['sku'] = $this->sku;
                                    $this->defPackageParams[$i] = $this->setPackageDefParams($itemDataTwo);
                                    $i++;
                                    $countProductInBox = 1;
                                }
                            }
                        } catch (\DVDoug\BoxPacker\ItemTooLargeException $e) {
                            $countProductInBox = 0;
                            $this->_logger->error($e->getMessage());
                        }
                    } else if (!empty($this->defConfParams['max_weight'])) {
                        $i = 0;
                        $allPackages = [];
                        foreach ($shipmentAllItems as $item) {
                            $itemData = $item->getData();
                            if (!isset($itemData['qty'])) {
                                $itemData['qty'] = $itemData['qty_ordered'];
                            }

                            if (!empty($itemData['qty'])) {
                                for ($ik = 0; $ik < $itemData['qty']; $ik++) {
                                    $sizePackages = count($allPackages) + 1;
                                    for ($i = 0; $i < $sizePackages; $i++) {
                                        if (empty($allPackages[$i]['weight']) || $this->defConfParams['max_weight'] >= $allPackages[$i]['weight'] + $item->getWeight()) {
                                            break;
                                        }
                                    }

                                    if (empty($allPackages[$i]['weight'])) {
                                        $allPackages[$i]['weight'] = 0;
                                    }

                                    if (empty($allPackages[$i]['price'])) {
                                        $allPackages[$i]['price'] = 0;
                                    }

                                    $allPackages[$i]['weight'] += $item->getWeight();
                                    $allPackages[$i]['price'] += $item->getBasePrice() * $currencyKoef;
                                    $allPackages[$i]['sku'][] = $itemData['sku'];
                                }
                            }
                        }

                        if (!empty($allPackages)) {
                            $ipack = 0;
                            foreach ($allPackages as $allPackage) {
                                $this->defPackageParams[$ipack] = $this->setPackageDefParams($allPackage);
                                $countProductInBox++;
                                $ipack++;
                            }
                        }
                    }
                }

                if ($countProductInBox == 0) {
                    $this->defPackageParams[$i] = $this->setPackageDefParams(null);
                }
            }
        }

        $this->defConfParams['shipper_no'] = $this->_conf->getStoreConfig('fedexlabel/shipping/defaultshipper',
            $this->storeId);
        $this->defConfParams['soldto_address'] = $this->_conf->getStoreConfig('fedexlabel/paperless/soldto_address',
            $this->storeId);
        $this->defConfParams['testing'] = $this->_conf->getStoreConfig('fedexlabel/testmode/testing', $this->storeId);
        $this->defConfParams['dropoff'] = $this->_conf->getStoreConfig('fedexlabel/shipping/dropoff', $this->storeId);
        $this->defConfParams['packagingtypecode'] = $this->_conf->getStoreConfig('fedexlabel/shipping/packagingtypecode', $this->storeId);
        $this->defConfParams['addtrack'] = $this->_conf->getStoreConfig('fedexlabel/shipping/addtrack', $this->storeId);
        $this->defConfParams['currencycode'] = $this->_conf->getStoreConfig('fedexlabel/ratepayment/currencycode',
            $this->storeId);
        $this->defConfParams['cod'] = $this->_conf->getStoreConfig('fedexlabel/ratepayment/cod', $this->storeId) == 1 ?
            1 : (($this->paymentmethod == 'cashondelivery' || $this->paymentmethod == 'phoenix_cashondelivery') ?
                1 : 0);
        $this->defConfParams['codmonetaryvalue'] = $this->shipmentTotalPrice;
        $this->defConfParams['default_return'] = ($this->_conf->getStoreConfig('fedexlabel/return/default_return', $this->storeId) == 0 || $this->_conf->getStoreConfig('fedexlabel/return/default_return_amount', $this->storeId) > $this->shipmentTotalPrice) ? 0 : 1;
        $this->defConfParams['default_return_servicecode'] = $shippingInternational == 0 ? $this->_conf->getStoreConfig('fedexlabel/return/default_return_method', $this->storeId) : $this->_conf->getStoreConfig('fedexlabel/return/default_return_method_inter', $this->storeId);
        $this->defConfParams['qvn'] = $this->_conf->getStoreConfig('fedexlabel/quantum/qvn', $this->storeId);
        $this->defConfParams['qvn_code'] = explode(",", $this->_conf->getStoreConfig('fedexlabel/quantum/qvn_code', $this->storeId));
        $this->defConfParams['qvn_email_shipper'] = $this->_conf->getStoreConfig('fedexlabel/quantum/qvn_email_shipper', $this->storeId);
        $this->defConfParams['qvn_email_shipto'] = $this->shippingAddress->getEmail();
        $this->defConfParams['adult'] = $this->_conf->getStoreConfig('fedexlabel/quantum/adult', $this->storeId);
        $this->defConfParams['weightunits'] = $this->_conf->getStoreConfig('fedexlabel/weightdimension/weightunits', $this->storeId);
        $this->defConfParams['unitofmeasurement'] = $this->_conf->getStoreConfig('fedexlabel/weightdimension/unitofmeasurement', $this->storeId);
        $this->defConfParams['residentialaddress'] = $this->_conf->getStoreConfig('fedexlabel/shipping/customer_residential', $this->storeId);
        $this->defConfParams['shiptocompanyname'] = strlen($this->shippingAddress->getCompany()) > 0 ? $this->shippingAddress->getCompany() : $this->shippingAddress->getFirstname() . ' ' . $this->shippingAddress->getLastname();
        $this->defConfParams['shiptoattentionname'] = $this->shippingAddress->getFirstname() . ' ' . $this->shippingAddress->getLastname();
        $this->defConfParams['shiptophonenumber'] = $this->_conf->escapePhone($this->shippingAddress->getTelephone());
        $addressLine1 = $this->shippingAddress->getStreet();
        $this->defConfParams['shiptoaddressline1'] = is_array($addressLine1) && array_key_exists(0, $addressLine1) ? $addressLine1[0] : $addressLine1;
        $this->defConfParams['shiptoaddressline2'] = (is_array($addressLine1) && isset($addressLine1[1])) ? $addressLine1[1] : '';
        $this->defConfParams['shiptocity'] = $this->shippingAddress->getCity();
        $this->defConfParams['shiptostateprovincecode'] = $this->shippingAddress->getRegion();
        $this->defConfParams['shiptocountrycode'] = $this->shippingAddress->getCountryId();
        if ($this->shippingAddress->getCountryId() == 'JP') {
            $this->defConfParams['shiptopostalcode'] = str_replace("-", "", $this->shippingAddress->getPostcode());
            $this->defConfParams['shiptopostalcode'] = substr($this->defConfParams['shiptopostalcode'], 0, 3) . '-' . substr($this->defConfParams['shiptopostalcode'], 3);
        } else {
            $this->defConfParams['shiptopostalcode'] = $this->shippingAddress->getPostcode();
        }

        $this->defConfParams['saturday_delivery'] = $this->_conf->getStoreConfig('fedexlabel/shipping/saturday_delivery', $this->storeId) == 0 ? 0 : 1;
        $this->defConfParams['insured_automaticaly'] = $this->_conf->getStoreConfig('fedexlabel/ratepayment/insured_automaticaly', $this->storeId);

        $this->defConfParams['international_comments'] = $this->_conf->getStoreConfig('fedexlabel/paperless/international_comments', $this->storeId);
        $this->defConfParams['international_invoicenumber'] = $this->order->getIncrementId();
        $this->defConfParams['international_invoicedate'] = date("d F Y", time());
        $this->defConfParams['international_reasonforexport'] = $this->_conf->getStoreConfig('fedexlabel/paperless/reasonforexport', $this->storeId);
        $this->defConfParams['international_reasonforexport_desc'] = $this->_conf->getStoreConfig('fedexlabel/paperless/reasonforexport_desc', $this->storeId);
        $this->defConfParams['international_reasonforexport_return'] = $this->_conf->getStoreConfig('fedexlabel/paperless/reasonforexport_return', $this->storeId);
        $this->defConfParams['international_reasonforexport_return_desc'] = $this->_conf->getStoreConfig('fedexlabel/paperless/reasonforexport_return_desc', $this->storeId);
        $this->defConfParams['international_purchaseordernumber'] = $this->order->getIncrementId();
        $this->defConfParams['international_termsofsale'] = $this->_conf->getStoreConfig('fedexlabel/paperless/international_termsofsale', $this->storeId);
        $this->defConfParams['purpose_of_shipment'] = $this->_conf->getStoreConfig('fedexlabel/paperless/purpose_of_shipment', $this->storeId);
        $this->defConfParams['isElectronicTradeDocuments'] = $this->_conf->getStoreConfig('fedexlabel/paperless/enable', $this->storeId);

        $this->_eventManager->dispatch('infomodus_fedexlabel_set_def_params', ['handy' => $this]);

        return true;
    }

    public function getProductSizes($item, $itemData, $product, &$packer, $attributeWidth, $attributeHeight, $attributeLength, $currencyKoef)
    {
        $children = $item->getChildrenItems();

        $isSize = 0;
        $productType = $this->productRepository->getById($itemData['product_id'])->getTypeId();

        if ($productType != "virtual") {
            if (
                isset($product[$attributeWidth]) && $product[$attributeWidth] != "" && $product[$attributeWidth] > 0
                && isset($product[$attributeHeight]) && $product[$attributeHeight] != "" && $product[$attributeHeight] > 0
                && isset($product[$attributeLength]) && $product[$attributeLength] != "" && $product[$attributeLength] > 0
            ) {
                $packer->addItem(
                    new PackerItem(
                        $item->getBasePrice() * $currencyKoef,
                        $product[$attributeWidth]*1000,
                        $product[$attributeLength]*1000,
                        $product[$attributeHeight]*1000,
                        $product['weight']*1000,
                        true,
                        $itemData
                    )
                );
                $isSize++;
            }
        }

        if ($children && count($children) > 0) {
            foreach ($children as $child) {
                $productChildOrigin = $this->productRepository->getById($child->getProduct()->getId());
                $productChild = $productChildOrigin->getData();
                $productType = $productChildOrigin->getTypeId();

                if ($productType != "virtual") {
                    if (
                        isset($productChild[$attributeWidth]) && $productChild[$attributeWidth] != "" && $productChild[$attributeWidth] > 0
                        && isset($productChild[$attributeHeight]) && $productChild[$attributeHeight] != "" && $productChild[$attributeHeight] > 0
                        && isset($productChild[$attributeLength]) && $productChild[$attributeLength] != "" && $productChild[$attributeLength] > 0
                    ) {
                        $packer->addItem(
                            new PackerItem(
                                0,
                                $productChild[$attributeWidth]*1000,
                                $productChild[$attributeLength]*1000,
                                $productChild[$attributeHeight]*1000,
                                0.001*1000,
                                true,
                                $itemData
                            )
                        );
                        $isSize++;
                    }
                }
            }
        }

        if ($isSize > 0) {
            return $product;
        }

        return false;
    }

    protected function getAttributeContent($productOriginObj, $code)
    {
        $value = $productOriginObj->getAttributeText($code);
        if (empty($value)) {
            $value = $productOriginObj->getData($code);
        }

        return $value;
    }

    public function setPackageDefParams($itemData = null)
    {
        $defParArr_1['packagingreferencetype'] = $this->_conf->getStoreConfig('fedexlabel/packaging/packagingreference_type', $this->storeId);
        $defParArr_1['packagingreferencenumbervalue'] = $this->macropaste($this->_conf->getStoreConfig('fedexlabel/packaging/packagingreferencenumbervalue', $this->storeId), ['sku' => (!empty($itemData['sku']) ? $itemData['sku'] : [])]);
        $defParArr_1['packagingreferencetypereturn'] = $this->_conf->getStoreConfig('fedexlabel/return/packagingreference_type', $this->storeId);
        $defParArr_1['packagingreferencenumbervaluereturn'] = $this->macropaste($this->_conf->getStoreConfig('fedexlabel/return/packagingreferencenumbervalue', $this->storeId), ['sku' => (!empty($itemData['sku']) ? $itemData['sku'] : [])]);
        $defParArr_1['weight'] = ($itemData !== null && isset($itemData['weight'])) ? $itemData['weight'] : $this->totalWeight;
        $defParArr_1['packweight'] = round((float)str_replace(',', '.', $this->_conf->getStoreConfig('fedexlabel/weightdimension/packweight', $this->storeId)), 1) > 0 ? round((float)str_replace(',', '.', $this->_conf->getStoreConfig('fedexlabel/weightdimension/packweight', $this->storeId)), 1) : '0';
        $defParArr_1['width'] = $itemData !== null && isset($itemData['width']) ? $itemData['width'] : '';
        $defParArr_1['height'] = $itemData !== null && isset($itemData['height']) ? $itemData['height'] : '';
        $defParArr_1['length'] = $itemData !== null && isset($itemData['length']) ? $itemData['length'] : '';
        $defParArr_1['cod'] = $this->_conf->getStoreConfig('fedexlabel/ratepayment/cod', $this->storeId) == 1 ? 1 : (($this->paymentmethod == 'cashondelivery' || $this->paymentmethod == 'phoenix_cashondelivery') ? 1 : 0);
        $defParArr_1['insured_automaticaly'] = $this->_conf->getStoreConfig('fedexlabel/ratepayment/insured_automaticaly', $this->storeId);
        $defParArr_1['codmonetaryvalue'] = ($itemData !== null && isset($itemData['price'])) ? $itemData['price'] : $this->shipmentTotalPrice;
        $defParArr_1['box'] = $defParArr_1['width'] . 'x' . $defParArr_1['height'] . 'x' . $defParArr_1['length'];
        return ($defParArr_1);
    }

    public function macropaste($value, $additionalData = [])
    {
        $sku = substr(implode(",", array_unique($this->sku)), 0, 40);
        if (!empty($additionalData['sku'])) {
            $sku = substr(implode(",", array_unique($additionalData['sku'])), 0, 40);
        }

        return str_replace(
            ["#order_id#", "#customer_name#", "#sku#"],
            [$this->order->getIncrementId(), $this->shippingAddress->getFirstname() . ' ' . $this->shippingAddress->getLastname(), $sku],
            $value
        );
    }

    public
    function getShipRate($lbl)
    {
        return $lbl->getShipRate();
    }

    public
    function getLabel($order, $type, $params, $shipment_id = null)
    {
        if ($this->order === null) {
            $this->order = $order;
        }
        unset($order);
        $this->type = $type;
        unset($type);
        $this->shipment_id = $shipment_id;
        unset($shipment_id);
        if ($this->shipment_id !== null) {
            $this->shipment = $this->shipmentRepository->get($this->shipment_id);
        }

        $this->storeId = $this->order->getStoreId();

        $lbl = $this->fedexModelFactory->create();

        $lbl = $this->setParams($lbl, $params, $params['package']);
        $dhll2 = null;
        if ($this->type == 'shipment' || $this->type == 'invert') {
            $dhll = $lbl->getShip($this->type, $this->storeId);
            if (isset($params['default_return']) && $params['default_return'] == 1) {
                $lbl->serviceCode = array_key_exists('default_return_servicecode', $params) ? $params['default_return_servicecode'] : '';

                /*if (isset($params['return_methods'])) {
                    $shippingMethods = json_decode($params['return_methods'], true);
                    if (isset($shippingMethods['global'])) {
                        $lbl->serviceLocalCode = array_key_exists('return_methods', $params) ?
                            $shippingMethods['local'][array_search($lbl->serviceCode, $shippingMethods['global'])] : '';
                    }
                }*/
                $dhll2 = $lbl->getShipFrom($this->type, $this->storeId);
            }
        } elseif ($this->type == 'refund') {
            $dhll = $lbl->getShipFrom($this->type, $this->storeId);
        } else {
            return false;
        }

        return $this->saveDB($dhll, $dhll2, $params, $lbl);
    }

    public
    function setParams($lbl, $params, $packages)
    {
        if ($lbl === null) {
            $lbl = $this->fedexModelFactory->create();
        }
        $configOptions = $this->configOptions;
        $lbl->_handy = $this;
        $lbl->packages = $packages;

        $lbl->UserID = $this->_conf->getStoreConfig('fedexlabel/credentials/userid', $this->storeId);
        $lbl->Password = $this->_conf->getStoreConfig('fedexlabel/credentials/password', $this->storeId);
        $lbl->shipperNumber = $this->_conf->getStoreConfig('fedexlabel/credentials/shippernumber', $this->storeId);
        $lbl->meterNumber = $this->_conf->getStoreConfig('fedexlabel/credentials/meter_number', $this->storeId);

        $address = $this->addresses->getAddressesById($params['shipper_no']);

        if (empty($address)) {
            return $lbl;
        }

        $lbl->shipperName = $this->_conf->escapeXML($address->getCompany());
        $lbl->shipperAttentionName = $this->_conf->escapeXML($address->getAttention());
        $lbl->shipperPhoneNumber = $this->_conf->escapeXML($address->getPhone());
        $lbl->shipperAddressLine1 = $this->_conf->escapeXML($address->getStreetOne());
        $lbl->shipperAddressLine2 = $this->_conf->escapeXML($address->getStreetTwo());
        $lbl->shipperCity = $this->_conf->escapeXML($address->getCity());
        $lbl->shipperStateProvinceCode = $this->_conf->escapeXML($address->getProvinceCode());
        $lbl->shipperPostalCode = $this->_conf->escapeXML($address->getPostalCode());
        $lbl->shipperCountryCode = $this->_conf->escapeXML($address->getCountry());
        $lbl->shipperResidential = $this->_conf->escapeXML($address->getResidential());
        $lbl->shipperTinType = $this->_conf->escapeXML($address->getTinType());
        $lbl->shipperTinNumber = $this->_conf->escapeXML($address->getTinNumber());

        $lbl->shiptoCompanyName = $this->_conf->escapeXML($params['shiptocompanyname']);
        $lbl->shiptoAttentionName = $this->_conf->escapeXML($params['shiptoattentionname']);
        $lbl->shiptoPhoneNumber = $this->_conf->escapeXML($params['shiptophonenumber']);
        $lbl->shiptoAddressLine1 = trim($this->_conf->escapeXML($params['shiptoaddressline1']));
        $lbl->shiptoAddressLine2 = trim($this->_conf->escapeXML($params['shiptoaddressline2']));
        $lbl->shiptoCity = $this->_conf->escapeXML($params['shiptocity']);
        $lbl->shiptoStateProvinceCode = $this->_conf->escapeXML($configOptions->getProvinceCode($params['shiptostateprovincecode'], $params['shiptocountrycode']));
        $lbl->shiptoPostalCode = $this->_conf->escapeXML($params['shiptopostalcode']);
        $lbl->shiptoCountryCode = $this->_conf->escapeXML($params['shiptocountrycode']);
        $lbl->residentialAddress = isset($params['residentialaddress']) ? $params['residentialaddress'] : 1;

        $soldToIdAddress = $params['soldto_address'];
        if (!empty($soldToIdAddress) && $soldToIdAddress != 0) {
            $address = $this->addresses->getAddressesById($params['soldto_address']);

            if (empty($address)) {
                return $lbl;
            }

            $lbl->soldtoName = $this->_conf->escapeXML($address->getCompany());
            $lbl->soldtoAttentionName = $this->_conf->escapeXML($address->getAttention());
            $lbl->soldtoPhoneNumber = $this->_conf->escapeXML($address->getPhone());
            $lbl->soldtoAddressLine1 = $this->_conf->escapeXML($address->getStreetOne());
            $lbl->soldtoAddressLine2 = $this->_conf->escapeXML($address->getStreetTwo());
            $lbl->soldtoCity = $this->_conf->escapeXML($address->getCity());
            $lbl->soldtoStateProvinceCode = $this->_conf->escapeXML($address->getProvinceCode());
            $lbl->soldtoPostalCode = $this->_conf->escapeXML($address->getPostalCode());
            $lbl->soldtoCountryCode = $this->_conf->escapeXML($address->getCountry());
            $lbl->soldtoResidential = $this->_conf->escapeXML($address->getResidential());
        }

        $lbl->serviceCode = array_key_exists('serviceCode', $params) ? $params['serviceCode'] : '';

        $lbl->weightUnits = array_key_exists('weightunits', $params) ? $params['weightunits'] : '';

        $lbl->unitOfMeasurement = array_key_exists('unitofmeasurement', $params) ? $params['unitofmeasurement'] : '';

        $lbl->adult = $this->_conf->escapeXML($params['adult']);
        $lbl->dropoff = $this->_conf->escapeXML($params['dropoff']);

        $lbl->codYesNo = array_key_exists('cod', $params) ? $params['cod'] : '';
        $lbl->currencyCode = array_key_exists('currencycode', $params) ? $this->mappingCurrencyCode($params['currencycode']) : '';
        $lbl->codMonetaryValue = array_key_exists('codmonetaryvalue', $params) ? $params['codmonetaryvalue'] : '';

        /*$this->allowedCurrencies = $this->_currencyFactory->create()->getConfigAllowCurrencies();
        $responseCurrencyCode = $this->mappingCurrencyCode($this->_conf->getStoreConfig('currency/options/base', $this->storeId));
        $lbl->currencyCoefficient = 1;
        if ($responseCurrencyCode) {
            if (in_array($responseCurrencyCode, $this->allowedCurrencies) && in_array($lbl->currencyCode, $this->allowedCurrencies)) {
                $lbl->currencyCoefficient = $this->_getBaseCurrencyKoef($lbl->currencyCode, $responseCurrencyCode);
            }
        }*/

        $lbl->packagingType = array_key_exists('packagingtypecode', $params) ? $params['packagingtypecode'] : '';

        $lbl->saturdayDelivery = isset($params['saturday_delivery']) ? $params['saturday_delivery'] : 0;

        if (array_key_exists('qvn', $params) && $params['qvn'] > 0) {
            $lbl->qvn = 1;
            $lbl->qvn_code = isset($params['qvn_code']) ? $params['qvn_code'] : "";
        }

        $lbl->qvn_email_shipper = $params['qvn_email_shipper'];
        $lbl->qvn_email_shipto = $params['qvn_email_shipto'];

        $lbl->customerFedexShippingNumber = '';

        $lbl->fedexAccount = $params['fedexaccount'];
        if ($params['fedexaccount'] != "S" && $params['fedexaccount'] != "R") {
            $lbl->fedexAccount = 1;
            $lbl->accountData = $this->accountFactory->create()->load($params['fedexaccount']);
        } else {
            $lbl->fedexAccounts = 0;
            $lbl->accountData = $params['fedexaccount'];
        }

        $lbl->testing = $params['testing'];

        $lbl->isElectronicTradeDocuments = isset($params['isElectronicTradeDocuments']) ? $params['isElectronicTradeDocuments'] : 0;
        $lbl->internationalType = $this->_conf->getStoreConfig('fedexlabel/paperless/type', $this->storeId);
        $lbl->international_comments = isset($params['international_comments']) ? $this->_conf->escapeXML($params['international_comments']) : "";
        $lbl->international_invoicenumber = isset($params['international_invoicenumber']) ? $this->_conf->escapeXML($params['international_invoicenumber']) : "";
        $lbl->international_invoicedate = isset($params['international_invoicedate']) ? $this->_conf->escapeXML($params['international_invoicedate']) : "";
        $lbl->international_reasonforexport = isset($params['international_reasonforexport']) ? $this->_conf->escapeXML($params['international_reasonforexport']) : "";
        $lbl->international_reasonforexport_desc = isset($params['international_reasonforexport_desc']) ? $this->_conf->escapeXML($params['international_reasonforexport_desc']) : "";
        $lbl->international_purchaseordernumber = isset($params['international_purchaseordernumber']) ? $this->_conf->escapeXML($params['international_purchaseordernumber']) : "";
        $lbl->international_termsofsale = isset($params['international_termsofsale']) ? $this->_conf->escapeXML($params['international_termsofsale']) : "";
        $lbl->international_purpose_of_shipment = isset($params['purpose_of_shipment']) ? $this->_conf->escapeXML($params['purpose_of_shipment']) : "";
        $lbl->international_products = [];
        if (isset($params['invoice_product'])) {
            foreach ($params['invoice_product'] as $product) {
                if (isset($product['enabled']) && $product['enabled'] == 1) {
                    $lbl->international_products[] = $product;
                }
            }
        }
        $printerType = $this->_conf->getStoreConfig('fedexlabel/printing/printer', $this->storeId);
        $lbl->isAlcohol = $this->_conf->getStoreConfig('fedexlabel/alcohol/alcohol', $this->storeId);
        $lbl->regulatoryLabelsGenOp = $this->_conf->getStoreConfig('fedexlabel/alcohol/alcohol_gen_opt', $this->storeId);
        $lbl->recipientType = $this->_conf->getStoreConfig('fedexlabel/alcohol/alcohol_recipient_type', $this->storeId);
        $lbl->graphicImage = $printerType == "pdf" ? "PDF" : ($printerType == "png" ? "PNG" : $printerType);
        $lbl->paperSize = ($lbl->graphicImage == "PDF" || $lbl->graphicImage == "PNG") ? $this->_conf->getStoreConfig('fedexlabel/printing/printer_format_pdf', $this->storeId) : $this->_conf->getStoreConfig('fedexlabel/printing/printer_format_thermal', $this->storeId);
        $this->_eventManager->dispatch('infomodus_fedexlabel_set_params', ['params' => $params, 'addresses' => $this->addresses, 'lbl' => $lbl, 'conf' => $this->_conf]);
        return $lbl;
    }

    public
    function saveDB($fedexl, $params, $fedexl2 = null, $lbl = NULL)
    {
        if ($this->order->getId() > 0) {
            $colls2 = $this->labelModel->create()->getCollection()
                ->addFieldToFilter('order_id', $this->order->getId())->addFieldToFilter('type', $this->type)
                ->addFieldToFilter('lstatus', 1);
            if (count($colls2) > 0) {
                foreach ($colls2 as $c) {
                    $c->delete();
                }
            }

            if (is_array($fedexl) && !array_key_exists('error', $fedexl) || !$fedexl['error']) {


                foreach ($fedexl['arrResponsXML'] as $fedexl_one) {
                    
                    // fedex responsed xml file
                    $fedexlabel = $this->labelModel->create();
                    $fedexlabel->setTitle('Order ' . $this->order->getIncrementId() . ' TN' . $fedexl_one['trackingnumber']);
                    $fedexlabel->setOrderId($this->order->getId());
                    $fedexlabel->setOrderIncrementId($this->order->getIncrementId());
                    $fedexlabel->setType($this->type);
                    $fedexlabel->setType2($this->type);
                    $fedexlabel->setTrackingnumber($fedexl_one['trackingnumber']);
                    $fedexlabel->setData('responseData',  $fedexl_one['responseData']);
                    $fedexlabel->setShipmentidentificationnumber($fedexl['shipidnumber']);
                    if (!isset($fedexl_one['labelname'])) {
                        $fedexlabel->setLabelname('label' . $fedexl_one['trackingnumber'] . '.' . strtolower($fedexl_one['type_print']));
                    } else {
                        $fedexlabel->setLabelname($fedexl_one['labelname']);
                    }

                    $fedexlabel->setStatustext(__('Successfully'));
                    $fedexlabel->setTypePrint($fedexl_one['type_print']);
                    $fedexlabel->setLstatus(0);
                    if (isset($fedexl['price'], $fedexl['price']['currency'])) {
                        $fedexlabel->setPrice($fedexl['price']['price']);
                        $fedexlabel->setCurrency($fedexl['price']['currency']);
                    }

                    $fedexlabel->setStoreId($this->order->getStoreId());
                    $fedexlabel->setCreatedTime(Date("Y-m-d H:i:s"));
                    $fedexlabel->setUpdateTime(Date("Y-m-d H:i:s"));
                    if ((int)$this->_conf->getStoreConfig('fedexlabel/printing/copies', $this->storeId) > 1 && strtolower($fedexl_one['type_print']) == 'pdf') {
                        $path = $this->_conf->getBaseDir('media') . '/fedexlabel/label/';
                        $this->createCopies((int)$this->_conf->getStoreConfig('fedexlabel/printing/copies', $this->storeId), $path . 'label' . $fedexl_one['trackingnumber'] . '.' . strtolower($fedexl_one['type_print']));
                    }

                    if ($fedexlabel->save() !== FALSE
                        && $this->_conf->getStoreConfig('fedexlabel/printing/automatic_printing', $this->storeId) == 1
                        && $this->_conf->getStoreConfig('fedexlabel/printing/printer', $this->storeId) != "pdf") {
                        $this->_conf->sendPrint($fedexl_one['graphicImage'], $this->storeId);
                        $fedexlabel->setRvaPrinted(1)->save();
                    }

                    if ($this->shipment_id === null && $this->type == "shipment") {
                        if ($this->order->canShip() && count($this->order->getShipmentsCollection()) == 0) {
                            if ($this->_registry->registry('current_shipment')) {
                                $this->_registry->unregister('current_shipment');
                            }

                            $items = [];
                            foreach ($this->order->getAllItems() as $item) {
                                $items[$item->getId()] = $item->getQtyToShip();
                            }
                            $shipmentLoader = $this->shipmentLoaderFactory->create();
                            $shipmentLoader->setOrderId($this->order->getId());
                            $shipmentLoader->setShipment($items);

                            $shipment = $shipmentLoader->load();
                            if ($shipment) {
                                $shipment->register();
                                $shipment->getOrder()->setIsInProcess(true);
                                $this->shipmentRepository->save($shipment);
                                $this->orderRepository->save($shipment->getOrder());
                                /*$transactionSave = $this->_objectManager->create(
                                    'Magento\Framework\DB\Transaction'
                                );
                                $transactionSave->addObject(
                                    $shipment
                                )->addObject(
                                    $shipment->getOrder()
                                );
                                $transactionSave->save();*/
                            }

                            $this->shipment = $this->shipmentRepository->get($shipment->getId());
                            $this->shipment_id = $this->shipment->getId();
                        } else {
                            $this->shipment = $this->order->getShipmentsCollection()->getFirstItem();
                            $this->shipment_id = $this->shipment->getId();
                        }
                    }

                    $fedexlabel->setShipmentId($this->shipment_id);
                    if ($this->type == "shipment") {
                        $fedexlabel->setShipmentIncrementId($this->shipment->getIncrementId());
                    }

                    $fedexlabel->save();

                    // save response 
                    $fedexresponse = $this->fedexResponseModel->create();
                    $fedexresponse->setData('order_id', $this->order->getId());
                    $fedexresponse->setData('trackingnumber', $fedexl_one['trackingnumber']);
                    $fedexresponse->setData('stringbarcode', $fedexl_one['stringBarcode']);
                    $fedexresponse->setData('formid', $fedexl_one['formId']);
                    $fedexresponse->setData('ursaCode', $fedexl_one['ursaCode']);
                    $fedexresponse->setData('operational_instructions', $fedexl_one['instructions']);
                    
                    $fedexresponse->save();
                    
                    if (isset($fedexl_one['trackingnumberReturn'])) {
                        $fedexlabel = $this->labelModel->create();
                        $fedexlabel->setTitle('Order ' . $this->order->getIncrementId() . ' TN'
                            . $fedexl_one['trackingnumberReturn']);
                        $fedexlabel->setOrderId($this->order->getId());
                        $fedexlabel->setOrderIncrementId($this->order->getIncrementId());
                        $fedexlabel->setShipmentId($this->shipment_id);
                        if ($this->type == "shipment") {
                            $fedexlabel->setShipmentIncrementId($this->shipment->getIncrementId());
                        }

                        $fedexlabel->setType($this->type);
                        $fedexlabel->setType2('refund');
                        $fedexlabel->setTrackingnumber($fedexl_one['trackingnumberReturn']);
                        $fedexlabel->setShipmentidentificationnumber($fedexl['shipidnumber']);
                        $fedexlabel->setLabelname('label' . $fedexl_one['trackingnumberReturn'] . '.'
                            . strtolower($fedexl_one['type_printReturn']));
                        $fedexlabel->setStatustext(__('Successfully'));
                        $fedexlabel->setTypePrint($fedexl_one['type_printReturn']);
                        $fedexlabel->setLstatus(0);
                        $fedexlabel->setStoreId($this->order->getStoreId());
                        $fedexlabel->setCreatedTime(Date("Y-m-d H:i:s"));
                        $fedexlabel->setUpdateTime(Date("Y-m-d H:i:s"));
                        if ($fedexlabel->save() !== FALSE
                            && $this->_conf->getStoreConfig('fedexlabel/printing/automatic_printing', $this->storeId) == 1
                            && $this->_conf->getStoreConfig('fedexlabel/printing/printer', $this->storeId) != "pdf"
                        ) {
                            $this->_conf->sendPrint($fedexl_one['graphicImageReturn'], $this->storeId);
                            $fedexlabel->setRvaPrinted(1)->save();
                        }
                    }

                    $this->label[] = $fedexlabel;
                }

                if (isset($params['addtrack']) && $params['addtrack'] == 1 && $this->type == 'shipment') {
                    $trTitle = 'FedEx';
                    if ($this->shipment) {
                        foreach ($fedexl['arrResponsXML'] as $fedexl_one1) {
                            $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\Track')
                                ->setNumber(trim($fedexl_one1['trackingnumber']))
                                ->setCarrierCode('fedex')
                                ->setTitle($trTitle);
                            $this->shipment->addTrack($track);
                            $this->shipmentRepository->save($this->shipment);
                        }

                        if ($this->_conf->getStoreConfig('fedexlabel/shipping/track_send',
                                $this->storeId) == 1) {
                            $this->_objectManager->create('Magento\Sales\Model\Service\ShipmentService')
                                ->notify($this->shipment->getId());
                        }
                    }
                }

                if (isset($params['default_return']) && $params['default_return'] == 1) {
                    if (isset($fedexl2) && !empty($fedexl2) && is_array($fedexl2) && (!array_key_exists('error', $fedexl2) || !$fedexl2['error'])) {
                        foreach ($fedexl2['arrResponsXML'] as $fedexl_one) {
                            $fedexlabel = $this->labelModel->create();
                            $fedexlabel->setTitle('Order ' . $this->order->getIncrementId() . ' TN' . $fedexl_one['trackingnumber']);
                            $fedexlabel->setOrderId($this->order->getId());
                            $fedexlabel->setOrderIncrementId($this->order->getIncrementId());
                            $fedexlabel->setShipmentId($this->shipment_id);
                            if ($this->type == "shipment") {
                                $fedexlabel->setShipmentIncrementId($this->shipment->getIncrementId());
                            }

                            $fedexlabel->setType($this->type);
                            $fedexlabel->setType2('refund');
                            $fedexlabel->setTrackingnumber($fedexl_one['trackingnumber']);
                            $fedexlabel->setShipmentidentificationnumber($fedexl2['shipidnumber']);
                            if (!isset($fedexl_one['labelname'])) {
                                $fedexlabel->setLabelname('label' . $fedexl_one['trackingnumber']
                                    . '.' . strtolower($fedexl_one['type_print']));
                            } else {
                                $fedexlabel->setLabelname($fedexl_one['labelname']);
                            }

                            $fedexlabel->setStatustext(__('Successfully'));
                            $fedexlabel->setTypePrint($fedexl_one['type_print']);
                            $fedexlabel->setLstatus(0);
                            if (isset($fedexl2['price'], $fedexl2['price']['currency'])) {
                                $fedexlabel->setPrice($fedexl2['price']['price']);
                                $fedexlabel->setCurrency($fedexl2['price']['currency']);
                            }

                            $fedexlabel->setCreatedTime(Date("Y-m-d H:i:s"));
                            $fedexlabel->setUpdateTime(Date("Y-m-d H:i:s"));
                            if ($fedexlabel->save() !== FALSE
                                && $this->_conf->getStoreConfig('fedexlabel/printing/automatic_printing', $this->storeId) == 1) {
                                $this->_conf->sendPrint($fedexl_one['graphicImage'], $this->storeId);
                                $fedexlabel->setRvaPrinted(1)->save();
                            }

                            $this->label2[] = $fedexlabel;
                        }
                        if (isset($params['addtrack']) && $params['addtrack'] == 1 && $this->type == 'shipment') {
                            $trTitle = 'FedEx (return)';
                            if ($this->shipment) {
                                foreach ($fedexl2['arrResponsXML'] as $fedexl_one1) {
                                    $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\Track')
                                        ->setNumber(trim($fedexl_one1['trackingnumber']))
                                        ->setCarrierCode('fedex')
                                        ->setTitle($trTitle);
                                    $this->shipment->addTrack($track);
                                    $this->shipmentRepository->save($this->shipment);
                                }

                                if ($this->_conf->getStoreConfig('fedexlabel/shipping/track_send',
                                        $this->storeId) == 1) {
                                    $this->_objectManager->create('Magento\Sales\Model\Service\ShipmentService')
                                        ->notify($this->shipment->getId());
                                }
                            }
                        }
                    } else if ($fedexl2 !== null) {
                        $fedexlabel = $this->labelModel->create();
                        $fedexlabel->setTitle('Order ' . $this->order->getId());
                        $fedexlabel->setOrderId($this->order->getId());
                        $fedexlabel->setShipmentId($this->shipment_id);
                        $fedexlabel->setType($this->type);
                        $fedexlabel->setType2('refund');
                        $fedexlabel->setStatustext($fedexl2['error']['desc']);
                        $fedexlabel->setLstatus(1);
                        $fedexlabel->setXmllog(json_encode($fedexl2['error']['request']) . json_encode($fedexl2['error']['response']));
                        $fedexlabel->setStoreId($this->order->getStoreId());
                        $fedexlabel->setCreatedTime(Date("Y-m-d H:i:s"));
                        $fedexlabel->setUpdateTime(Date("Y-m-d H:i:s"));
                        $fedexlabel->save();
                        $this->label2[] = $fedexlabel;
                    }
                }

                if ($this->_conf->getStoreConfig('fedexlabel/additional_settings/orderstatuses', $this->storeId) != '') {
                    $this->order->setStatus($this->_conf
                        ->getStoreConfig('fedexlabel/additional_settings/orderstatuses', $this->storeId), true);
                    $this->order->save();
                }

                return true;
            } else {
                $fedexlabel = $this->labelModel->create();
                $fedexlabel->setTitle('Order ' . $this->order->getIncrementId());
                $fedexlabel->setOrderId($this->order->getId());
                $fedexlabel->setOrderIncrementId($this->order->getIncrementId());
                $fedexlabel->setShipmentId($this->shipment_id);
                $fedexlabel->setType($this->type);
                $fedexlabel->setType2($this->type);
                $fedexlabel->setStatustext($fedexl['error']['desc']);
                $fedexlabel->setXmllog(json_encode($fedexl['error']['request']) . json_encode($fedexl['error']['response']));
                $fedexlabel->setLstatus(1);
                $fedexlabel->setStoreId($this->order->getStoreId());
                $fedexlabel->setCreatedTime(Date("Y-m-d H:i:s"));
                $fedexlabel->setUpdateTime(Date("Y-m-d H:i:s"));
                $fedexlabel->save();
                $this->label[] = $fedexlabel;
            }

            return true;
        }

        return false;
    }

    public
    function deleteLabel($shipidnumber = null, $type = 'shipidnumber')
    {
        if ($shipidnumber !== null) {
            if ($type == 'label_ids') {
                $labels = $this->labelModel->create()->getCollection()->addFieldToFilter('fedexlabel_id', ['in' => $shipidnumber]);
            } else if ($type == 'shipidnumber') {
                $labels = $this->labelModel->create()->getCollection()->addFieldToFilter('shipment_id', ['in' => $shipidnumber]);
            }

            if (count($labels) > 0) {
                $this->order = null;
                $this->shipment_id = null;
                foreach ($labels as $model) {
                    if ($this->order === null) {
                        $this->order = $this->orderRepository->get($model->getOrderId());
                    }

                    if ($model->getShipmentId() > 0 && $this->shipment_id === null) {
                        $this->shipment_id = $model->getShipmentId();
                    }

                    if ($model->getLstatus() == 0) {
                        if ($model->getLabelname() != ""
                            && file_exists($this->_conf->getBaseDir('media') . '/fedexlabel/label/' . $model->getLabelname())) {
                            unlink($this->_conf->getBaseDir('media') . '/fedexlabel/label/' . $model->getLabelname());
                        }

                        if (file_exists($this->_conf->getBaseDir('media') . '/fedexlabel/label/invoice' . $model->getTrackingnumber() . '.pdf')) {
                            unlink($this->_conf->getBaseDir('media') . '/fedexlabel/label/invoice' . $model->getTrackingnumber() . '.pdf');
                        }

                        $fedexModel = $this->fedexModelFactory->create();
                        $fedexModel->meterNumber = $this->_conf->getStoreConfig('fedexlabel/credentials/meter_number', $model->getStoreId());
                        $fedexModel->UserID = $this->_conf->getStoreConfig('fedexlabel/credentials/userid', $model->getStoreId());
                        $fedexModel->Password = $this->_conf->getStoreConfig('fedexlabel/credentials/password', $model->getStoreId());
                        $fedexModel->shipperNumber = $this->_conf->getStoreConfig('fedexlabel/credentials/shippernumber', $model->getStoreId());
                        $fedexModel->testing = $this->_conf->getStoreConfig('fedexlabel/testmode/testing', $model->getStoreId());
                        $data[] = array(
                            'tracking_number' => $model->getTrackingnumber(),
                        );

                        $fedexModel->rollBack($data);
                        if ($model->getShipmentId() > 0) {
                            $shipm = $this->shipmentRepository->get($model->getShipmentId());
                            $tracks = $shipm->getAllTracks();
                            foreach ($tracks as $track) {
                                if ($track->getNumber() == $model->getTrackingnumber()) {
                                    $track->delete();
                                }
                            }
                        }
                    }

                    $model->delete();
                }

                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public
    function createCopies($count, $file)
    {

        $pdf2show = new \Zend_Pdf();
        if (file_exists($file)) {
            $pdf2 = \Zend_Pdf::load($file);
            foreach ($pdf2->pages as $k => $page) {
                $template2 = clone $pdf2->pages[$k];
                $page2 = new \Zend_Pdf_Page($template2);
                for ($i = 0; $i < $count; $i++) {
                    $pdf2show->pages[] = $page2;
                }
            }
        }

        $pdfData = $pdf2show->render();
        file_put_contents($file, $pdfData);
    }

    protected function _getBaseCurrencyKoef($from, $to)
    {
        return $this->_currencyFactory->create()->load(
            $from
        )->getAnyRate(
            $to
        );
    }

    private function mappingCurrencyCode($code)
    {
        $currencyMapping = [
            'RMB' => 'CNY',
            'CHF' => 'SFR',
            'JPY' => 'JYE',
            'GBP' => 'UKL',
            'CNH' => 'CNY',
            'AED' => 'DHS',
        ];

        return isset($currencyMapping[$code]) ? $currencyMapping[$code] : $code;
    }
}
