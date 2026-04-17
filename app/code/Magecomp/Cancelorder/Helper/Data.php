<?php
namespace Magecomp\Cancelorder\Helper;
use Magento\Store\Model\ScopeInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CANCEL_ORDER_ENABLE = 'cancelordersection/cancelordergroup/active';
    const CANCEL_ORDER_ADMIN_EMAIL_TEMPLATE = 'cancelordersection/emailopt/template';
    const CANCEL_ORDER_EMAIL_SENDER = 'cancelordersection/emailopt/emailsender';
    const CANCEL_ORDER_ADMIN_EMAIL_RECIPIENT = 'cancelordersection/emailopt/emailto';
    const CANCEL_ORDER_CUSTOMER_EMAIL_TEMPLATE = 'cancelordersection/emailopt/template_for_customer';
    const CANCEL_ORDER_CRON_EMAIL_TEMPLATE = 'cancelordersection/emailopt/template_for_cron';
    const CANCEL_ORDER_BUTTON_TEXT = 'cancelordersection/cancelordergroup/cancelorderbuttontext';
    const CANCEL_ORDER_COMMENT_ENABLED = 'cancelordersection/cancelordergroup/usecomment';
    const CANCEL_ORDER_REASONS = 'cancelordersection/cancelordergroup/cancelreasons';
    const CANCEL_ORDER_COMMENT_POPUP_HEADER_TEXT = 'cancelordersection/cancelordergroup/formheadertext';
    const CANCEL_ORDER_COMMENT_POPUP_NOTE = 'cancelordersection/cancelordergroup/formnote';
    const CANCEL_ORDER_CUSTOMER_GROUP = 'cancelordersection/cancelordergroup/customer_groups';
    const CANCEL_ORDER_STATUS = 'cancelordersection/cancelordergroup/order_status';
    const CANCEL_ORDER_AUTOCANCEL_ENABLE = 'cancelordersection/autocancel/autocancleorder';
    const CANCEL_ORDER_AUTOCANCEL_ORDERSTATUS = 'cancelordersection/autocancel/autoorder_status';
    const CANCEL_ORDER_AUTOCANCEL_PAYMENT_TIME = 'cancelordersection/autocancel/paymentmethod_time';

    protected $_urlBuilder;
    protected $_customerModel;
    protected $_serializer;
    protected $_appConfigScopeConfigInterface;
    protected $_paymentModelConfig;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerModel,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\Config\ScopeConfigInterface $appConfigScopeConfigInterface,
        \Magento\Payment\Model\Config $paymentModelConfig
    ) {
        parent::__construct($context);
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_customerModel = $customerModel;
        $this->_serializer = $serializer;
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_paymentModelConfig = $paymentModelConfig;
    }
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::CANCEL_ORDER_ENABLE, ScopeInterface::SCOPE_STORE);
    }
    public function isEnabledadmin($storeid)
    {
        $configValue = $this->scopeConfig->getValue(
            self::CANCEL_ORDER_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeid
        );
        return $configValue;
    }
    public function getCancelOrderUrl($order)
    {
        return $this->_urlBuilder
                ->getUrl('cancelorder/index/view',
                        ['order_id' => $order->getId(),'_secure' => true]);
    }
    public function isCancelOrderAllowed()
    {
        if($this->isEnabled() && $this->isValidCustomerGroup())
        {
            return true;
        }
        return false;
    }
    public function isValidCustomerGroup()
    {
        $customerGroups = explode(",", $this->scopeConfig->getValue(self::CANCEL_ORDER_CUSTOMER_GROUP, ScopeInterface::SCOPE_STORE));
        $customerGroupId = $this->_customerModel->getCustomerGroupId();
        if (in_array($customerGroupId, $customerGroups))
        {
            return true;
        }
        return false;
    }
    public function OrderStatusCheck($data){
        $status=explode(",", $this->scopeConfig->getValue(self::CANCEL_ORDER_STATUS, ScopeInterface::SCOPE_STORE));
        foreach($status as $var)
        {
            if($var==$data){
                return true;
            }
        }
    }
    public function getCancelReasons()
    {
        $cancelReasosns = $this->_serializer->unserialize($this->scopeConfig->getValue(self::CANCEL_ORDER_REASONS, ScopeInterface::SCOPE_STORE));
        $html="";
        foreach ($cancelReasosns as $reason) {
            $html.="<option>".$reason['filetype']."</option>";
        }
        return $html;
    }
    public function isCommentEnabled()
    {
        return $this->scopeConfig->getValue(self::CANCEL_ORDER_COMMENT_ENABLED, ScopeInterface::SCOPE_STORE);
    }
    public function getCancelOrderButtonText()
    {
        return $this->scopeConfig->getValue(self::CANCEL_ORDER_BUTTON_TEXT, ScopeInterface::SCOPE_STORE);
    }
    public function getPopupFormHeaderText()
    {
        return $this->scopeConfig->getValue(self::CANCEL_ORDER_COMMENT_POPUP_HEADER_TEXT, ScopeInterface::SCOPE_STORE);
    }
    public function getPopupFormNote()
    {
        return $this->scopeConfig->getValue(self::CANCEL_ORDER_COMMENT_POPUP_NOTE, ScopeInterface::SCOPE_STORE);
    }
    public function getCustomerEmailTemplate()
    {
        return $this->scopeConfig->getValue(self::CANCEL_ORDER_CUSTOMER_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE);
    }
    public function getCronEmailTemplate()
    {
        return $this->scopeConfig->getValue(self::CANCEL_ORDER_CRON_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE);
    }
    public function getAdminEmailTemplate()
    {
        return $this->scopeConfig->getValue(self::CANCEL_ORDER_ADMIN_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE);
    }
    public function getEmailSender()
    {
        return $this->scopeConfig->getValue(self::CANCEL_ORDER_EMAIL_SENDER, ScopeInterface::SCOPE_STORE);
    }
    public function getAdminEmailRecipient()
    {
        return $this->scopeConfig->getValue(self::CANCEL_ORDER_ADMIN_EMAIL_RECIPIENT, ScopeInterface::SCOPE_STORE);
    }
    public function isAutoEnabled()
    {
        return $this->scopeConfig->getValue(self::CANCEL_ORDER_AUTOCANCEL_ENABLE, ScopeInterface::SCOPE_STORE);
    }
    public function getPaymentstatus()
    {
        $status= $this->scopeConfig->getValue(self::CANCEL_ORDER_AUTOCANCEL_ORDERSTATUS, ScopeInterface::SCOPE_STORE);
        return $status;
    }
    public function getPaymentandtime()
    {
        $paymentwithtime = $this->_serializer->unserialize($this->scopeConfig->getValue(self::CANCEL_ORDER_AUTOCANCEL_PAYMENT_TIME, ScopeInterface::SCOPE_STORE));
        return $paymentwithtime;
    }
}
