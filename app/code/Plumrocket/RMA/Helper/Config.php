<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\File\Size;
use Magento\Framework\Filesystem;
use Plumrocket\Base\Api\ConfigUtilsInterface;

class Config extends AbstractHelper
{
    /**
     * @var Size
     */
    protected $fileSizeService;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var \Plumrocket\Base\Api\ConfigUtilsInterface
     */
    private $configUtils;

    /**
     * @param \Magento\Framework\App\Helper\Context     $context
     * @param \Magento\Framework\File\Size              $fileSize
     * @param \Magento\Framework\Filesystem             $filesystem
     * @param \Plumrocket\Base\Api\ConfigUtilsInterface $configUtils
     */
    public function __construct(
        Context $context,
        Size $fileSize,
        Filesystem $filesystem,
        ConfigUtilsInterface $configUtils
    ) {
        $this->fileSizeService = $fileSize;
        $this->filesystem = $filesystem;
        parent::__construct($context);
        $this->configUtils = $configUtils;
    }

    /**
     * Get return information positions
     *
     * @return array
     */
    public function getReturnPositions()
    {
        return explode(',', (string) $this->configUtils->getConfig(
            'prrma/general/return_placement'
        ));
    }

    /**
     * Get store address
     *
     * @return string
     */
    public function getStoreAddress()
    {
        return trim((string) $this->configUtils->getConfig(
            'prrma/general/store_address'
        ));
    }

    /**
     * Get auto clode period
     *
     * @return int
     */
    public function getAutoClose()
    {
        return (int)$this->configUtils->getConfig(
            'prrma/general/auto_close'
        );
    }

    /**
     * Retrieve if tracking number is enabled for customer
     *
     * @return bool
     */
    public function enabledTrackingNumber()
    {
        return $this->configUtils->isSetFlag(
            'prrma/general/tracking_number'
        );
    }

    /**
     * Get shipping carriers list
     *
     * @return array
     */
    public function getShippingCarriers()
    {
        $list = explode(',', (string) $this->configUtils->getConfig(
            'prrma/general/shipping_carriers'
        ));

        array_walk($list, function (&$value) {
            $value = trim($value);
        });

        return array_combine($list, $list);
    }

    /**
     * Get count of shipping label files
     *
     * @return int
     */
    public function getShippingLabelCount()
    {
        return 1;
    }

    /**
     * Check possibility to creating return on frontend
     *
     * @return bool
     */
    public function allowCreateOnFrontend()
    {
        return $this->configUtils->isSetFlag(
            'prrma/newrma/allow'
        );
    }

    /**
     * Get default manager id for new return
     *
     * @return int
     */
    public function getDefaultManagerId()
    {
        return (int)$this->configUtils->getConfig(
            'prrma/newrma/default_manager'
        );
    }

    /**
     * Check is required return policy
     *
     * @return bool
     */
    public function enabledReturnPolicy()
    {
        return $this->configUtils->isSetFlag(
            'prrma/newrma/return_policy'
        );
    }

    /**
     * Get policy block id
     *
     * @return string
     */
    public function getReturnPolicyBlock()
    {
        return $this->configUtils->getConfig(
            'prrma/newrma/return_policy_block'
        );
    }

    /**
     * Check if can to authorize items
     *
     * @return bool
     */
    public function canAutoAuthorize()
    {
        return $this->configUtils->isSetFlag(
            'prrma/newrma/auto_authorize'
        );
    }

    /**
     * Get return success message block id
     *
     * @return string
     */
    public function getReturnSuccessBlock()
    {
        return $this->configUtils->getConfig(
            'prrma/newrma/return_success'
        );
    }

    /**
     * Get return instruction message block id
     *
     * @return string
     */
    public function getReturnInstructionsBlock()
    {
        return $this->configUtils->getConfig(
            'prrma/newrma/return_instructions'
        );
    }

    /**
     * Get sender name
     *
     * @return string
     */
    public function getSenderName($store = null)
    {
        return trim((string) $this->configUtils->getConfig(
            'prrma/email/sender_name',
            $store
        ));
    }

    /**
     * Get sender email
     *
     * @return string
     */
    public function getSenderEmail($store = null)
    {
        return trim((string) $this->configUtils->getConfig(
            'prrma/email/sender_email',
            $store
        ));
    }

    /**
     * Get email template by recipient type and email type
     *
     * @param string $recipient
     * @param string $emailType
     * @param        $store
     * @return string
     */
    public function getEmailTemplate(
        string $recipient,
        string $emailType,
        $store = null
    ): string {
        if (! in_array($recipient, ['customer', 'manager'])) {
            return '';
        }

        switch ($emailType) {
            case 'new':
                $field = 'new_template';
                break;
            case 'update':
                $field = 'update_template';
                break;
            default:
                return '';
        }

        return (string) $this->configUtils->getConfig(
            "prrma/email/email_{$recipient}/{$field}",
            $store
        );
    }

    /**
     * Get additional email addresses by recipient type and email type
     *
     * @param string $recipient
     * @param string $emailType
     * @param        $store
     * @return string
     */
    public function getAdditionalAddresses(
        string $recipient,
        string $emailType,
        $store = null
    ): string {
        if (! in_array($recipient, ['customer', 'manager'])) {
            return '';
        }

        switch ($emailType) {
            case 'new':
                $field = 'new_copy';
                break;
            case 'update':
                $field = 'update_copy';
                break;
            default:
                return '';
        }

        return (string) $this->configUtils->getConfig(
            "prrma/email/email_{$recipient}/{$field}",
            $store
        );
    }

    /**
     * Get new email template to customer
     *
     * @return string
     */
    public function getCustomerNewTemplate($store = null)
    {
        return trim($this->getEmailTemplate('customer', 'new', $store));
    }

    /**
     * Get additional addresses for new email to customer
     *
     * @return array
     */
    public function getCustomerNewEmails($store = null)
    {
        $emails = explode(',', $this->getAdditionalAddresses(
            'customer',
            'new',
            $store
        ));

        foreach ($emails as $key => &$value) {
            $value = trim($value);
            if (! $value) {
                unset($emails[$key]);
            }
        }

        return array_unique($emails);
    }

    /**
     * Get update email template to customer
     *
     * @return string
     */
    public function getCustomerUpdateTemplate($store = null)
    {
        return trim($this->getEmailTemplate('customer', 'update', $store));
    }

    /**
     * Get additional addresses for update email to customer
     *
     * @return array
     */
    public function getCustomerUpdateEmails($store = null)
    {
        $emails = explode(',', $this->getAdditionalAddresses(
            'customer',
            'update',
            $store
        ));

        foreach ($emails as $key => &$value) {
            $value = trim($value);
            if (! $value) {
                unset($emails[$key]);
            }
        }

        return array_unique($emails);
    }

    /**
     * Get new email template to manager
     *
     * @return string
     */
    public function getManagerNewTemplate($store = null)
    {
        return trim($this->getEmailTemplate('manager', 'new', $store));
    }

    /**
     * Get additional addresses for new email to manager
     *
     * @return array
     */
    public function getManagerNewEmails($store = null)
    {
        $emails = explode(',', $this->getAdditionalAddresses(
            'manager',
            'new',
            $store
        ));

        foreach ($emails as $key => &$value) {
            $value = trim($value);
            if (! $value) {
                unset($emails[$key]);
            }
        }

        return array_unique($emails);
    }

    /**
     * Get update email template to manager
     *
     * @return string
     */
    public function getManagerUpdateTemplate($store = null)
    {
        return trim($this->getEmailTemplate('manager', 'update', $store));
    }

    /**
     * Get additional addresses for update email to manager
     *
     * @return array
     */
    public function getManagerUpdateEmails($store = null)
    {
        $emails = explode(',', $this->getAdditionalAddresses(
            'manager',
            'update',
            $store
        ));

        foreach ($emails as $key => &$value) {
            $value = trim($value);
            if (! $value) {
                unset($emails[$key]);
            }
        }

        return array_unique($emails);
    }

    /**
     * Get allowed file types
     *
     * @return array
     */
    public function getFileAllowedExtensions()
    {
        $types = explode(',', (string) $this->configUtils->getConfig(
            'prrma/file/type'
        ));

        array_walk($types, function (&$value) {
            $value = trim($value);
        });

        return $types;
    }

    /**
     * Get max size of file
     *
     * @param bool $inBytes
     * @return int
     */
    public function getFileMaxSize($inBytes = false)
    {
        $size = (int)$this->configUtils->getConfig('prrma/file/size');
        $size *= 1024 * 1024;
        $size = min($size, $this->fileSizeService->getMaxFileSize());

        if (! $inBytes) {
            // $size /= (1024 * 1024);
            $size = $this->fileSizeService->getFileSizeInMb($size);
        }

        return $size;
    }

    /**
     * Get max count of files
     *
     * @return int
     */
    public function getFileMaxCount()
    {
        $count = (int)$this->configUtils->getConfig('prrma/file/count');
        return max(1, $count);
    }

    /**
     * Filesystem directory path of temporary files
     *
     * @param bool $full
     * @return string
     */
    public function getBaseTmpMediaPath($full = true)
    {
        $path = 'tmp/prrma';
        if ($full) {
            $path = $this->filesystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath($path);
        }
        return $path;
    }

    /**
     * Filesystem directory path of stable files
     *
     * @param bool $full
     * @return string
     */
    public function getBaseMediaPath($full = true)
    {
        $path = 'prrma';
        if ($full) {
            $path = $this->filesystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath($path);
        }
        return $path;
    }
}
