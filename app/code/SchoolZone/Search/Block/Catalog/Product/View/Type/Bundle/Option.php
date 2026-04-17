<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SchoolZone\Search\Block\Catalog\Product\View\Type\Bundle;


/**
 * Bundle option renderer
 * @api
 * @since 100.0.2
 */
class Option extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Checkbox
{
    // protected $postCollection;
     /**
     * @param \Magento\Catalog\Model\Product $selection
     * @param bool $includeContainer
     * @return string
     */

    public function getSelectionQtyTitleTable($selection, $includeContainer = true)
    {   

        $result =array();
        $this->setFormatProduct($selection);
      
        
        $spl_price= $selection->getFinalPrice();
       // $spl_price = $selection->getSpecialPrice();
       $price = $selection->getPrice();

        $qty = (int)$selection->getSelectionQty();
        $subtotal = $qty*$price;
        $dis_subtotal = $qty*$spl_price;

        $priceTitle['1'] = (int)$selection->getSelectionQty();
        $priceTitle['2'] = $this->escapeHtml($selection->getName());
        if($price > $spl_price){
           $priceTitle['3'] = '<span class="bundle-oldprice product-spl-enabled"> ₹'.number_format($price,2).'</span>  ₹'.number_format($spl_price,2);
          } else {
              $priceTitle['3'] = number_format($price,2);
          }
      //substr($price, 0, -2);             //$this->renderPriceString($selection, $includeContainer);
        $priceTitle['4'] = $subtotal.'.00';
        if($price > $spl_price){
           $priceTitle['4'] = '<span class="bundle-oldprice"> ₹'.number_format($subtotal,2).'</span>  ₹'.number_format($dis_subtotal,2);
          } else {
              $priceTitle['4'] = number_format($subtotal,2);
          }
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $parentProduct = $objectManager->get('Magento\Framework\Registry')->registry('current_product');
            
           



            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('catalog_product_bundle_selection');
            $sql = $connection->select()->from($tableName)->where('parent_product_id = ?', $parentProduct->getId());
            // $sql = $connection->select()->from($tableName);
            $result = $connection->fetchAll($sql);  

            foreach ($result as $key => $value) {
                if($value['product_id'] == $selection->getId()){
                   if($value['custom_field'] == 1){
                        $priceTitle['5']='This will delivered in 5-7* bussiness days';  
                    }
                    if($value['custom_field'] == 2){
                        $priceTitle['6']='This item will be issued by school';  
                    }
                }
            }

        array_push($result, $priceTitle); 
        return $result;
    }
     /**
     * @param \Magento\Catalog\Model\Product $selection
     * @param bool $includeContainer
     * @return string
     */
    public function getSelectionQtyTitlePrice($selection, $includeContainer = true)
    {
        $this->setFormatProduct($selection);
        $priceTitle = '<span class="product-name">'
            . $selection->getSelectionQty() * 1
            . ' x '
            . $this->escapeHtml($selection->getName())
            . '</span>';

          $spl_price= $selection->getFinalPrice();
        $price = $selection->getPrice();
        $qty = (int)$selection->getSelectionQty();
        $subtotal = $qty*$price; 
         $dis_subtotal = $qty*$spl_price;  


        $amt = $this->renderPriceString($selection, $includeContainer);
        
        $custom_data = '<span class="product-name">'.$this->escapeHtml($selection->getName());
        $custom_data .= '</td>';
        $custom_data .= '<td>'.substr($selection->getSelectionQty(), 0, -5).'<td>';
        // $custom_data .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '') .
        //     $this->renderPriceString($selection, $includeContainer) . ($includeContainer ? '</span>' : '');
         if($price > $spl_price)
            $custom_data .= '<span class="bundle-oldprice product-spl-enabled"> ₹'.number_format($price,2).'</span>  ₹'.number_format($spl_price,2);
        else
          $custom_data .= ' &nbsp; ₹'.number_format($price,2);        

        $custom_data .= '</td>';
          if($price > $spl_price)
         $custom_data .= '<td><span class="bundle-oldprice"> ₹'.number_format($subtotal,2).'</span>  ₹'.number_format($dis_subtotal,2).'</td>';
        else
         $custom_data .= '<td>₹'.$subtotal.'.00</td>';
        
        $custom_data .= '</span>';

        return $custom_data;
    }

     /**
     * Get title price for selection product
     *
     * @param Product $selection
     * @param bool $includeContainer
     * @return string
     */
    public function getSelectionTitlePrice($selection, $includeContainer = true)
    {
         $spl_price= $selection->getFinalPrice();
        $price = $selection->getPrice();
         
        $priceTitle = '<span class="product-name">' . $this->escapeHtml($selection->getName()) . '</span>';
         if($price > $spl_price)
            $priceTitle .= ' &nbsp; + &nbsp; <span class="bundle-oldprice product-spl-enabled"> ₹'.number_format($price,2).'</span>  ₹'.number_format($spl_price,2);
          else 
               $priceTitle .= ' &nbsp; + &nbsp;₹'.number_format($price,2); 
                 
        // $priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '') . '+'
        //     . $this->renderPriceString($selection, $includeContainer) . ($includeContainer ? '</span>' : '');
        return $priceTitle;
    }

}
