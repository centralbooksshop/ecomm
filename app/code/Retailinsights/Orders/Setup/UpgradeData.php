<?php namespace Retailinsights\Orders\Setup;

  use Magento\Customer\Setup\CustomerSetupFactory;
  use Magento\Customer\Model\Customer;
  use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
  use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
  use Magento\Framework\Setup\UpgradeDataInterface;
  use Magento\Framework\Setup\ModuleContextInterface;
  use Magento\Framework\Setup\ModuleDataSetupInterface;
  use Magento\Eav\Setup\EavSetupFactory;

  class UpgradeData implements UpgradeDataInterface
  {

      /**
       * CustomerSetupFactory
       * @var CustomerSetupFactory
       */
      protected $customerSetupFactory;

      /**
       * $attributeSetFactory
       * @var AttributeSetFactory
       */
      private $attributeSetFactory;

      private $eavSetupFactory;

      /**
       * initiate object
       * @param CustomerSetupFactory $customerSetupFactory
       * @param AttributeSetFactory $attributeSetFactory
       */
      public function __construct(
          CustomerSetupFactory $customerSetupFactory,
          AttributeSetFactory $attributeSetFactory,
          EavSetupFactory $eavSetupFactory
      )
      {
          $this->customerSetupFactory = $customerSetupFactory;
          $this->attributeSetFactory = $attributeSetFactory;
          $this->eavSetupFactory = $eavSetupFactory;
      }

      /**
       * install data method
       * @param ModuleDataSetupInterface $setup
       * @param ModuleContextInterface $context
       */
      public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
      {
		  if (version_compare($context->getVersion(), '1.0.3') < 0) { 
            $setup->startSetup();
            // product attribute 'weight' as mandatory field 
            // updateAttribute(entity_type_id, attribute_id, column_name, value, null)
            $this->eavSetupFactory->updateAttribute(4,82,'is_required',1,null); 
            $setup->endSetup(); 
			}
			
		    if (version_compare($context->getVersion(), '1.0.5') < 0) {
              /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();

            /** @var $attributeSet AttributeSet */
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            $setup->startSetup();
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
	 		$eavSetup->addAttribute(
	              \Magento\Catalog\Model\Product::ENTITY,
	              'navision_item_number',
	              [
	                'type' => 'text',
	                'backend' => '',
	                'frontend' => '',
	                'label' => 'Navision Item Number',
	                'input' => 'text',
	                'class' => '',
	                'source' => '',
	                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
	                'visible' => true,
	                'required' => false,
	                'user_defined' => false,
	                'default' => '',
	                'searchable' => false,
	                'filterable' => false,
	                'comparable' => false,
	                'visible_on_front' => false,
	                'used_in_product_listing' => true,
	                'unique' => false,
	                'apply_to' => 'simple'
	              ]
	            );
			$setup->endSetup();
			}
		}
}

