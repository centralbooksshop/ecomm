<?php

namespace Shipsy\EcommerceExtension\Controller\Adminhtml\Setup;

class Formdata extends \Magento\Framework\App\Action\Action
{
    protected $_messageManager;
    protected $urlInterface;
    protected $_cookieManager;
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Shipsy\EcommerceExtension\Helper\Data $dataHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->_messageManager = $messageManager;
        $this->urlInterface = $urlInterface;
        $this->_cookieManager = $cookieManager;
        $this->scopeConfig = $scopeConfig;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
    }
    
    public function execute()
    {
        $organisation_id = $this->scopeConfig->getValue('configuration/services/organisation_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $base_url = $this->dataHelper->getBaseUrl($this->scopeConfig, $organisation_id);
        
        try {
            $postRequest = $this->getRequest()->getPostValue();
            if (isset($postRequest['useForwardCheck']) && $postRequest['useForwardCheck'] === 'true') {
                $useForwardAddress = true;
                $reverseAddress =  [
                    'name' => $postRequest['forward-name'],
                    'phone' => $postRequest['forward-phone'],
                    'alternate_phone' => (!empty($postRequest['forward-alt-phone'])) ? $postRequest['forward-alt-phone'] : $postRequest['forward-phone'],
                    'address_line_1' => $postRequest['forward-line-1'],
                    'address_line_2' => $postRequest['forward-line-2'],
                    'pincode' => $postRequest['forward-pincode'],
                    'city' => $postRequest['forward-city'],
                    'state' => $postRequest['forward-state'],
                    'country' => $postRequest['forward-country'],
                    'w3w_code' => $postRequest['forward-what3word-code'],
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
                    'country' => $postRequest['reverse-country'],
                    'w3w_code' => $postRequest['reverse-what3word-code'],
                ];
            }
            $dataToSendArray = [
                'forwardAddress' => [
                    'name' => $postRequest['forward-name'],
                    'phone' => $postRequest['forward-phone'],
                    'alternate_phone' => (!empty($postRequest['forward-alt-phone'])) ? $postRequest['forward-alt-phone'] : $postRequest['forward-phone'],
                    'address_line_1' => $postRequest['forward-line-1'],
                    'address_line_2' => $postRequest['forward-line-2'],
                    'pincode' => $postRequest['forward-pincode'],
                    'city' => $postRequest['forward-city'],
                    'state' => $postRequest['forward-state'],
                    'country' => $postRequest['forward-country'],
                    'w3w_code' => $postRequest['forward-what3word-code'],
                ],
                'reverseAddress' => $reverseAddress,
                
                'useForwardAddress' => $useForwardAddress,
                'exceptionalReturnAddress' => [
                    'name' => $postRequest['exp-return-name'],
                    'phone' => $postRequest['exp-return-phone'],
                    'alternate_phone' => (!empty($postRequest['exp-return-alt-phone'])) ? $postRequest['exp-return-alt-phone'] : $postRequest['exp-return-phone'],
                    'address_line_1' => $postRequest['exp-return-line-1'],
                    'address_line_2' => $postRequest['exp-return-line-2'],
                    'pincode' => $postRequest['exp-return-pincode'],
                    'city' => $postRequest['exp-return-city'],
                    'state' => $postRequest['exp-return-state'],
                    'country' => $postRequest['exp-return-country'],
                    'w3w_code' => $postRequest['exp-return-what3word-code'],
                ]
            ];
            $dataToSendJson = json_encode($dataToSendArray);
            // var_dump($dataToSendJson);
            $headers = [
                'Content-Type:application/json',
                'organisation-id:'.$organisation_id,
                'shop-origin:magento',
                'shop-url:'.$this->urlInterface->getBaseUrl(),
                'customer-id:'.$this->_cookieManager->getCookie('customer-id'),
                'access-token:'.$this->_cookieManager->getCookie('access-token-shipsy')
            ];

            $this->logger->debug("updateaddress save headers");
            $this->logger->log(100, json_encode($headers));

            $ch = curl_init($base_url . '/api/ecommerce/updateaddress');

            curl_setopt($ch, CURLOPT_POST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataToSendJson);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            curl_close($ch);
            $resultdata = json_decode($result, true);
            if (array_key_exists('success', $resultdata) && $resultdata['success'] === true) {
                $this->_messageManager->addSuccessMessage('Successfully updated address details');
            } else {
                $this->_messageManager->addErrorMessage($resultdata['error']['message']);
            }
        } catch (\Exception $e) {
            $this->_messageManager->addErrorMessage('Error' . $e->getMessage());
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setRefererOrBaseUrl();
    }
}
