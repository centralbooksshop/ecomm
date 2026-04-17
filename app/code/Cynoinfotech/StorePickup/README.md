## Store Pickup Magento 2 Extension

docs : http://docs.cynoinfotech.com/storepickup/
By: CynoInfotech Team
Platform: Magento 2 Community Edition
Email: cynoinfotech@gmail.com

###1 - Installation Guide

 * Download extension zip file from the account
 * Unzip the file
 * root of your Magento 2 installation and merge app folder {Magento Root}
 * folder structure --> app/code/Cynoinfotech/StorePickup
 * folder structure --> app/code/Cynoinfotech/Base

####2 -  Enable Extension :  
  
  After this run below commands from Magento 2 root directory to install module.
  
 * Run :cd < your Magento install dir >
 * php bin/magento cache:disable
 * php bin/magento module:enable Cynoinfotech_Base
 * php bin/magento module:enable Cynoinfotech_StorePickup
 * php bin/magento setup:upgrade
 * php bin/magento setup:static-content:deploy
 * rm -rf var/cache var/generation var/di var/cache  /generated

####3 - Configuration

- Go to Admin -> CynoInfotech ->  Stores / Import Store CSV / Configuration
- There are all configuration options 


/**********************************************************/
Extension Features:

- Enable/Disable extension from the backend.
- Create multiple pickup store location.
- This extension helps customer to choose specific store as per their convenience.
- Customer can choose pickup date and time at the checkout process.
- Admin can set store location using Google map.
- Display shipping address, pickup date and time, store working hours in order view page.
- Easy to install and configure.


Extension Information : 

Store Pickup Magento 2 extension offers both facilities to the customer that “buy online and pickup from store”. This extension helps customer to make order online and pickup their order from the store at selected convenience date and time.

The store pickup magento2 extension allows admin to add new shipping method ”store pickup” at checkout process. Admin can set multiple pickup store locations. Customer can choose nearest store during the checkout process. This extension allows admin to add store location using a custom pin icon on a Google map.

The Magento 2 store pickup extension helps you have an engaging overview of your store. You can easily add contact information of store such as store address, phone number, email address for customers convenient.

The customer can see store pickup address, shipping day and shipping time on their order detail page. At the same admin can see customer’s pickup order information on the backend order invoice page and shipping order information section.

/***************************************************/
