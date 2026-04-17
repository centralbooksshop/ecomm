<?php

namespace Infomodus\Fedexlabel\Model;

use Magento\Framework\Module\Dir;
use Magento\Framework\Xml\Security;

class Fedex extends \Magento\Fedex\Model\Carrier
{
    public $soapLocation;
    protected $_code = 'fedexlabel';
    private $masterTrackingId = null;

    public $UserID;
    public $Password;
    public $shipperNumber;
    public $meterNumber;

    public $packages;
    public $weightUnits;
    public $dropoff;
    public $packagingType;

    public $unitOfMeasurement;
    public $length;
    public $width;
    public $height;

    public $shipperName;
    public $shipperPhoneNumber;
    public $shipperAddressLine1;
    public $shipperAddressLine2;
    public $shipperCity;
    public $shipperStateProvinceCode;
    public $shipperPostalCode;
    public $shipperCountryCode;
    public $shipperAttentionName;
    public $shipperResidential;
    public $shipperTinType;
    public $shipperTinNumber;

    public $shiptoCompanyName;
    public $shiptoAttentionName;
    public $shiptoPhoneNumber;
    public $shiptoAddressLine1;
    public $shiptoAddressLine2;
    public $shiptoCity;
    public $shiptoStateProvinceCode;
    public $shiptoPostalCode;
    public $shiptoCountryCode;
    public $residentialAddress;

    public $soldtoName;
    public $soldtoPhoneNumber;
    public $soldtoAddressLine1;
    public $soldtoAddressLine2;
    public $soldtoCity;
    public $soldtoStateProvinceCode;
    public $soldtoPostalCode;
    public $soldtoCountryCode;
    public $soldtoAttentionName;
    public $soldtoResidential;

    public $serviceCode;

    public $saturdayDelivery;

    public $trackingNumber;
    public $graphicImage = "PDF";
    public $paperSize = "";

    public $codYesNo;
    public $currencyCode;
    public $currencyCoefficient = 1;
    public $codMonetaryValue;
    public $testing;
    /*public $shipmentcharge = 0;*/
    public $qvn = 0;
    public $qvn_code = [];
    public $qvn_email_shipper = '';
    public $qvn_email_shipto = '';
    public $adult;
    public $fedexAccount = 'S';
    public $accountData;
    public $international_invoice = 0;
    public $international_description;
    public $international_comments;
    public $international_invoicenumber;
    public $international_reasonforexport;
    public $international_reasonforexport_desc;
    public $international_termsofsale;
    public $international_purchaseordernumber;
    public $international_products;
    public $international_invoicedate;
    public $international_purpose_of_shipment;

    public $isElectronicTradeDocuments = 1;

    /* Pickup */
    public $RatePickupIndicator;
    public $CloseTime;
    public $ReadyTime;
    public $PickupDateYear;
    public $PickupDateMonth;
    public $PickupDateDay;
    public $AlternateAddressIndicator;
    public $ServiceCode;
    public $Quantity;
    public $DestinationCountryCode;
    public $ContainerCode;
    public $Weight;
    public $OverweightIndicator;
    public $PaymentMethod;
    public $SpecialInstruction;
    public $ReferenceNumber;
    public $Notification;
    public $ConfirmationEmailAddress;
    public $UndeliverableEmailAddress;
    public $room;
    public $floor;
    public $urbanization;
    public $residential;
    public $pickup_point;
    /* END Pickup */

    public $storeId = null;

    public $_conf;
    public $accountFactory;
    public $managerInterface;
    public $requestClient;
    public $isAlcohol;
    public $regulatoryLabelsGenOp = 'CONTENT_ON_SHIPPING_LABEL_ONLY';
    public $recipientType = 'LICENSEE';
    protected $eventManager;
    private $fedexItemsFactory;
    private $fedexlabelCollection;

    public function __construct(
        \Infomodus\Fedexlabel\Model\ResourceModel\Items\CollectionFactory $fedexlabelCollection,
        \Infomodus\Fedexlabel\Model\ItemsFactory $fedexItemsFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Dir\Reader $configReader,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Infomodus\Fedexlabel\Helper\Config $config,
        \Infomodus\Fedexlabel\Model\AccountFactory $accountFactory,
        \Magento\Framework\Message\ManagerInterface $managerInterface,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $data = []
    )
    {
        $this->fedexlabelCollection = $fedexlabelCollection;
        $this->fedexItemsFactory = $fedexItemsFactory;
        $this->_storeManager = $storeManager;
        $this->_conf = $config;
        $this->managerInterface = $managerInterface;
        $this->eventManager = $eventManager;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $storeManager,
            $configReader,
            $productCollectionFactory,
            $data
        );
        $wsdlBasePath = $configReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Infomodus_Fedexlabel') . '/wsdl/FedEx/';
        $this->_shipServiceWsdl = $wsdlBasePath . 'ShipService_v21.wsdl';
        $this->_rateServiceWsdl = $wsdlBasePath . 'RateService_v20.wsdl';
        $this->_imageServiceWsdl = $wsdlBasePath . 'UploadDocumentService_v8.wsdl';
        $this->accountFactory = $accountFactory;
        $this->_conf->createMediaFolders();
    }

    public function getShip($type = "shipment")
    {
        $data = [];
        $isFirstRequest = true;
        $totalWeight = 0;
        $arrResponsXML = [];
        $valueToDb = [];
        $debugData = ['result' => []];
        $debugData['result']['code'] = '';
        $debugData['result']['error'] = '';
        if (!isset($valueToDb['price']['price'])) {
            $valueToDb['price']['price'] = 0;
        }

        foreach ($this->packages as $k => $package) {
            if (isset($package['weight']) && $package['weight'] > 0) {
                if (empty($package['packweight']) || !is_numeric($package['packweight'])) {
                    $package['packweight'] = 0;
                }

                $totalWeight += round((float)$package['weight'] + (float)$package['packweight'], 2);
            }
        }

        if ($type == "shipment") {
            $shipper = [
                'Contact' => [
                    'PersonName' => $this->shipperAttentionName,
                    'CompanyName' => $this->shipperName,
                    'PhoneNumber' => $this->shipperPhoneNumber,
                    'EMailAddress' => $this->qvn_email_shipper
                ],
                'Address' => [
                    'StreetLines' => [
                        $this->shipperAddressLine1,
                        $this->shipperAddressLine2
                    ],
                    'City' => $this->shipperCity,
                    'StateOrProvinceCode' => $this->shipperStateProvinceCode,
                    'PostalCode' => $this->shipperPostalCode,
                    'CountryCode' => $this->shipperCountryCode
                ]
            ];
            if ($this->shipperTinType != "") {
                $shipper['Tins'] = [
                    'TinType' => $this->shipperTinType,
                    'Number' => $this->shipperTinNumber
                ];
            }

            $recipient = [
                'Contact' => [
                    'PersonName' => $this->shiptoAttentionName,
                    'CompanyName' => $this->shiptoCompanyName,
                    'PhoneNumber' => $this->shiptoPhoneNumber,
                    'EMailAddress' => $this->qvn_email_shipto
                ],
                'Address' => [
                    'StreetLines' => [
                        $this->shiptoAddressLine1,
                        $this->shiptoAddressLine2
                    ],
                    'City' => $this->shiptoCity,
                    'StateOrProvinceCode' => $this->shiptoStateProvinceCode,
                    'PostalCode' => $this->shiptoPostalCode,
                    'CountryCode' => $this->shiptoCountryCode,
                    'Residential' => (bool)$this->residentialAddress
                ],
            ];
        } else {
            $shipper = [
                'Contact' => [
                    'PersonName' => $this->shiptoAttentionName,
                    'CompanyName' => $this->shiptoCompanyName,
                    'PhoneNumber' => $this->shiptoPhoneNumber,
                    'EMailAddress' => $this->qvn_email_shipto
                ],
                'Address' => [
                    'StreetLines' => [
                        $this->shiptoAddressLine1,
                        $this->shiptoAddressLine2
                    ],
                    'City' => $this->shiptoCity,
                    'StateOrProvinceCode' => $this->shiptoStateProvinceCode,
                    'PostalCode' => $this->shiptoPostalCode,
                    'CountryCode' => $this->shiptoCountryCode,
                    'Residential' => (bool)$this->residentialAddress
                ],
            ];
            $recipient = [
                'Contact' => [
                    'PersonName' => $this->shipperAttentionName,
                    'CompanyName' => $this->shipperName,
                    'PhoneNumber' => $this->shipperPhoneNumber,
                    'EMailAddress' => $this->qvn_email_shipper
                ],
                'Address' => [
                    'StreetLines' => [
                        $this->shipperAddressLine1,
                        $this->shipperAddressLine2
                    ],
                    'City' => $this->shipperCity,
                    'StateOrProvinceCode' => $this->shipperStateProvinceCode,
                    'PostalCode' => $this->shipperPostalCode,
                    'CountryCode' => $this->shipperCountryCode,
                    'Residential' => (bool)$this->shipperResidential
                ]
            ];
            if ($this->shipperTinType != "") {
                $recipient['Tins'] = [
                    'TinType' => $this->shipperTinType,
                    'Number' => $this->shipperTinNumber
                ];
            }
        }


        foreach ($this->packages as $k => $package) {
            $packagesArray = [];
            $requestClient = [
                'RequestedShipment' => [
                    'ShipTimestamp' => time(),
                    'DropoffType' => $this->dropoff,
                    'PackagingType' => $this->packagingType,
                    'ServiceType' => $this->serviceCode,
                    'Shipper' => $shipper,
                    'Recipient' => $recipient,
                    'LabelSpecification' => [
                        'LabelFormatType' => 'COMMON2D',
                        'ImageType' => $this->graphicImage,
                        'LabelStockType' => $this->paperSize,
                    ],
                    /*'RateRequestTypes' => array('ACCOUNT'),*/
                    'PackageCount' => count($this->packages),
                ]
            ];

            if($this->serviceCode == 'SMART_POST'){
                $requestClient['RequestedShipment']['SmartPostShipmentDetail']['SmartPostShipmentProcessingOptionsRequested']['SmartPostShipmentProcessingOptionType'] = 'GROUND_TRACKING_ NUMBER_REQUESTED';
                $requestClient['RequestedShipment']['SmartPostDetail'] = [
                    'Indicia' => 'PARCEL_SELECT',
                    'AncillaryEndorsement' => 'FORWARDING_SERVICE',
                    'HubID' => (int)$this->_conf->getStoreConfig('fedexlabel/additional_settings/smart_hub_id', $this->storeId),
                ];
            }

            if ($this->shiptoCountryCode != $this->shipperCountryCode) {
                $requestClient['RequestedShipment']['EdtRequestType'] = 'ALL';
            }

            if ($this->fedexAccount == 'S') {
                $requestClient['RequestedShipment']['ShippingChargesPayment'] = [
                    'PaymentType' => 'SENDER',
                    'Payor' => ['ResponsibleParty' => [
                        'AccountNumber' => $this->shipperNumber
                    ]]
                ];
            } elseif ($this->fedexAccount == 'R') {
                $requestClient['RequestedShipment']['ShippingChargesPayment'] = [
                    'PaymentType' => 'RECIPIENT',
                    /*'Payor' => array('ResponsibleParty' => array(
                        'AccountNumber' => $this->shipperNumber
                    ))*/
                ];
            } else {
                $requestClient['RequestedShipment']['ShippingChargesPayment'] = [
                    'PaymentType' => 'THIRD_PARTY',
                    'Payor' => ['ResponsibleParty' => [
                        'AccountNumber' => $this->accountData->getAccountnumber()
                    ]]
                ];
            }

            if ($this->masterTrackingId !== null) {
                $requestClient['RequestedShipment']['MasterTrackingId']['TrackingNumber'] = $this->masterTrackingId;
            }

            if (isset($package['weight']) && $package['weight'] > 0) {
                if (!isset($package['packweight'])) {
                    $package['packweight'] = 0;
                }

                $packagesArray = [
                    'SequenceNumber' => ($k + 1),
                    'Weight' => [
                        'Units' => $this->weightUnits,
                        'Value' => round((float)$package['weight'] + (float)$package['packweight'], 2)
                    ],
                    'CustomerReferences' => [
                        [
                            'CustomerReferenceType' => $package['packagingreferencetype'],
                            'Value' => $package['packagingreferencenumbervalue']
                        ],
                    ],
                    'SpecialServicesRequested' => [
                        'SpecialServiceTypes' => ['SIGNATURE_OPTION'],
                        'SignatureOptionDetail' => ['OptionType' => $this->adult]
                    ],
                ];

                if($this->shipperCountryCode != $this->shiptoCountryCode){
                    $packagesArray['CustomerReferences'][] = [
                        'CustomerReferenceType' => 'DEPARTMENT_NUMBER',
                        'Value' => ($this->_conf
                            ->getStoreConfig('fedexlabel/ratepayment/dytytaxinternational',
                                $this->storeId) === 'shipper' ?
                            'Bill Duties : Sender' : 'Bill Duties : Recipient')
                    ];
                }
            }

            if ($this->shiptoCountryCode == 'IN') {
                $packagesArray['CustomerReferences'][] = [
                    'CustomerReferenceType' => 'INVOICE_NUMBER',
                    'Value' => $this->international_invoicenumber . 'Dated : ' . $this->international_invoicedate
                ];
            }

            if (isset($package['insured_automaticaly']) && $package['insured_automaticaly'] == 1) {
                $packagesArray['InsuredValue'] = [
                    'Currency' => $this->currencyCode,
                    'Amount' => round($package['codmonetaryvalue'] / $this->currencyCoefficient, 2)
                ];
            }

            if ($this->masterTrackingId === null) {
                $requestClient['RequestedShipment']['TotalWeight'] = [
                    'Units' => $this->weightUnits,
                    'Value' => $totalWeight
                ];
                if (isset($package['insured_automaticaly']) && $package['insured_automaticaly'] == 1) {
                    $requestClient['RequestedShipment']['TotalInsuredValue'] = ['Currency' => $this->currencyCode, 'Amount' => round($package['codmonetaryvalue'] / $this->currencyCoefficient, 2)];
                }
            }

            if (isset($package['cod']) && $package['cod'] == 1) {
                $requestClient['RequestedShipment']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'COD';
                $requestClient['RequestedShipment']['SpecialServicesRequested']['CodDetail'] = [
                    'CodCollectionAmount' => [
                        'Amount' => round($package['codmonetaryvalue'] / $this->currencyCoefficient, 2),
                        'Currency' => $this->currencyCode
                    ],
                    'CollectionType' => 'ANY'
                ];
                if ($this->shiptoCountryCode == 'IN') {
                    $requestClient['RequestedShipment']['SpecialServicesRequested']['CodDetail']['CollectionType'] = 'CASH';
                }
            }

            if(!empty($this->isAlcohol)){
                $packagesArray['SpecialServicesRequested']['SpecialServiceTypes'][] = 'ALCOHOL';
                $packagesArray['SpecialServicesRequested']['AlcoholDetail']['RecipientType'] = $this->recipientType;
                $requestClient['RequestedShipment']['LabelSpecification']['CustomerSpecifiedDetail']['RegulatoryLabels']['Type'] = 'ALCOHOL_SHIPMENT_LABEL';
                $requestClient['RequestedShipment']['LabelSpecification']['CustomerSpecifiedDetail']['RegulatoryLabels']['GeneralOptions'] = $this->regulatoryLabelsGenOp;
            }

            if ($this->qvn == 1) {
                $requestClient['RequestedShipment']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'EVENT_NOTIFICATION';
                $requestClient['RequestedShipment']['SpecialServicesRequested']['EventNotificationDetail']['AggregationType'] = 'PER_SHIPMENT';
                $requestClient['RequestedShipment']['SpecialServicesRequested']['EventNotificationDetail']['EventNotifications'] = [
                    [
                        'Role' => 'RECIPIENT',
                        'Events' => $this->qvn_code,
                        'NotificationDetail' => [
                            'NotificationType' => 'EMAIL',
                            'EmailDetail' => [
                                'EmailAddress' => $this->qvn_email_shipto,
                                'Name' => $this->shiptoAttentionName,
                            ],
                            'Localization' => [
                                'LanguageCode' => 'EN',
                            ]
                        ],
                        'FormatSpecification' => ['Type' => 'HTML'],
                    ],
                    [
                        'Role' => 'SHIPPER',
                        'Events' => $this->qvn_code,
                        'NotificationDetail' => [
                            'NotificationType' => 'EMAIL',
                            'EmailDetail' => [
                                'EmailAddress' => $this->qvn_email_shipper,
                                'Name' => $this->shipperAttentionName,
                            ],
                            'Localization' => [
                                'LanguageCode' => 'EN',
                            ]
                        ],
                        'FormatSpecification' => ['Type' => 'HTML'],
                    ]
                ];
            }

            if ($this->saturdayDelivery == 1) {
                $requestClient['RequestedShipment']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'SATURDAY_DELIVERY';
            }

            if (!empty($package['length']) && !empty($package['width']) && !empty($package['height'])) {
                $dimensions = [];
                $dimensions['Length'] = round($package['length'], 0);
                $dimensions['Width'] = round($package['width'], 0);
                $dimensions['Height'] = round($package['height'], 0);
                $dimensions['Units'] = $this->unitOfMeasurement;
                $packagesArray['Dimensions'] = $dimensions;
            }

            $requestClient['RequestedShipment']['RequestedPackageLineItems'][] = $packagesArray;

            // for international shipping
            if ($this->shipperCountryCode != $this->shiptoCountryCode) {
                if ($this->isElectronicTradeDocuments == 1) {
                    $requestClient['RequestedShipment']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'ELECTRONIC_TRADE_DOCUMENTS';
                    $requestClient['RequestedShipment']['SpecialServicesRequested']['EtdDetail']['RequestedDocumentCopies'] = $this->internationalType;
                }

                $requestClient['RequestedShipment']['ShippingDocumentSpecification'] = [
                    'ShippingDocumentTypes' => $this->internationalType,
                    'CommercialInvoiceDetail' => [
                        'Format' => [
                            'ImageType' => 'PDF',
                            'StockType' => 'PAPER_LETTER',
                            'ProvideInstructions' => '1',
                        ]
                    ]
                ];

                if(!empty($this->soldtoCountryCode)) {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['ImporterOfRecord'] = [
                        'Contact' => [
                            'PersonName' => $this->soldtoAttentionName,
                            'CompanyName' => $this->soldtoName,
                            'PhoneNumber' => $this->soldtoPhoneNumber
                        ],
                        'Address' => [
                            'StreetLines' => [
                                $this->soldtoAddressLine1,
                                $this->soldtoAddressLine2
                            ],
                            'City' => $this->soldtoCity,
                            'StateOrProvinceCode' => $this->soldtoStateProvinceCode,
                            'PostalCode' => $this->soldtoPostalCode,
                            'CountryCode' => $this->soldtoCountryCode
                        ]
                    ];
                }

                if ($this->_conf->getStoreConfig('fedexlabel/paperless/signature', $this->storeId) != '') {
                    $requestClient['RequestedShipment']['ShippingDocumentSpecification']['CommercialInvoiceDetail']['CustomerImageUsages'][] = array(
                        'Type' => 'SIGNATURE',
                        'Id' => 'IMAGE_1'
                    );
                }

                if ($this->_conf->getStoreConfig('fedexlabel/paperless/letter_head', $this->storeId) != '') {
                    $requestClient['RequestedShipment']['ShippingDocumentSpecification']['CommercialInvoiceDetail']['CustomerImageUsages'][] = array(
                        'Type' => 'LETTER_HEAD',
                        'Id' => 'IMAGE_2'
                    );
                }

                if(!empty($this->international_invoicenumber)){
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['CommercialInvoice']['CustomerReferences'] = ['CustomerReferenceType' => 'INVOICE_NUMBER', 'Value' => $this->international_invoicenumber];
                }

                $requestClient['RequestedShipment']['CustomsClearanceDetail']['CustomsOptions'] =
                    ['Type' => $this->international_reasonforexport];
                $requestClient['RequestedShipment']['CustomsClearanceDetail']['DocumentContent'] = 'DOCUMENTS_ONLY';

                if ($this->international_reasonforexport == 'OTHER' && $this->international_reasonforexport_desc != '') {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['CustomsOptions']['Description'] = $this->international_reasonforexport_desc;
                }

                if (!empty($this->international_purpose_of_shipment) && ($this->internationalType !== 'PRO_FORMA_INVOICE' || $this->international_purpose_of_shipment !== 'SOLD')) {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['CommercialInvoice']['Purpose'] = $this->international_purpose_of_shipment;
                }

                if (!empty($this->international_termsofsale)) {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['CommercialInvoice']['TermsOfSale'] = $this->international_termsofsale;
                }

                if ($this->_conf->getStoreConfig('fedexlabel/ratepayment/dytytaxinternational', $this->storeId) === 'shipper') {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['DutiesPayment'] = [
                        'PaymentType' => 'SENDER',
                        'Payor' => ['ResponsibleParty' => [
                            'AccountNumber' => $this->shipperNumber
                        ]]
                    ];
                } else if ($this->_conf->getStoreConfig('fedexlabel/ratepayment/dytytaxinternational', $this->storeId) === 'customer') {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['DutiesPayment'] = [
                        'PaymentType' => 'RECIPIENT',
                        /*'Payor' => array('ResponsibleParty' => array(
                            'AccountNumber' => $this->shipperNumber
                        ))*/
                    ];
                } else if ($this->_conf->getStoreConfig('fedexlabel/ratepayment/dytytaxinternational', $this->storeId) != '') {
                    $acctModel = $this->accountFactory->create()->load($this->_conf->getStoreConfig('fedexlabel/ratepayment/dytytaxinternational', $this->storeId));
                    if ($acctModel) {
                        $requestClient['RequestedShipment']['CustomsClearanceDetail']['DutiesPayment'] = [
                            'PaymentType' => 'THIRD_PARTY',
                            'Payor' => array('ResponsibleParty' => array(
                                'AccountNumber' => $acctModel->getAccountnumber()
                            ))
                        ];
                    }
                }

                $requestClient['RequestedShipment']['CustomsClearanceDetail']['CustomsValue'] = [
                    'Currency' => $this->currencyCode,
                    'Amount' => round($this->codMonetaryValue / $this->currencyCoefficient, 2),
                ];

                $requestClient = $this->getCommodities($requestClient);
            } else {
                if ($this->shiptoCountryCode == 'IN') {
                    /*if (!isset($package['cod']) || $package['cod'] != 1) {
                        $requestClient['RequestedShipment']['CustomsClearanceDetail']['FreightOnValue'] = 'CARRIER_RISK';
                    }*/
                    if (isset($package['cod']) && $package['cod'] == 1) {
                        $requestClient['RequestedShipment']['CustomsClearanceDetail']['CommercialInvoice']['Purpose'] = 'SOLD';
                    } else {
                        $requestClient['RequestedShipment']['CustomsClearanceDetail']['CommercialInvoice']['Purpose'] = 'NOT_SOLD';
                    }

                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['CustomsValue'] = [
                        'Currency' => $this->currencyCode,
                        'Amount' => round($this->codMonetaryValue / $this->currencyCoefficient, 2),
                    ];
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['CustomsOptions'] =
                        ['Type' => $this->international_reasonforexport];
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['DocumentContent'] = 'DOCUMENTS_ONLY';
                    $requestClient = $this->getCommodities($requestClient);
                }
            }

            $this->requestClient = $requestClient;
            $this->eventManager->dispatch('infomodus_fedexlabel_set_model_request', ['model' => $this]);
            $requestClient = $this->requestClient;

            $request = $this->_getAuthDetails() + $requestClient;
            //print_r($request);
            $client = $this->_createShipSoapClient();
            $response = $client->processShipment($request);
            //print_r($client->__getLastRequest());
            //print_r($client->__getLastResponse());
            $this->_logger->info($client->__getLastRequest());
            $this->_logger->info($client->__getLastResponse());

            // $this->_logger->info($this->xml2array($response));
            /*print_r($response);*/
            if (is_soap_fault($response)) {
                if (isset($response->detail->desc)) {
                    $debugData['result']['code'] .= $response->faultcode . '; ';
                    $debugData['result']['error'] .= $response->detail->desc . '; ';
                } else {
                    $debugData['result']['code'] .= 'Unknown error code; ';
                    $debugData['result']['error'] .= 'Unknown error description; ';
                }

                $this->rollBack($data);
                return ['error' => [
                    'code' => $debugData['result']['code'],
                    'desc' => $debugData['result']['error'],
                    'request' => $request, 'response' => $this->xml2array($response)]
                ];
            } else {
                if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
                    $shippingLabelContent = $response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image;
                    $trackingNumber = $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->TrackingNumber;
                    $imageType = strtolower($response->CompletedShipmentDetail->CompletedPackageDetails->Label->ImageType);
                    // Get string Barcode and store into DB with tracking no.
                    $stringBarcode = $response->CompletedShipmentDetail->CompletedPackageDetails->OperationalDetail->Barcodes->StringBarcodes->Value;
                    $shippingInvoiceContent = false;
                    if (isset($response->CompletedShipmentDetail->ShipmentDocuments)) {
                        $shippingInvoiceContent = $response->CompletedShipmentDetail->ShipmentDocuments->Parts->Image;
                        $invoiceType = strtolower($response->CompletedShipmentDetail->ShipmentDocuments->ImageType);
                    }

                    if ($imageType == "zplii") {
                        $imageType = "zpl2";
                    }
                     
                    $formId = $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->FormId;
                    // 03 HYDHM
                    $serviceCode = $response->CompletedShipmentDetail->CompletedPackageDetails->OperationalDetail->OperationalInstructions[2]->Content;
                    // instructions
                    $instr1 = $response->CompletedShipmentDetail->CompletedPackageDetails->OperationalDetail->OperationalInstructions[6]->Content;
                    $instr2 = $response->CompletedShipmentDetail->CompletedPackageDetails->OperationalDetail->OperationalInstructions[7]->Content;
                    $instr3 = $response->CompletedShipmentDetail->CompletedPackageDetails->OperationalDetail->OperationalInstructions[8]->Content;
                    $instr4 = $response->CompletedShipmentDetail->CompletedPackageDetails->OperationalDetail->OperationalInstructions[9]->Content;
                    $instr5 = $response->CompletedShipmentDetail->CompletedPackageDetails->OperationalDetail->OperationalInstructions[11]->Content;
                    $instr6 = $response->CompletedShipmentDetail->CompletedPackageDetails->OperationalDetail->OperationalInstructions[10]->Content;
                    $instructions = 'Instructions : '.$instr1.' : '.$instr2.' : '.$instr3.' : '.$instr4.' : '.$instr5.' : '.$instr6;

                     $dataFedex = array(
                        'trackingnumber'=>$trackingNumber,
                        'stringBarcode' => $stringBarcode,
                        'formId' => $formId,
                        'instructions' => $instructions,
                        'ursaCode' => $serviceCode
                    );
                    $responseData = json_encode($dataFedex);

                    /*if($imageType == "epl2"){$imageType = "epl";}*/
                    $arrResponsXML[$k]['trackingnumber'] = $trackingNumber;
                    $arrResponsXML[$k]['graphicImage'] = $shippingLabelContent;
                    $arrResponsXML[$k]['type_print'] = $imageType;
                    $arrResponsXML[$k]['responseData'] = $responseData;
                    $arrResponsXML[$k]['stringBarcode'] = $stringBarcode;
                    $arrResponsXML[$k]['formId'] = $formId;
                    $arrResponsXML[$k]['instructions'] = $instructions;
                    $arrResponsXML[$k]['ursaCode'] = $serviceCode;



                    $path = $this->_conf->getBaseDir('media') . '/fedexlabel/label/';
                    $file = fopen($path . 'label' . $trackingNumber . '.' . $imageType, 'w');
                    fwrite($file, $shippingLabelContent);
                    fclose($file);

                    if ($shippingInvoiceContent) {
                        $file = fopen($path . 'invoice' . $trackingNumber . '.' . $invoiceType, 'w');
                        fwrite($file, $shippingInvoiceContent);
                        fclose($file);
                    }

                    $data[] = [
                        'tracking_number' => $trackingNumber
                    ];
                    if ($isFirstRequest === true && isset($response->CompletedShipmentDetail->MasterTrackingId)) {
                        $this->masterTrackingId = $response->CompletedShipmentDetail->MasterTrackingId->TrackingNumber;
                        $isFirstRequest = false;
                    }

                    if (isset($response->CompletedShipmentDetail->ShipmentRating) && isset($response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails)) {
                        if (isset($response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails) && is_array($response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails) && count($response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails) > 0) {
                            $valueToDb['price'] = ['currency' => $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails[0]->TotalNetChargeWithDutiesAndTaxes->Currency, 'price' => $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails[0]->TotalNetChargeWithDutiesAndTaxes->Amount];
                        } else {
                            $valueToDb['price'] = ['currency' => $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails->TotalNetChargeWithDutiesAndTaxes->Currency, 'price' => $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails->TotalNetChargeWithDutiesAndTaxes->Amount];
                        }
                    } elseif (isset($response->CompletedShipmentDetail) && isset($response->CompletedShipmentDetail->CompletedPackageDetails) && isset($response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails)) {
                        if (isset($response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails) && is_array($response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails) && count($response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails) > 0) {
                            $valueToDb['price'] = ['currency' => $response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails[0]->NetCharge->Currency, 'price' => $valueToDb['price']['price'] + $response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails[0]->NetCharge->Amount];
                        } else {
                            $valueToDb['price'] = ['currency' => $response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails->NetCharge->Currency, 'price' => $valueToDb['price']['price'] + $response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails->NetCharge->Amount];
                        }
                    }

                    if (isset($package['cod']) && $package['cod'] == 1
                        && isset($response->CompletedShipmentDetail->AssociatedShipments)
                        && $response->CompletedShipmentDetail->AssociatedShipments->Type == 'COD_RETURN'
                        && isset($response->CompletedShipmentDetail->AssociatedShipments->Label->Type)
                        && $response->CompletedShipmentDetail->AssociatedShipments->Label->Type == 'COD_RETURN_LABEL'
                    ) {
                        $trackingNumberReturn = $response->CompletedShipmentDetail->AssociatedShipments->TrackingId->TrackingNumber;
                        $shippingReturnLabelContent = $response->CompletedShipmentDetail->AssociatedShipments->Label->Parts->Image;
                        $imageTypeReturn = strtolower($response->CompletedShipmentDetail->AssociatedShipments->Label->ImageType);
                        $file = fopen($path . 'label' . $trackingNumberReturn . '.' . $imageTypeReturn, 'w');
                        fwrite($file, $shippingReturnLabelContent);
                        fclose($file);
                        $arrResponsXML[$k]['trackingnumberReturn'] = $trackingNumberReturn;
                        $arrResponsXML[$k]['graphicImageReturn'] = $shippingReturnLabelContent;
                        $arrResponsXML[$k]['type_printReturn'] = $imageTypeReturn;
                    }
                } else {
                    if (is_array($response->Notifications)) {
                        foreach ($response->Notifications as $notification) {
                            $debugData['result']['code'] .= $notification->Code . '; ';
                            $debugData['result']['error'] .= $notification->Message . '; ';
                        }
                    } else {
                        $debugData['result']['code'] = $response->Notifications->Code . ' ';
                        $debugData['result']['error'] = $response->Notifications->Message . ' ';
                    }

                    $this->rollBack($data);
                    return ['error' => ['code' => $debugData['result']['code'],
                        'desc' => $debugData['result']['error'], 'request' => $request,
                        'response' => $this->xml2array($response)]];
                }
            }
        }
        $valueToDb['arrResponsXML'] = $arrResponsXML;
        $valueToDb['shipidnumber'] = $this->masterTrackingId;
        return $valueToDb;
    }

    /**
     * @return array
     */
    public function getShipFrom()
    {
        $data = [];
        $isFirstRequest = true;
        $totalWeight = 0;
        $arrResponsXML = [];
        $valueToDb = [];
        $debugData = [];
        $debugData['result'] = ['code' => '', 'error' => ''];
        if (!isset($valueToDb['price']['price'])) {
            $valueToDb['price']['price'] = 0;
        }
        /*$package = $this->packages[0];
        if ($this->shipperCountryCode == $this->shiptoCountryCode) {
            $totalWeight += round($package['weight'] + $package['packweight'], 2);
        } else {
            $totalWeight = round($package['packweight'], 2);
            foreach ($this->international_products as $kp => $products) {
                $totalWeight += round($products['weight'], 2);
            }
        }*/
        foreach ($this->packages as $k => $package) {
            if (isset($package['weight']) && $package['weight'] > 0) {
                if (empty($package['packweight']) || !is_numeric($package['packweight'])) {
                    $package['packweight'] = 0;
                }

                $totalWeight += round((float)$package['weight'] + (float)$package['packweight'], 2);
            }

            if (isset($package['cod']) && $package['cod'] == 1) {
                return null;
            }
        }
        foreach ($this->packages as $k => $package) {
            $requestClient = [
                'RequestedShipment' => [
                    'ShipTimestamp' => time(),
                    'DropoffType' => $this->dropoff,
                    'PackagingType' => $this->packagingType,
                    'ServiceType' => $this->serviceCode,
                    'Recipient' => [
                        'Contact' => [
                            'PersonName' => $this->shipperAttentionName,
                            'CompanyName' => $this->shipperName,
                            'PhoneNumber' => $this->shipperPhoneNumber,
                            'EMailAddress' => $this->qvn_email_shipper
                        ],
                        'Address' => [
                            'StreetLines' => [
                                $this->shipperAddressLine1,
                                $this->shipperAddressLine2
                            ],
                            'City' => $this->shipperCity,
                            'StateOrProvinceCode' => $this->shipperStateProvinceCode,
                            'PostalCode' => $this->shipperPostalCode,
                            'CountryCode' => $this->shipperCountryCode
                        ]
                    ],
                    'Shipper' => [
                        'Contact' => [
                            'PersonName' => $this->shiptoAttentionName,
                            'CompanyName' => $this->shiptoCompanyName,
                            'PhoneNumber' => $this->shiptoPhoneNumber,
                            'EMailAddress' => $this->qvn_email_shipto
                        ],
                        'Address' => [
                            'StreetLines' => [
                                $this->shiptoAddressLine1,
                                $this->shiptoAddressLine2
                            ],
                            'City' => $this->shiptoCity,
                            'StateOrProvinceCode' => $this->shiptoStateProvinceCode,
                            'PostalCode' => $this->shiptoPostalCode,
                            'CountryCode' => $this->shiptoCountryCode,
                            'Residential' => (bool)$this->residentialAddress
                        ],
                    ],
                    'LabelSpecification' => [
                        'LabelFormatType' => 'COMMON2D',
                        'ImageType' => 'PDF',
                        'LabelStockType' => 'PAPER_4X6',
                    ],
                    /*'RateRequestTypes' => array('ACCOUNT'),*/
                    'PackageCount' => 1,
                ]
            ];

            if($this->serviceCode == 'SMART_POST'){
                $requestClient['RequestedShipment']['SmartPostShipmentDetail']['SmartPostShipmentProcessingOptionsRequested']['SmartPostShipmentProcessingOptionType'] = 'GROUND_TRACKING_ NUMBER_REQUESTED';
                $requestClient['RequestedShipment']['SmartPostDetail'] = [
                    'Indicia' => 'PARCEL_RETURN',
                    'AncillaryEndorsement' => 'RETURN_SERVICE',
                    'HubID' => (int)$this->_conf->getStoreConfig('fedexlabel/additional_settings/smart_hub_id', $this->storeId),
                ];
            }

            if ($this->fedexAccount == 'S') {
                $requestClient['RequestedShipment']['ShippingChargesPayment'] = [
                    'PaymentType' => 'SENDER',
                    'Payor' => ['ResponsibleParty' => [
                        'AccountNumber' => $this->shipperNumber
                    ]]
                ];
            } else {
                $requestClient['RequestedShipment']['ShippingChargesPayment'] = [
                    'PaymentType' => 'THIRD_PARTY',
                    'Payor' => ['ResponsibleParty' => [
                        'AccountNumber' => $this->accountData->getAccountnumber()
                    ]]
                ];
            }
            if ($this->shiptoCountryCode != 'IN') {
                $requestClient['RequestedShipment']['SpecialServicesRequested'] = [
                    'SpecialServiceTypes' => 'RETURN_SHIPMENT',
                    'ReturnShipmentDetail' => ['ReturnType' => 'PRINT_RETURN_LABEL']
                ];
            }

            $packagesArray = [
                'SequenceNumber' => 1,
                'Weight' => [
                    'Units' => $this->weightUnits,
                    'Value' => $totalWeight
                ],
                'CustomerReferences' => [
                    'CustomerReferenceType' => $package['packagingreferencetypereturn'],
                    'Value' => $package['packagingreferencenumbervaluereturn']
                ],
                'SpecialServicesRequested' => [
                    'SpecialServiceTypes' => ['SIGNATURE_OPTION'],
                    'SignatureOptionDetail' => ['OptionType' => $this->adult]
                ],
            ];

            if (!empty($package['length']) || !empty($package['width']) || !empty($package['height'])) {
                $dimensions = [];
                $dimensions['Length'] = round($package['length'], 0);
                $dimensions['Width'] = round($package['width'], 0);
                $dimensions['Height'] = round($package['height'], 0);
                $dimensions['Units'] = $this->unitOfMeasurement;
                $packagesArray['Dimensions'] = $dimensions;
            }
            $requestClient['RequestedShipment']['RequestedPackageLineItems'][] = $packagesArray;

            if ($this->shipperCountryCode != $this->shiptoCountryCode) {
                $requestClient['RequestedShipment']['TotalWeight'] = ['Units' => $this->weightUnits, 'Value' => $totalWeight];
                $requestClient['RequestedShipment']['CustomsClearanceDetail'] =
                    [
                        'CustomsValue' =>
                            [
                                'Currency' => $this->currencyCode,
                                'Amount' => round($this->codMonetaryValue / $this->currencyCoefficient, 2),
                            ],
                        'CustomsOptions' => ['Type' => $this->international_reasonforexport]
                    ];
                if ($this->international_reasonforexport == 'OTHER' && $this->international_reasonforexport_desc != '') {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['CustomsOptions']['Description'] = $this->international_reasonforexport_desc;
                }
                $requestClient['RequestedShipment']['CustomsClearanceDetail']['DutiesPayment'] = [
                    'PaymentType' => 'SENDER',
                    'Payor' => ['ResponsibleParty' => [
                        'AccountNumber' => $this->shipperNumber
                    ]]
                ];
                /*if(!empty($this->international_termsofsale)){
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['CommercialInvoice']['TermsOfSale'] = $this->international_termsofsale;
                }*/
                $requestClient = $this->getCommodities($requestClient);
            } else {
                if ($this->shiptoCountryCode == 'IN') {
                    /*$requestClient['RequestedShipment']['CustomsClearanceDetail']['FreightOnValue'] = 'CARRIER_RISK';*/
                    /*if (isset($package['cod']) && $package['cod'] == 1) {
                        $requestClient['RequestedShipment']['CustomsClearanceDetail']['CommercialInvoice']['Purpose'] = 'SOLD';
                    } else {*/
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['CommercialInvoice']['Purpose'] = 'NOT_SOLD';
                    /*}*/
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['CustomsValue'] = [
                        'Currency' => $this->currencyCode,
                        'Amount' => round($this->codMonetaryValue / $this->currencyCoefficient, 2),
                        'CustomsOptions' => ['Type' => $this->international_reasonforexport],
                        'DocumentContent' => 'DOCUMENTS_ONLY',
                    ];
                    $requestClient = $this->getCommodities($requestClient);
                }
            }

            $request = $this->_getAuthDetails() + $requestClient;
            //print_r($request);
            use_soap_error_handler(true);
            $client = $this->_createShipSoapClient();
            $response = $client->processShipment($request);
            //print_r($client->__getLastRequest());
            //print_r($client->__getLastResponse());
            $this->_logger->info($client->__getLastRequest());
            $this->_logger->info($client->__getLastResponse());
            /*$this->_logger->info($this->packages);*/
            /*print_r($response);*/
            if (is_soap_fault($response)) {
                if (isset($response->detail->desc)) {
                    $debugData['result']['code'] .= $response->faultcode . '; ';
                    $debugData['result']['error'] .= $response->detail->desc . '; ';
                } else {
                    $debugData['result']['code'] .= 'Unknown error code; ';
                    $debugData['result']['error'] .= 'Unknown error description; ';
                }

                $this->rollBack($data);
                return [
                    'error' => [
                        'code' => $debugData['result']['code'],
                        'desc' => $debugData['result']['error'],
                        'request' => $request,
                        'response' => $this->xml2array($response)
                    ]
                ];
            } else {
                if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
                    $shippingLabelContent = $response->
                    CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image;
                    $trackingNumber = $response->
                    CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->TrackingNumber;
                    $arrResponsXML[$k]['trackingnumber'] = $trackingNumber;
                    $arrResponsXML[$k]['graphicImage'] = $shippingLabelContent;
                    $arrResponsXML[$k]['type_print'] = 'pdf';
                    $path = $this->_conf->getBaseDir('media') . '/fedexlabel/label/';
                    $file = fopen($path . 'label' . $trackingNumber . '.pdf', 'w');
                    fwrite($file, $shippingLabelContent);
                    fclose($file);
                    $data[] = [
                        'tracking_number' => $trackingNumber
                    ];
                    if ($isFirstRequest === true && isset($response->CompletedShipmentDetail->MasterTrackingId)) {
                        $this->masterTrackingId = $response->CompletedShipmentDetail->MasterTrackingId->TrackingNumber;
                        $isFirstRequest = false;
                    }
                    if (isset($response->CompletedShipmentDetail->ShipmentRating)) {
                        if (is_array($response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails) && !empty($response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails)) {
                            $valueToDb['price'] = ['currency' => $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails[0]->TotalNetChargeWithDutiesAndTaxes->Currency, 'price' => $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails[0]->TotalNetChargeWithDutiesAndTaxes->Amount];
                        } else {
                            $valueToDb['price'] = ['currency' => $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails->TotalNetChargeWithDutiesAndTaxes->Currency, 'price' => $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails->TotalNetChargeWithDutiesAndTaxes->Amount];
                        }
                    } else if (isset($response->CompletedShipmentDetail->CompletedPackageDetails) && isset($response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating)) {
                        if (is_array($response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails) && !empty($response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails)) {
                            $valueToDb['price'] = ['currency' => $response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails[0]->NetCharge->Currency, 'price' => $valueToDb['price']['price'] + $response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails[0]->NetCharge->Amount];
                        } else {
                            $valueToDb['price'] = ['currency' => $response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails->NetCharge->Currency, 'price' => $valueToDb['price']['price'] + $response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails->NetCharge->Amount];
                        }
                    }
                } else {
                    if (is_array($response->Notifications)) {
                        foreach ($response->Notifications as $notification) {
                            $debugData['result']['code'] .= $notification->Code . '; ';
                            $debugData['result']['error'] .= $notification->Message . '; ';
                        }
                    } else {
                        $debugData['result']['code'] = $response->Notifications->Code . ' ';
                        $debugData['result']['error'] = $response->Notifications->Message . ' ';
                    }
                    $this->rollBack($data);
                    return ['error' => ['code' => $debugData['result']['code'], 'desc' =>
                        $debugData['result']['error'],
                        'request' => $request, 'response' => $this->xml2array($response)]];
                }
            }
            /*break;*/
        }
        $valueToDb['arrResponsXML'] = $arrResponsXML;
        $valueToDb['shipidnumber'] = $this->masterTrackingId;
        return $valueToDb;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getRate($type = "shipment")
    {
        $totalWeight = 0;
        $valueToDb = [];
        $debugData = ['result' => []];
        $debugData['result']['code'] = '';
        $debugData['result']['error'] = '';
        foreach ($this->packages as $k => $package) {
            if (isset($package['weight']) && $package['weight'] > 0) {
                if (empty($package['packweight']) || !is_numeric($package['packweight'])) {
                    $package['packweight'] = 0;
                }
                $totalWeight += round((float)$package['weight'] + (float)$package['packweight'], 2);
            }
        }

        if ($type == "ajaxprice_shipment") {
            $shipper = [
                'Contact' => [
                    'PersonName' => $this->shipperAttentionName,
                    'CompanyName' => $this->shipperName,
                    'PhoneNumber' => $this->shipperPhoneNumber
                ],
                'Address' => [
                    'StreetLines' => [
                        $this->shipperAddressLine1
                    ],
                    'City' => $this->shipperCity,
                    'StateOrProvinceCode' => $this->shipperStateProvinceCode,
                    'PostalCode' => $this->shipperPostalCode,
                    'CountryCode' => $this->shipperCountryCode
                ]
            ];
            if ($this->shipperTinType != "") {
                $shipper['Tins'] = [
                    'TinType' => $this->shipperTinType,
                    'Number' => $this->shipperTinNumber
                ];
            }

            $recipient = [
                'Contact' => [
                    'PersonName' => $this->shiptoAttentionName,
                    'CompanyName' => $this->shiptoCompanyName,
                    'PhoneNumber' => $this->shiptoPhoneNumber
                ],
                'Address' => [
                    'StreetLines' => [
                        $this->shiptoAddressLine1,
                        $this->shiptoAddressLine2
                    ],
                    'City' => $this->shiptoCity,
                    'StateOrProvinceCode' => $this->shiptoStateProvinceCode,
                    'PostalCode' => $this->shiptoPostalCode,
                    'CountryCode' => $this->shiptoCountryCode,
                    'Residential' => (bool)$this->residentialAddress
                ],
            ];
        } else {
            $shipper = [
                'Contact' => [
                    'PersonName' => $this->shiptoAttentionName,
                    'CompanyName' => $this->shiptoCompanyName,
                    'PhoneNumber' => $this->shiptoPhoneNumber
                ],
                'Address' => [
                    'StreetLines' => [
                        $this->shiptoAddressLine1,
                        $this->shiptoAddressLine2
                    ],
                    'City' => $this->shiptoCity,
                    'StateOrProvinceCode' => $this->shiptoStateProvinceCode,
                    'PostalCode' => $this->shiptoPostalCode,
                    'CountryCode' => $this->shiptoCountryCode,
                    'Residential' => (bool)$this->residentialAddress
                ],
            ];
            $recipient = [
                'Contact' => [
                    'PersonName' => $this->shipperAttentionName,
                    'CompanyName' => $this->shipperName,
                    'PhoneNumber' => $this->shipperPhoneNumber
                ],
                'Address' => [
                    'StreetLines' => [
                        $this->shipperAddressLine1
                    ],
                    'City' => $this->shipperCity,
                    'StateOrProvinceCode' => $this->shipperStateProvinceCode,
                    'PostalCode' => $this->shipperPostalCode,
                    'CountryCode' => $this->shipperCountryCode,
                    'Residential' => (bool)$this->shipperResidential
                ]
            ];
            if ($this->shipperTinType != "") {
                $recipient['Tins'] = [
                    'TinType' => $this->shipperTinType,
                    'Number' => $this->shipperTinNumber
                ];
            }
        }

        $requestClient = [
            'RequestedShipment' => [
                'ShipTimestamp' => date('c', time()),
                'DropoffType' => $this->dropoff,
                'PackagingType' => $this->packagingType,
                'ServiceType' => $this->serviceCode,
                'Shipper' => $shipper,
                'Recipient' => $recipient,
                'PackageCount' => count($this->packages),
            ]
        ];
        if ($this->fedexAccount == 'S') {
            $requestClient['RequestedShipment']['ShippingChargesPayment'] = [
                'PaymentType' => 'SENDER',
                'Payor' => ['ResponsibleParty' => [
                    'AccountNumber' => $this->shipperNumber
                ]]
            ];
        } elseif ($this->fedexAccount == 'R') {
            $requestClient['RequestedShipment']['ShippingChargesPayment'] = [
                'PaymentType' => 'RECIPIENT',
                'Payor' => ['ResponsibleParty' => [
                    'AccountNumber' => $this->shipperNumber
                ]]
            ];
        } else {
            $requestClient['RequestedShipment']['ShippingChargesPayment'] = [
                'PaymentType' => 'THIRD_PARTY',
                'Payor' => ['ResponsibleParty' => [
                    'AccountNumber' => $this->accountData->getAccountnumber()
                ]]
            ];
        }

        foreach ($this->packages as $k => $package) {
            $packagesArray = [
                'SequenceNumber' => ($k + 1),
                'GroupNumber' => 1/*($k + 1)*/,
                'GroupPackageCount' => 1/*($k + 1)*/,
                'Weight' => [
                    'Units' => $this->weightUnits,
                    'Value' => round((float)$package['weight'] + (float)$package['packweight'], 2)
                ],
                'CustomerReferences' => [
                    [
                        'CustomerReferenceType' => $package['packagingreferencetype'],
                        'Value' => $package['packagingreferencenumbervalue']
                    ],
                ]/*,
                'SpecialServicesRequested' => [
                    'SpecialServiceTypes' => ['SIGNATURE_OPTION'],
                    'SignatureOptionDetail' => ['OptionType' => $this->adult]
                ],*/
            ];

            if($this->shipperCountryCode != $this->shiptoCountryCode){
                $packagesArray['CustomerReferences'][] = [
                    'CustomerReferenceType' => 'DEPARTMENT_NUMBER',
                    'Value' => ($this->fedexAccount == 'S' ?
                        'Bill Duties : Sender' : 'Bill Duties : Recipient')
                ];
            }

            if ($this->shiptoCountryCode == 'IN') {
                $packagesArray['CustomerReferences'][] = [
                    'CustomerReferenceType' => 'INVOICE_NUMBER',
                    'Value' => $this->international_invoicenumber . 'Dated : ' . $this->international_invoicedate
                ];
            }

            if ($this->masterTrackingId === null) {
                $requestClient['RequestedShipment']['TotalWeight'] = [
                    'Units' => $this->weightUnits, 'Value' => $totalWeight
                ];
            }
            if (isset($package['cod']) && $package['cod'] == 1) {
                $requestClient['RequestedShipment']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'COD';
                $requestClient['RequestedShipment']['SpecialServicesRequested']['CodDetail'] = [
                    'CodCollectionAmount' => [
                        'Amount' => round($package['codmonetaryvalue'] / $this->currencyCoefficient, 2),
                        'Currency' => $this->currencyCode
                    ],
                    'CollectionType' => 'ANY'
                ];
                if ($this->shiptoCountryCode == 'IN') {
                    $requestClient['RequestedShipment']['SpecialServicesRequested']['CodDetail']['CollectionType'] =
                        'CASH';
                }
            }
            if ($this->saturdayDelivery == 1) {
                $requestClient['RequestedShipment']['SpecialServicesRequested']['SpecialServiceTypes'][] =
                    'SATURDAY_DELIVERY';
            }

            if (!empty($package['length']) || !empty($package['width']) || !empty($package['height'])) {
                $dimensions = [];
                $dimensions['Length'] = round($package['length'], 0);
                $dimensions['Width'] = round($package['width'], 0);
                $dimensions['Height'] = round($package['height'], 0);
                $dimensions['Units'] = $this->unitOfMeasurement;
                $packagesArray['Dimensions'] = $dimensions;
            }
            $requestClient['RequestedShipment']['RequestedPackageLineItems'][] = $packagesArray;

            // for international shipping
            if ($this->shipperCountryCode != $this->shiptoCountryCode) {
                $requestClient['RequestedShipment']['CustomsClearanceDetail']['CustomsOptions'] =
                    ['Type' => $this->international_reasonforexport];
                $requestClient['RequestedShipment']['CustomsClearanceDetail']['DocumentContent'] = 'DOCUMENTS_ONLY';
                if ($this->international_reasonforexport == 'OTHER'
                    && $this->international_reasonforexport_desc != ''
                ) {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['CustomsOptions']['Description'] =
                        $this->international_reasonforexport_desc;
                }

                if ($this->_conf->getStoreConfig('fedexlabel/ratepayment/dytytaxinternational', $this->storeId) === 'shipper') {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['DutiesPayment'] = [
                        'PaymentType' => 'SENDER',
                        'Payor' => ['ResponsibleParty' => [
                            'AccountNumber' => $this->shipperNumber
                        ]]
                    ];
                } else {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['DutiesPayment'] = [
                        'PaymentType' => 'RECIPIENT',
                        /*'Payor' => array('ResponsibleParty' => array(
                            'AccountNumber' => $this->shipperNumber
                        ))*/
                    ];
                }

                $requestClient['RequestedShipment']['CustomsClearanceDetail']['CustomsValue'] = [
                    'Currency' => $this->currencyCode,
                    'Amount' => round($this->codMonetaryValue / $this->currencyCoefficient, 2),
                ];
                $requestClient = $this->getCommodities($requestClient);
            } else {
                if ($this->shiptoCountryCode == 'IN') {
                    if (!isset($package['cod']) || $package['cod'] != 1) {
                        $requestClient['RequestedShipment']['CustomsClearanceDetail']['FreightOnValue'] =
                            'CARRIER_RISK';
                    }

                    if (isset($package['cod']) && $package['cod'] == 1) {
                        $requestClient['RequestedShipment']['CustomsClearanceDetail']['CommercialInvoice']['Purpose'] =
                            'SOLD';
                    } else {
                        $requestClient['RequestedShipment']['CustomsClearanceDetail']['CommercialInvoice']['Purpose'] =
                            'NOT_SOLD';
                    }

                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['CustomsValue'] = [
                        'Currency' => $this->currencyCode,
                        'Amount' => round($this->codMonetaryValue / $this->currencyCoefficient, 2),
                    ];
                    $requestClient = $this->getCommodities($requestClient);
                }
            }
        }
        $requestClient['ReturnTransitAndCommit'] = true;
        $request = $this->_getAuthDetails('crs', 20) + $requestClient;
        /*print_r($request); exit;*/
        $client = $this->_createRateSoapClient();
        $response = $client->getRates($request);
        //print_r($client->__getLastRequest());
        //print_r($client->__getLastResponse());
        $this->_logger->info($client->__getLastRequest());
        $this->_logger->info($client->__getLastResponse());
        /*$this->_logger->info($this->xml2array($response));*/
        /*print_r($response);*/
        if (is_soap_fault($response)) {
            if (isset($response->detail->desc)) {
                $debugData['result']['code'] .= $response->faultcode . '; ';
                $debugData['result']['error'] .= $response->detail->desc . '; ';
            } else {
                $debugData['result']['code'] .= 'Unknown error code; ';
                $debugData['result']['error'] .= 'Unknown error description; ';
            }

            return array('error' => ['code' => $debugData['result']['code'], 'desc' => $debugData['result']['error'],
                'request' => $request/*, 'response' => $this->xml2array($response)*/]);
        } else {
            if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR'
                && isset($response->RateReplyDetails)
            ) {
                $response = $response->RateReplyDetails;

                $responses = $response;
                if (!is_array($responses)) {
                    $responses2 = [];
                    $responses2[0] = $responses;
                    $responses = $responses2;
                }

                foreach ($responses as $response) {
                    if (array_key_exists('DeliveryTimestamp', $response)) {
                        $time = $response->DeliveryTimestamp;
                    } elseif (array_key_exists('TransitTime', $response)) {
                        $time = $response->TransitTime;
                    } else {
                        $time = '';
                    }

                    if (isset($response->RatedShipmentDetails) && is_array($response->RatedShipmentDetails)) {
                        foreach ($response->RatedShipmentDetails as $replyPrice) {
                            $valueToDb[]['price'] = ['time' => $time,
                                'currency' => $replyPrice->ShipmentRateDetail->TotalNetCharge->Currency,
                                'price' => number_format($replyPrice->ShipmentRateDetail
                                    ->TotalNetCharge->Amount, 2, ".", ",")];
                        }
                    } elseif (isset($response->RatedShipmentDetails) && !is_array($response->RatedShipmentDetails)) {
                        $valueToDb[]['price'] = ['time' => $time,
                            'currency' => $response->RatedShipmentDetails->ShipmentRateDetail->TotalNetChargeWithDutiesAndTaxes->Currency,
                            'price' =>
                                number_format($response->RatedShipmentDetails->ShipmentRateDetail->TotalNetChargeWithDutiesAndTaxes->Amount,
                                    2, ".", ",")];
                    }
                }
            } else {
                if (is_array($response->Notifications)) {
                    foreach ($response->Notifications as $notification) {
                        $debugData['result']['code'] .= $notification->Code . '; ';
                        $debugData['result']['error'] .= $notification->Message . '; ';
                    }
                } else {
                    $debugData['result']['code'] = $response->Notifications->Code . ' ';
                    $debugData['result']['error'] = $response->Notifications->Message . ' ';
                }

                return ['error' => ['code' => $debugData['result']['code'], 'desc' => $debugData['result']['error'],
                    'request' => $request/*, 'response' => $this->xml2array($response)*/]];
            }
        }
        return $valueToDb;
    }

    public function uploadImage($image)
    {
        $valueToDb = [];
        $debugData = ['result' => []];
        $debugData['result']['code'] = '';
        $debugData['result']['error'] = '';


        $requestClient = ['Images' => [
            '0' => [
                'Id' => 'IMAGE_1',
                'Image' => stream_get_contents(fopen($image, "r"))
            ]
        ]];

        $request = $this->_getAuthDetails('cdus', 8) + $requestClient;
        /*print_r($request); exit;*/
        $client = $this->_createImageUploadSoapClient();
        $response = $client->uploadImages($request);
        //print_r($client->__getLastRequest());
        //print_r($client->__getLastResponse());

        $this->_logger->info($client->__getLastRequest());
        $this->_logger->info($client->__getLastResponse());
        /*$this->_logger->info($this->xml2array($response));*/
        /*print_r($response);*/
        if (is_soap_fault($response)) {
            if (isset($response->detail->desc)) {
                $debugData['result']['code'] .= $response->faultcode . '; ';
                $debugData['result']['error'] .= $response->detail->desc . '; ';
            } else {
                $debugData['result']['code'] .= 'Unknown error code; ';
                $debugData['result']['error'] .= 'Unknown error description; ';
            }

            $this->managerInterface->addErrorMessage(__('Signature image is not uploaded'));

            return ['error' => ['code' => $debugData['result']['code'], 'desc' => $debugData['result']['error'],
                'request' => $request/*, 'response' => $this->xml2array($response)*/]];
        } else {

            if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
                $this->managerInterface->addSuccessMessage(__('Signature image is success uploaded'));
            } else {
                $this->managerInterface->addErrorMessage(__('Signature image is not uploaded'));
            }
        }

        return $valueToDb;
    }

    public function uploadLetterHead($image)
    {
        $valueToDb = [];
        $debugData = ['result' => []];
        $debugData['result']['code'] = '';
        $debugData['result']['error'] = '';


        $requestClient = ['Images' => [
            '0' => [
                'Id' => 'IMAGE_2',
                'Image' => stream_get_contents(fopen($image, "r"))
            ]
        ]];

        $request = $this->_getAuthDetails('cdus', 8) + $requestClient;
        /*print_r($request); exit;*/
        $client = $this->_createImageUploadSoapClient();
        $response = $client->uploadImages($request);
        //print_r($client->__getLastRequest());
        //print_r($client->__getLastResponse());
        $this->_logger->info($client->__getLastRequest());
        $this->_logger->info($client->__getLastResponse());
        /*$this->_logger->info($this->xml2array($response));*/
        /*print_r($response);*/

        if (is_soap_fault($response)) {
            if (isset($response->detail->desc)) {
                $debugData['result']['code'] .= $response->faultcode . '; ';
                $debugData['result']['error'] .= $response->detail->desc . '; ';
            } else {
                $debugData['result']['code'] .= 'Unknown error code; ';
                $debugData['result']['error'] .= 'Unknown error description; ';
            }

            $this->managerInterface->addErrorMessage(__('Logo image is not uploaded'));

            return ['error' => ['code' => $debugData['result']['code'], 'desc' => $debugData['result']['error'],
                'request' => $request/*, 'response' => $this->xml2array($response)*/]];
        } else {

            if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
                $this->managerInterface->addSuccessMessage(__('Logo image is success uploaded'));
            } else {
                $this->managerInterface->addErrorMessage(__('Logo image is not uploaded'));
            }
        }

        return $valueToDb;
    }

    public function xml2array($xmlObject)
    {
        $outString = json_encode($xmlObject);
        unset($xmlObject);
        return json_decode($outString, true);
    }

    protected function _getAuthDetails($type = 'ship', $major = 21)
    {
        return array(
            'WebAuthenticationDetail' => [
                'UserCredential' => [
                    'Key' => $this->UserID,
                    'Password' => $this->Password
                ]
            ],
            'ClientDetail' => [
                'AccountNumber' => $this->shipperNumber,
                'MeterNumber' => $this->meterNumber
            ],
            'TransactionDetail' => [
                'CustomerTransactionId' => '*** Express Domestic Shipping Request v10 using PHP ***'
            ],
            'Version' => [
                'ServiceId' => $type,
                'Major' => $major,
                'Intermediate' => '0',
                'Minor' => '0'
            ]
        );
    }

    protected function _createShipSoapClient()
    {
        return $this->_createSoapClient($this->_shipServiceWsdl, 1);
    }

    protected function _createRateSoapClient()
    {
        return $this->_createSoapClient($this->_rateServiceWsdl, 1);
    }

    protected function _createImageUploadSoapClient()
    {
        return $this->_createSoapClient($this->_imageServiceWsdl, 1);
    }

    protected function _createSoapClient($wsdl, $trace = false)
    {
        $client = new \SoapClient($wsdl, ['trace' => $trace, 'exceptions' => 0, 'cache_wsdl' => WSDL_CACHE_NONE]);
        $this->soapLocation = $this->testing == 1
            ? 'https://wsbeta.fedex.com:443/web-services'
            : 'https://ws.fedex.com:443/web-services';
        $client->__setLocation($this->soapLocation);

        return $client;
    }

    private function getCommodities($requestClient)
    {
        if (!empty($this->international_products)) {
            $i = 0;
            foreach ($this->international_products as $kp => $products) {
                $requestClient['RequestedShipment']['CustomsClearanceDetail']['Commodities'][$i] = [
                    'Weight' => [
                        'Units' => $this->weightUnits,
                        'Value' => round($products['weight'], 2)
                    ],
                    'NumberOfPieces' => ($kp + 1),
                    'CountryOfManufacture' => $products['country_code'],
                    'Description' => $products['description'],
                    'Quantity' => ceil($products['qty']),
                    'QuantityUnits' => 'pcs',
                    'UnitPrice' => [
                        'Currency' => $this->currencyCode,
                        'Amount' => round($products['price'] / $this->currencyCoefficient, 2)
                    ],
                    'CustomsValue' => [
                        'Currency' => $this->currencyCode,
                        'Amount' => round($products['price'] / $this->currencyCoefficient*ceil($products['qty']), 2)
                    ],
                ];
                if (!empty($products['harmonized'])) {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['Commodities'][$i]['HarmonizedCode']
                        = $products['harmonized'];
                }

                $i++;
            }
        }

        return $requestClient;
    }
}