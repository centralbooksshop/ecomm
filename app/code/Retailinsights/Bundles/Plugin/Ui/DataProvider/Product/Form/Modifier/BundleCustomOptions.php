<?php

namespace Retailinsights\Bundles\Plugin\Ui\DataProvider\Product\Form\Modifier;

use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Date;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundleCustomOptions as MagentoBundleCustomOptions;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Psr\Log\LoggerInterface;

class BundleCustomOptions
{

    const FIELD_CUSTOM_FIELD_OPTION_NAME = 'custom_field';
    const FIELD_CUSTOM_FIELD_DATE_TIME = 'updated_at';
    protected $adminSession;
    protected $storeManager;
    protected $request;
    protected $logger;
    protected $locator;

    public function __construct(
	    AdminSession $adminSession,
	    StoreManagerInterface $storeManager,
	    RequestInterface $request,
	    LoggerInterface $logger,
            LocatorInterface $locator
    ) {
    	$this->adminSession = $adminSession;
	$this->storeManager = $storeManager;
	$this->request = $request;
	$this->logger = $logger;
        $this->locator = $locator;
      }


    public function afterModifyMeta(MagentoBundleCustomOptions $subject, array $meta)
    {
	    $product = $this->locator->getProduct();
	    if (!$product || !$product->getId()) {
 		   return $meta;
            }
	    $websiteIds = $product->getWebsiteIds(); 
	    $this->logger->info('Product website IDs: ' . implode(',', $websiteIds)); 
	    $applyRestriction = false;
	    $schoolWebsiteId = 2;
	    if (in_array($schoolWebsiteId, $websiteIds)) {
		    $user = $this->adminSession->getUser();
		    if ($user) {
		        $role = strtolower($user->getRole()->getRoleName());
		        $this->logger->info('Admin role: ' . $role);
		        $allowedRoles = ['administrators', 'bom'];
		        if (!in_array($role, $allowedRoles)) {
		        	$applyRestriction = true;
	     	        }
    		    }
	     }
	     if ($applyRestriction) {
		$meta['bundle-items']['children']['bundle_options']
	        ['arguments']['data']['config']['hide_group_delete'] = true;
	        if (isset(
	            $meta['bundle-items']['children']['bundle_options']
	            ['children']['record']['children']['product_bundle_container']
	            ['children']['option_info']['children']['title']
	        )) {
        	    $meta['bundle-items']['children']['bundle_options']
	            ['children']['record']['children']['product_bundle_container']
	            ['children']['option_info']['children']['title']
	            ['arguments']['data']['config']['disabled'] = true;
	        }

        if (isset(
            $meta['bundle-items']['children']['bundle_options']
            ['children']['record']['children']['product_bundle_container']
            ['children']['bundle_selections']['children']['record']['children']['selection_qty']
        )) {
            $meta['bundle-items']['children']['bundle_options']
            ['children']['record']['children']['product_bundle_container']
            ['children']['bundle_selections']['children']['record']['children']['selection_qty']
            ['arguments']['data']['config']['disabled'] = true;
	}


	if (isset($meta['bundle-items']['children']['bundle_options']['children']['record']['children']['product_bundle_container']
            ['children']['bundle_selections']['children']['record']['children']['action_delete'])) {
		    $meta['bundle-items']['children']['bundle_options']['children']['record']['children']['product_bundle_container']
		    ['children']['bundle_selections']['children']['record']['children']['action_delete']
		   ['arguments']['data']['config']['visible'] = false;
	}

	if (isset(
    $meta['bundle-items']['children']['bundle_options']
    ['children']['record']['children']['product_bundle_container']
    ['children']['modal_set']
)) {
    $meta['bundle-items']['children']['bundle_options']
    ['children']['record']['children']['product_bundle_container']
    ['children']['modal_set']
    ['arguments']['data']['config']['visible'] = false;
}

if (isset(
    $meta['bundle-items']['children']
    ['bundle_header']['children']['add_button']
)) {
    $meta['bundle-items']['children']
    ['bundle_header']['children']['add_button']
    ['arguments']['data']['config']['visible'] = false;
}

if (isset(
        $meta['product-details']['children']['container_isbn']['children']['isbn']
    )) {
        $meta['product-details']['children']['container_isbn']['children']['isbn']
            ['arguments']['data']['config']['disabled'] = true;

        $meta['product-details']['children']['container_isbn']['children']['isbn']
            ['arguments']['data']['config']['notice'] = __('This field is locked for this store.');
}

  if (isset(
        $meta['product-details']['children']['container_sku']['children']['sku']
    )) {
        $meta['product-details']['children']['container_sku']['children']['sku']
            ['arguments']['data']['config']['disabled'] = true;

        $meta['product-details']['children']['container_sku']['children']['sku']
            ['arguments']['data']['config']['notice'] =
                __('SKU is locked for this store.');
  }

   } 


        if (isset($meta['bundle-items']['children']['bundle_options']['children']['record']['children']['product_bundle_container']['children']['bundle_selections']['children']['record']['children'])) {


            $meta['bundle-items']['children']['bundle_options']['children']['record']['children']['product_bundle_container']['children']['bundle_selections']['children']['record']['children'][static::FIELD_CUSTOM_FIELD_OPTION_NAME] = $this->getCuststomFieldOptionFieldConfig(125);
			$meta['bundle-items']['children']['bundle_options']['children']['record']['children']['product_bundle_container']['children']['bundle_selections']['children']['record']['children'][static::FIELD_CUSTOM_FIELD_DATE_TIME] = $this->getCuststomFieldDateFieldConfig(126);


            // Reorder table headings

            $action_delete = $meta['bundle-items']['children']['bundle_options']['children']['record']['children']['product_bundle_container']['children']['bundle_selections']['children']['record']['children']['action_delete'];
            unset($meta['bundle-items']['children']['bundle_options']['children']['record']['children']['product_bundle_container']['children']['bundle_selections']['children']['record']['children']['action_delete']);
            $meta['bundle-items']['children']['bundle_options']['children']['record']['children']['product_bundle_container']['children']['bundle_selections']['children']['record']['children']['action_delete'] = $action_delete;

            // There should be more convenient way to reorder table headings

        }

        return $meta;
    }

    protected function getCuststomFieldOptionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Options'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => static::FIELD_CUSTOM_FIELD_OPTION_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'default' => 'default',
                        'options' => [
                            [
                                'label' => __('select'),
                                'value' => '0',
                            ],
                            [
                                'label' => __('Will Be Given'),
                                'value' => '1',
                            ],
                            [
                                'label' => __('School Given'),
                                'value' => '2',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getCuststomFieldDateFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Updated Date'),
                        'formElement' => 'date',
                        'componentType' => Field::NAME,
                        'dataScope' => static::FIELD_CUSTOM_FIELD_DATE_TIME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
			            'dateFormat' => 'MM/dd/YYYY',
		                //'timeFormat' => 'hh:mm:ss',
			             'id' => 'datepicker',
                    ],
                ],
            ],
        ];

		
    }

}
