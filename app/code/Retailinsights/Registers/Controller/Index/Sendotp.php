<?php
 
namespace Retailinsights\Registers\Controller\Index;
 
use Magento\Framework\App\Action\Context;
 
class Sendotp extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_customer;
    protected $_customerFactory;
    protected $_sessionFactory;
    protected $scopeConfig;
    protected $helperData;
 
    public function __construct(
        \Retailinsights\Registers\Helper\Data $helperData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Context $context, 
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Customer\Model\Customer $customers

    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_sessionFactory = $sessionFactory;
        $this->_customerFactory = $customerFactory;
        $this->_customer = $customers;
        $this->_resultPageFactory = $resultPageFactory;
        $this->helperData=$helperData;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $mobile = $this->getRequest()->getPost('mobile');
        $isPhone = $this->getRequest()->getPost('isPhone');
        
        $code = $this->getRequest()->getPost('code');
        $type = $this->getRequest()->getPost('type');
        if($code == 'resend_otp'){
            if($isPhone){
                $this->sendAndResend($type,$mobile);
            }else{
               $this->helperData->sendEmail($mobile);   
            }
        }

        if($code=="sendotp"){
            if($isPhone == 'true'){
                $customerData=$this->_customerFactory->create()->addAttributeToSelect('*')->addAttributeToFilter('mobile_number',$mobile);
            }else{
                $customerData=$this->_customerFactory->create()->addAttributeToSelect('*')->addAttributeToFilter('email',$mobile);
            }

            if(empty($customerData->getData())){
                if($type=="login"){
                    echo "new_user";
                }else if($type=="register"){
                    //$this->sendAndResend($type,$mobile);
					if($isPhone=="true"){
                        $this->sendAndResend($type,$mobile);
                    }else{
                        $this->helperData->sendEmail($mobile);
                    }
                }
            }else{
                if($type=="login"){
                    if($isPhone=="true"){
                        $this->sendAndResend($type,$mobile);
                    }else{
                        $this->helperData->sendEmail($mobile);
                    }
                }else if($type=="register") {
                    echo "registered";               
                }
            }
        }
    }

    public function loginOrRegisterType($type,$mobile,$msg)
    {
        $otp=$this->helperData->GenrateOtp("Use code",$msg,"Y",$mobile,$type);
        $sms = $this->helperData->SendSms("Use code",$msg,"Y",$otp,$mobile);

        if($sms==''){
            if($type=="user_otp_regi"){
                $_SESSION["user_mobile_regi"] = $mobile;
                echo "new_user";
            }else{
                $_SESSION['start'] = time();
                echo "registered";              
            }
        }else{
         echo "sms service error";
        }   
    }

    public function sendAndResend($type,$mobile){
        if($type=="login"){
            $login = "Login";
            // $sms=$this->loginOrRegisterType('user_otp_regi',$mobile,'To ',$login,' for centralbooksonline account.');

            // $sms=$this->loginOrRegisterType('otp',$mobile,'To Login for centralbooksonline account');
            $sms=$this->loginOrRegisterType('otp',$mobile,'to login for centralbooksonline.');
        }else if($type=="register"){
            $sms=$this->loginOrRegisterType('user_otp_regi',$mobile,'to register for centralbooksonline account.');
            // $sms=$this->loginOrRegisterType('register',$mobile,'to register for centralbooksonline account.');
        }
    }

}
