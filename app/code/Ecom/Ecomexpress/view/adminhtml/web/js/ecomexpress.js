/**
 * EcomExpress
  *
  * NOTICE OF LICENSE
  *
  * This source file is subject to the Open Source License (OSL 3.0)
  * It is also available through the world-wide-web at this URL:
  * http://opensource.org/licenses/osl-3.0.php
  *
  * @category    Ecom
  * @package     Ecom_Express
  * @author      Ecom Dev Team <developer@ecomexpress.com >
  * @copyright   Copyright EcomExpress (http://ecomexpress.com/)
  * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
    
    function popup(sUrl) {
    	
	    oPopup = new Window({
								id:"popup_window",
								className: "magento",
								windowClassName: "popup-window",
								title: "Current Order Status",
								recenterAuto:false,
								url: sUrl,
								width: 1100,
								height: 280,
								minimizable: false,
								maximizable: false,
								showEffectOptions: {
											duration: 0.4
												},
							   hideEffectOptions:{
											duration: 0.4
												},
						    	destroyOnClose: true
											});
								oPopup.setZIndex(100);
								oPopup.showCenter(true);
	
    }

    