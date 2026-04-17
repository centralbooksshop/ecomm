<?php
namespace SchoolZone\Registration\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;


class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

	public function __construct(EavSetupFactory $eavSetupFactory)
	{
		$this->eavSetupFactory = $eavSetupFactory;
    }
    
	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		if (version_compare($context->getVersion(), '1.0.6', '<')) {
			$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

		$eavSetup->addAttribute(
			\Magento\Catalog\Model\Product::ENTITY,
			'school_name',
			[
                'group'    => 'School Attributes',
				'type'     => 'text',
				'label'    => 'School Name',
				'input'    => 'select',
				'visible'  => true,
                'required' => true,
                'source'   => 'SchoolZone\Registration\Model\Config\Source\Options',
				'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				
			]
        );
        
        $eavSetup->addAttribute(
			\Magento\Catalog\Model\Product::ENTITY,
			'board',
			[
                'group'    => 'School Attributes',
				'type'     => 'text',
				'label'    => 'Board',
				'input'    => 'select',
				'visible'  => true,
                'required' => true,
                'source'   => 'SchoolZone\Registration\Model\Config\Source\BoardOptions',
				'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				
			]
        );

        $eavSetup->addAttribute(
			\Magento\Catalog\Model\Product::ENTITY,
			'cities',
			[
                'group'    => 'School Attributes',
				'type'     => 'text',
				'label'    => 'City',
				'input'    => 'select',
				'visible'  => true,
                'required' => true,
                'source'   => 'SchoolZone\Registration\Model\Config\Source\CityOptions',
				'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				
			]
        );
        
		}
	}
}