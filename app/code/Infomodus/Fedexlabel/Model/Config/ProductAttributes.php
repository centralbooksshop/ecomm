<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Fedexlabel\Model\Config;
class ProductAttributes implements \Magento\Framework\Option\ArrayInterface
{
    protected $collectionFactory;
    public function __construct(\Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $coll = $this->collectionFactory->create()->setOrder('main_table.frontend_label', 'ASC');
        $attributes = $coll->load()->getItems();
        $attributeArray = [[
            'label' => __('Not Selected'),
            'value' => ''
        ]];

        foreach($attributes as $attribute){
            $attributeArray[] = [
                'label' => $attribute->getData('frontend_label'),
                'value' => $attribute->getData('attribute_code')
            ];
        }
        return $attributeArray;
    }
}