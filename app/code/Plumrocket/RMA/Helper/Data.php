<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\Base\Api\ConfigUtilsInterface;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;

class Data extends AbstractHelper
{
    /**
     * Config section id
     */
    public const SECTION_ID = 'prrma';

    /**
     * Param name for store form data in session
     */
    private const FORM_DATA_PARAM = 'prrma_form_data';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var State
     */
    private $state;

    /**
     * Storage of form data
     *
     * @var mixed
     */
    private $formData = null;

    /**
     * @var \Plumrocket\Base\Api\ConfigUtilsInterface
     */
    private $configUtils;

    /**
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Customer\Model\Session            $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\State               $state
     * @param \Plumrocket\Base\Api\ConfigUtilsInterface  $configUtils
     */
    public function __construct(
        Context $context,
        Session $session,
        StoreManagerInterface $storeManager,
        State $state,
        ConfigUtilsInterface $configUtils
    ) {
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->state = $state;
        parent::__construct($context);
        $this->configUtils = $configUtils;
    }

    /**
     * Is module enabled
     *
     * @param  int $store store id
     * @return boolean
     */
    public function moduleEnabled($store = null)
    {
        return $this->configUtils->isSetFlag('prrma/general/enabled', $store);
    }

    /**
     * Retrieve store address
     *
     * @return string
     */
    public function getStoreAddress()
    {
        $address = $this->storeManager->getStore()->getFormattedAddress();
        if (! preg_match('#[A-Za-z0-9]+#', strip_tags($address))) {
            $address = '';
        }

        return strip_tags($address);
    }

    /**
     * Get store identifiers
     *
     * @return  array
     */
    public function getStoreIds()
    {
        $stores = $this->storeManager->getStores();
        $ids = [];

        foreach ($stores as $store) {
            $ids[] = $store['store_id'];
        }

        return $ids;
    }

    /**
     * Store form data
     *
     * @param mixed $data
     * @return void
     */
    public function setFormData($data = null)
    {
        if (null === $data) {
            $data = $this->_getRequest()->getParams();
        }

        $this->session->setData(self::FORM_DATA_PARAM, $data);
    }

    /**
     * Store form data with other data
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function addFormData($key, $value)
    {
        $formData = $this->getFormData() ?: [];
        $formData[$key] = $value;
        $this->setFormData($formData);
    }

    /**
     * Retrieve stored form data
     *
     * @param  null|string $key
     * @return mixed
     */
    public function getFormData($key = null)
    {
        if (null === $this->formData) {
            $this->formData = $this->session
                ->getData(self::FORM_DATA_PARAM, false);
        }

        if (null !== $key) {
            return $this->formData[$key] ?? null;
        }

        return $this->formData;
    }

    /**
     * Get store name
     *
     * @return string
     */
    public function getStoreName()
    {
        if (! $name = $this->storeManager->getStore()->getFrontendName()) {
            $name = __('Store Owner');
        }

        return $name;
    }

    /**
     * Check if current request is backend
     *
     * @return boolean
     */
    public function isBackend()
    {
        return $this->state->getAreaCode() === Area::AREA_ADMINHTML;
    }

    /**
     * Get color class of return status
     *
     * @param  string $status
     * @param  bool   $isAdminHtml
     * @return string
     */
    public function getStatusColor($status, $isAdminHtml = false)
    {
        $class = 'prrma-status ';
        switch (true) {
            case ReturnsStatus::STATUS_PROCESSED_CLOSED == $status:
            case ReturnsStatus::STATUS_APPROVED_PART == $status && $isAdminHtml:
                $class .= 'prrma-status-processed_closed';
                break;

            case ReturnsStatus::STATUS_REJECTED == $status:
            case ReturnsStatus::STATUS_REJECTED_PART == $status && $isAdminHtml:
                $class .= 'prrma-status-rejected';
                break;

            case ReturnsStatus::STATUS_CANCELLED == $status:
                $class .= 'prrma-status-closed';
                break;

            case ReturnsStatus::STATUS_RECEIVED == $status && $isAdminHtml:
            case ReturnsStatus::STATUS_RECEIVED_PART == $status && $isAdminHtml:
                $class .= 'prrma-status-received';
                break;

            case ReturnsStatus::STATUS_AUTHORIZED == $status && $isAdminHtml:
            case ReturnsStatus::STATUS_AUTHORIZED_PART == $status && $isAdminHtml:
                $class .= 'prrma-status-authorized';
                break;

            default:
                $class .= 'prrma-status-' . $status;
        }

        return $class;
    }
}
