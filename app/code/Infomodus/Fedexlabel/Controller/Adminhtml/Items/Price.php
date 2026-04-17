<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Items;

class Price extends \Infomodus\Fedexlabel\Controller\Adminhtml\Items
{
    public function execute()
    {
        $content = '';
        if ($this->getRequest()->getPostValue()) {
            $order_id = $this->getRequest()->getParam('order_id');
            $type = $this->getRequest()->getParam('type');
            $params = $this->getRequest()->getParams();
            $order = $this->orderRepository->get($order_id);

            $arrPackagesOld = $params['package'];
            if (count($arrPackagesOld) > 0) {
                foreach ($arrPackagesOld as $k => $v) {
                    $i = 0;
                    foreach ($v as $d => $f) {
                        $arrPackages[$i][$k] = $f;
                        $i += 1;
                    }
                }
                unset($v, $k, $i, $d, $f);
                $params['package'] = $arrPackages;
            }

            $price = $this->_handy->getLabel($order, 'ajaxprice_'.$type, null, $params);
            if (!is_array($price) && strlen($price) > 0) {
                $price = json_decode($price, true);
                if (isset($price['price'])) {
                    $content .=  __('Price').': '.$price['price']['def']['MonetaryValue'][0].''.$price['price']['def']['CurrencyCode'][0];
                    if(isset($price['price']['negotiated']) && is_array($price['price']['negotiated']) && count($price['price']['negotiated']) > 0){
                        $content .= '<br />'.__('Negotiated Price').': '.$price['price']['negotiated']['MonetaryValue'][0].''.$price['price']['negotiated']['CurrencyCode'][0];
                    }
                }
                $this->getResponse()
                    ->setContent($content);
                return;
            } elseif (is_array($price) && count($price) > 0) {
                $this->getResponse()
                    ->setContent(json_encode($price));
                return;
            } else {
                $this->getResponse()
                    ->setContent(__('Error (price 1001)'));
                return;
            }


        }
    }
}
