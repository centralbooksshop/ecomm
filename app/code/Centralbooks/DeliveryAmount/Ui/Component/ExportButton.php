<?php
namespace Centralbooks\DeliveryAmount\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Authorization\Model\UserContextInterface;

class ExportButton extends \Magento\Ui\Component\ExportButton {

    const NAME = 'exportButton';
    protected $urlBuilder;

    public function __construct(
    ContextInterface $context, UrlInterface $urlBuilder, UserContextInterface $userContext, \Magento\User\Model\UserFactory $userFactory, array $components = [], array $data = []
    ) {
        parent::__construct($context, $urlBuilder, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->userContext = $userContext;
        $this->userFactory = $userFactory;
    }

    public function getComponentName() {
        return static::NAME;
    }

    public function prepare() {
        
        $userId = $this->userContext->getUserId();

        $context = $this->getContext();
        $config = $this->getData('config');

        $user = $this->userFactory->create()->load($userId);
        $role = $user->getRole();

        $roleids = array(1, 306);

        if (isset($config['options'])) {
            if (in_array($role->getRoleId(), $roleids)) {
                $options = [];
                foreach ($config['options'] as $option) {
                    $additionalParams = $this->getAdditionalParams($config, $context);
                    $option['url'] = $this->urlBuilder->getUrl($option['url'], $additionalParams);
                    $options[] = $option;
                }

                $config['options'] = $options;
            } else {
                $config = [];
            }


            $this->setData('config', $config);
        }

        parent::prepare();
    }

    protected function getAdditionalParams($config, $context) {
        $additionalParams = [];
        if (isset($config['additionalParams'])) {
            foreach ($config['additionalParams'] as $paramName => $paramValue) {
                if ('*' == $paramValue) {
                    $paramValue = $context->getRequestParam($paramName);
                }
                $additionalParams[$paramName] = $paramValue;
            }
        }
        return $additionalParams;
    }

}

