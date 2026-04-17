<?php

namespace Retailinsights\Bundles\Plugin\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\General;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Psr\Log\LoggerInterface;

class GeneralModifier
{
    protected $locator;
    protected $adminSession;
    protected $storeManager;
    protected $request;
    protected $logger;

    public function __construct(
	    LocatorInterface $locator,
            AdminSession $adminSession,
            StoreManagerInterface $storeManager,
            RequestInterface $request,
            LoggerInterface $logger
    ) {
        $this->locator = $locator;
        $this->adminSession = $adminSession;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->logger = $logger;
    }

    public function afterModifyMeta(General $subject, array $meta)
    {
        $product = $this->locator->getProduct();
        if (!$product || !$product->getId()) {
            return $meta;
        }
	$websiteIds = $product->getWebsiteIds();
	$applyRestriction = false;
	$schoolWebsiteId = 2;
	if (in_array($schoolWebsiteId, $websiteIds)) {
        $user = $this->adminSession->getUser();
	if ($user) {
        $role = strtolower($user->getRole()->getRoleName());
        $allowedRoles = [
            'administrators',
            'bom'
        ];
        if (!in_array($role, $allowedRoles, true)) {
            $applyRestriction = true;
        }
       }
       }
	if ($applyRestriction) {
        if (isset(
            $meta['product-details']['children']['container_sku']['children']['sku']
        )) {
            $meta['product-details']['children']['container_sku']['children']['sku']
                ['arguments']['data']['config']['disabled'] = true;
        }

        if (isset(
            $meta['product-details']['children']['container_isbn']['children']['isbn']
        )) {
            $meta['product-details']['children']['container_isbn']['children']['isbn']
                ['arguments']['data']['config']['disabled'] = true;
	}
	if (isset(
        $meta['product-details']['children']['container_navision_item_number']
        ['children']['navision_item_number']
    )) {
        $meta['product-details']['children']['container_navision_item_number']
            ['children']['navision_item_number']
            ['arguments']['data']['config']['disabled'] = true;
    }

	}

        return $meta;
    }
}


