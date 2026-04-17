<?php
namespace Centralbooks\DeliveryAmount\Model\Deliveryboy\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Backend\Model\Auth\Session as AuthSession;

class Deliverytype implements ArrayInterface
{
    protected $authSession;

    public function __construct(
        AuthSession $authSession
    ) {
        $this->authSession = $authSession;
    }


    public function toOptionArray()
    {

	$currentUser = $this->authSession->getUser();
	$userName = strtolower($currentUser->getUsername());
	$currentRole = $currentUser->getRole()->getRoleName();

        switch ($currentRole) {
	case 'Parent Delivery Boy Creation':
		return $this->getParentOptions();
		break;
	case 'Administrators':
		return $this->getAdminOptions();
		break;
	case 'Sub Admin':
		if($userName == "admin"){
                	return $this->getAdminOptions();
			break;
		}
        default:
                return $this->getDefaultOptions();
	}
    }

     protected function getParentOptions()
    {
	    return [
            
            ['value' => 'Parent', 'label' => __('Parent')],
        ];
     }

    protected function getAdminOptions()
    {
	    return [
		['value' => 'none', 'label' => __('None')],
		['value' => 'Parent', 'label' => __('Parent')],
		['value' => 'Child', 'label' => __('Child')],
        ];
     }

    protected function getDefaultOptions()
    {
        return [
            ['value' => 'Child', 'label' => __('Child')],
        ];
    }
}

