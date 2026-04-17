# Mage2 Module Centralbooks EcommerceOrders

    ``centralbooks/module-ecommerceorders``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Centralbooks Ecommerce Orders

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Centralbooks`
 - Enable the module by running `php bin/magento module:enable Centralbooks_EcommerceOrders`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer 
 - Install the module composer by running `composer require centralbooks/module-ecommerceorders`
 - enable the module by running `php bin/magento module:enable Centralbooks_EcommerceOrders`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications

 - API Endpoint
	- GET - Centralbooks\EcommerceOrders\Api\EcommerceOrdersManagementInterface > Centralbooks\EcommerceOrders\Model\EcommerceOrdersManagement


## Attributes



