<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */

namespace Ubertheme\UbContentSlider\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    protected $attribute;
    protected $attributeFactory;
    protected $attributeSetFactory;
    protected $eavEntityType;
    protected $productHelper;

    public function __construct(
        \Magento\Eav\Model\Entity\Type $eavEntityType,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
        \Magento\Catalog\Helper\Product $productHelper
    ) {
        $this->eavEntityType = $eavEntityType;
        $this->attributeFactory = $attributeFactory;
        $this->attribute = $attribute;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->productHelper = $productHelper;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $entityType = $this->eavEntityType->loadByCode(\Magento\Catalog\Model\Product::ENTITY);
        $entityTypeId = $entityType->getId();
        
        if ($entityTypeId) {
            echo "\nStart Installing attribute...\n";
            
            $attributeCount = 0;
            $data['attribute_code'] = 'is_hot';
            
            $attribute = $this->attribute->loadByCode($entityTypeId, $data['attribute_code']);
            if (!$attribute->getId()) {
                $attribute = $this->attributeFactory->create();
            }

            $data['frontend_input'] = 'boolean';
            $data['source_model'] = $this->productHelper->getAttributeSourceModelByInputType(
                $data['frontend_input']
            );
            $data['backend_model'] = $this->productHelper->getAttributeBackendModelByInputType(
                $data['frontend_input']
            );
            $data += ['used_in_product_listing' => 1, 'is_filterable' => 0, 'is_filterable_in_search' => 0, 'apply_to' => []];
            $data['backend_type'] = $attribute->getBackendTypeByInput($data['frontend_input']);
            $data['default_value'] = 0;
            $data['frontend_label'] = 'Is Hot';

            $attribute->addData($data);
            $attribute->setIsUserDefined(1);
            $attribute->setEntityTypeId($entityTypeId);

            //save 
            $attribute->save();
            $attributeId = $attribute->getId();

            echo "Added attribute: Code:{$data['attribute_code']}, Id:{$attributeId} to Product's attributes.\n";

            //update attribute set, attribute groups
            $attributeSets = $entityType->getAttributeSetCollection();
            if ($attributeSets){
                foreach ($attributeSets as $attributeSet){
                    $attributeCount++;
                    $attributeGroupId = $attributeSet->getDefaultGroupId();

                    echo "Updated Attribute Set: {$attributeSet->getId()}, Attribite Group: {$attributeGroupId}\n";

                    $attribute = $this->attributeFactory->create();
                    $attribute
                        ->setId($attributeId)
                        ->setAttributeGroupId($attributeGroupId)
                        ->setAttributeSetId($attributeSet->getId())
                        ->setEntityTypeId($entityTypeId)
                        ->setSortOrder($attributeCount + 999)
                        ->save();
                }
            }
            
            echo "Finished.\n";
        }
    }
}
