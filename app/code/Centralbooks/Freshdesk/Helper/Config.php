<?php
namespace Centralbooks\Freshdesk\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const XML_PATH_ENABLE_PRODUCT_CRON =
        'freshdesk_configuration/freshdesk/enable_product_cron';

    public function isProductCronEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_PRODUCT_CRON,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
