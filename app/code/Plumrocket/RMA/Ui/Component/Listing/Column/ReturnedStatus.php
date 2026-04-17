<?php

namespace Plumrocket\RMA\Ui\Component\Listing\Column;

use Magento\Framework\App\ResourceConnection;

class ReturnedStatus extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $resourceConnection;
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ){
         $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
              
               if($item['status'] == 'new'){
                    $item['status'] = '<span 
                    style="background-color:#1796ff;
                     display: inline-block;
                      padding: 0 15px;
                      color: #fff;
                      border-radius: 12px;
                      text-transform: uppercase;
                      font-size: 10px;
                      text-align: center;
                      line-height: 19px;"
                      class="new">Status Pending</span>';
               }
               if($item['status'] == 'authorized'){
                    $item['status'] = '<span 
                        style="background-color:#b5ca61;
                        display: inline-block;
                      padding: 0 15px;
                      color: #fff;
                      border-radius: 12px;
                      text-transform: uppercase;
                      font-size: 10px;
                      text-align: center;
                      line-height: 19px;"
                      class="new">Approved</span>';
               }
               if($item['status'] == 'closed'){
                    $item['status'] = '<span 
                        style=
                        "background-color:#7f7f7f;
                        display: inline-block;
                      padding: 0 15px;
                      color: #fff;
                      border-radius: 12px;
                      text-transform: uppercase;
                      font-size: 10px;
                      text-align: center;
                      line-height: 19px;" 
                      class="new">Cancelled</span>';
               }
               if($item['status'] == 'processed_closed'){
                    $item['status'] = '<span 
                    style="background-color:#cb260a;
                     display: inline-block;
                      padding: 0 15px;
                      color: #fff;
                      border-radius: 12px;
                      text-transform: uppercase;
                      font-size: 10px;
                      text-align: center;
                      line-height: 19px;" 
                      class="new">Package Sent</span>';
               }
               if($item['status'] == 'cancel_refund'){
                    $item['status'] = '<span 
                   
                    style="background-color:#ffd814;
                     display: inline-block;
                      padding: 0 15px;
                      color: #fff;
                      border-radius: 12px;
                      text-transform: uppercase;
                      font-size: 10px;
                      text-align: center;
                      line-height: 19px;" 
                      class="new">Resolved (Cancel/Refunded)</span>';
               } 
               if($item['status'] == 'replaced'){
                    $item['status'] = '<span 
                   
                    style="background-color:#4bd91d;
                     display: inline-block;
                     padding: 0 15px;
                      color: #fff;
                      border-radius: 12px;
                      text-transform: uppercase;
                      font-size: 10px;
                      text-align: center;
                      line-height: 19px;"
                      class="new">Resolved (Replaced)</span>';
               } 

            }

        }
       
        //Retailinsights/Pricerules/view/adminhtml/templares/buyxyz.phtml
        return $dataSource;
    }
}