require([
    'jquery'
], function ($) {
    'use strict';
    jQuery(document).ready(function(){
        jQuery(document).ajaxStop(function () {
           var def_status = $('[data-index="status"] input[type="checkbox"]').val();
           var def_select = $('[data-index="quantity_and_stock_status"] .admin__control-select').val();
           var def_dublicate = $('[data-index="is_duplicate"] input[type="checkbox"]').val();
           console.log('status instock dubli'+def_status+'_'+def_select+'_'+def_dublicate);

           if(def_dublicate == "1") {
             if(def_status == "2" && def_select == "0") {
              console.log('simple inside both');
             $('[data-index="status"] .admin__actions-switch-label').trigger('click');
             $('[data-index="quantity_and_stock_status"] .admin__control-select').val(1);
           } else if (def_status == "2") {
            console.log('simple product coming');
            $('[data-index="status"] .admin__actions-switch-label').trigger('click');
           }
           }

          
        });
    });
});