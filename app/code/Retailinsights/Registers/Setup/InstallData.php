<?php

namespace Retailinsights\Registers\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;

class InstallData implements InstallDataInterface
{
	private $eavSetupFactory;

	public function __construct(EavSetupFactory $eavSetupFactory, Config $eavConfig)
	{
		$this->eavSetupFactory = $eavSetupFactory;
		$this->eavConfig       = $eavConfig;
	}

	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
		 $eavSetup->removeAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'mobile_number'
        );
		$eavSetup->addAttribute(
			\Magento\Customer\Model\Customer::ENTITY,
			'mobile_number',
			[
				'type'         => 'varchar',
				'label'        => 'Mobile Number',
				'input'        => 'text',
				'required'     => false,
				'visible'      => true,
				'user_defined' => true,
				'position'     => 999,
				'system'       => 0,
			]
		);
		$sampleAttribute = $this->eavConfig->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'mobile_number');

		// more used_in_forms ['adminhtml_checkout','adminhtml_customer','adminhtml_customer_address','customer_account_edit','customer_address_edit','customer_register_address']
		$sampleAttribute->setData(
			'used_in_forms',
			['adminhtml_customer',
			'adminhtml_customer_address',
			'customer_account_edit',
			'customer_address_edit',
			'customer_register_address']
		);
		$sampleAttribute->save();
	}
}