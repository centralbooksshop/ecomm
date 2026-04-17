<?php
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Deliveryboy;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\User\Model\UserFactory;
use Magento\Backend\Model\UrlInterface;

class Adminrole extends Action
{
    protected $jsonFactory;
    protected $userFactory;
    protected $urlBuilder;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        UserFactory $userFactory,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->userFactory = $userFactory;
        $this->urlBuilder = $urlBuilder;
    }

    public function execute()
    {
        $userId = $this->_auth->getUser()->getId();
        $user = $this->userFactory->create()->load($userId);
        $role = $user->getRole();
        $currentUrl = $this->urlBuilder->getCurrentUrl();
        $result = $this->jsonFactory->create();
        return $result->setData([
            "role" => $role->getRoleName(),
            "current_url" => $currentUrl,
        ]);
    }
}

