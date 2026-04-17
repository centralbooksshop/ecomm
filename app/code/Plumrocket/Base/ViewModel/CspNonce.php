<?php
/**
 * @package     Plumrocket_Base
 * @copyright   Copyright (c) 2024 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Base\ViewModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * @since 2.11.5
 */
class CspNonce implements ArgumentInterface
{
    public function getNonce(): string
    {
        if (class_exists(\Magento\Csp\Helper\CspNonceProvider::class)) {
            $cspNonceProvider = ObjectManager::getInstance()->get(\Magento\Csp\Helper\CspNonceProvider::class);
            return ' nonce="' . $cspNonceProvider->generateNonce() . '"';
        }

        return '';
    }
}
