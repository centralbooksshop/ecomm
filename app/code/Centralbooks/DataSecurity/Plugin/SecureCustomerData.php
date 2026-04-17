<?php

namespace Centralbooks\DataSecurity\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

class SecureCustomerData
{
    protected $authSession;
    protected $redirectFactory;
    protected $messageManager;

    public function __construct(
        Session $authSession,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager
    ) {
        $this->authSession = $authSession;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
    }

    public function aroundExecute($subject, $proceed)
    {
        $user = $this->authSession->getUser();

        /*if ($user && $user->getUserName() == 'cbsadmin') {
            return $proceed();
        } elseif ($user && $user->getUserName() == 'ravi2') {
            return $proceed();
        }*/

		if ($user && $user->getRole()) {
            $roleName = $user->getRole()->getRoleName();
            if (in_array($roleName, ['Administrators', 'Willbegiven Manager', 'Data Export Manager'])) {
                return $proceed();
            }
        }

        $this->messageManager->addErrorMessage(
            __('As per CBS policy, data export is restricted.')
        );

        $redirect = $this->redirectFactory->create();
        return $redirect->setRefererUrl();
    }
}