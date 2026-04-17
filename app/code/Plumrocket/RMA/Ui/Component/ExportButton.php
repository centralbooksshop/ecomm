<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Plumrocket\RMA\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class ExportButton
 */
class ExportButton extends \Magento\Ui\Component\ExportButton {

    /**
     * Component name
     */
    const NAME = 'exportButton';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
    ContextInterface $context, UrlInterface $urlBuilder, UserContextInterface $userContext, \Magento\User\Model\UserFactory $userFactory, array $components = [], array $data = []
    ) {
        parent::__construct($context, $urlBuilder, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->userContext = $userContext;
        $this->userFactory = $userFactory;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName() {
        return static::NAME;
    }

    /**
     * @return void
     */
    public function prepare() {
        
        $userId = $this->userContext->getUserId();

        $context = $this->getContext();
        $config = $this->getData('config');

        $user = $this->userFactory->create()->load($userId);
        $role = $user->getRole();

        
        $roleids = array(1, 158);

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

    /**
     * Get export button additional parameters
     *
     * @param array $config
     * @param ContextInterface $context
     * @return array
     */
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
