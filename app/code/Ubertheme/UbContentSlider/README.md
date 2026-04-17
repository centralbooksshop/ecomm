# README #

# UbContentSlider Extension for Magento2 #

### Features: ###
   + Support multiple sliders per page, multiple websites, stores configuration with owl-carousel javascript plugin.
   + Allow play slider with:
      - [x] Latest Products (system auto)
      - [x] New Products from...to... date (by admin settings)
      - [x] Hot Products (by admin settings)
      - [x] Random Products (system auto)
      - [x] Images/Videos uploaded.
   + Allow setting and showing a slider via widget module

### Compatible: ###
   + Magento CE 2.0.x
   
### How to Install ###
Go to web root folder and run below commands:

- `composer config repositories.ubcontentslider vcs REPOSITOTY_URL_OF_THIS_MODULE`
- `composer require ubertheme/module-ubcontentslider` (*This is a private repository. So, you have to type your bitbucket credentials in this step.*)
- `php -f bin/magento module:enable --clear-static-content Ubertheme_UbContentSlider`
- `php -f bin/magento setup:upgrade`

### How to use ###
1 - Setting and show a slider via widget module:

2 - Call in CMS Block: 
```
{{block class="Ubertheme\UbContentSlider\Block\Slider" name="ub.content.slider1" title="Random Products" content_type="random_products"}}
```

3 - Call a slider images uploaded in CMS Block: 
```
{{block class="Ubertheme\UbContentSlider\Block\Slider" name="ub.content.slider2" title="Random Products" content_type="slider" slider_key="YOUR_SLIDER_KEY_HERE"}}
```

4 - Call in XML: 
```
<block class="Ubertheme\UbContentSlider\Block\Slider" name="ub.content.slider3" as="ub-content-slider3"/>
```
- Example call a block slider via custom design:
```
<referenceContainer name="content">
   <block class="Ubertheme\UbContentSlider\Block\Slider" name="ub.content.slider3" as="ub-content-slider3" >
      <arguments>
            <argument name="content_type" xsi:type="string">latest_products</argument>
            <argument name="number_items"  xsi:type="number">4</argument>
      </arguments>
   </block>
</referenceContainer>
```

#### Table option param ####

Param Name    | Desc/Values
------------- | -------------
enable        | 0,1
show_title    | 0, 1 Default is 1. Show/hide Block title
title         | The Block Title
content_type  | latest_products, new_products, hot_products, random_products, slider (uploaded by admin)
category_ids  | the category ids to filter. Example: `category_ids = "9, 10, 15"`
qty           | The number limit of quantity items to show. Default is 10.
slider_key    | The key of Slider to show. Only apply for content_type = 'slider'
item_width    | Width of item (px). Only apply for content_type = 'slider'
item_height   | Height of item (px). Only apply for content_type = 'slider'
show_item_title| 0, 1 Default is 1. Only apply for content_type = 'slider' 
show_item_desc| 0, 1 Default is 1. Only apply for content_type = 'slider'
thumb_width   | Width of thumbnail (px). Only apply for content_type = 'slider' and single_item = 1 and show_thumbnail = 1
thumb_height  | Height of thumbnail (px). Only apply for content_type = 'slider' and single_item = 1 and show_thumbnail = 1
show_name     | 0,1 Default is 1
show_price    | 0,1 Default is 1
show_desc     | 0,1 Default is 1
desc_length   | The number limit length chars of desc to show. Default is 100
show_review   | 0,1 Default is 1
show_wishlist | 0,1 Default is 1
show_compare  | 0,1 Default is 1
show_add_cart | 0,1 Default is 1
show_readmore | 0,1 Default is 1
single_item   | 0,1 Default is 0
auto_run      | 0,1 Default is 1
auto_height   | 0,1 Default is 1
slide_speed   | The speed of slider. Default is 200 mini second.
stop_on_hover | 0,1 Default is 1
show_navigation | 0,1 Default is 1
show_paging     | 0,1 Default is 1
paging_numbers  | 0,1 Default is 1
show_thumbnail  | 0,1 Only apply when `single_item = 1`
enable_lazyload | 0,1 Default is 1
show_processbar | 0,1 Default is 0
slide_transition | fade,backSlide,goDown,fadeUp. Default is 'fade'
number_items | Visible Items (width > 1200), default is 5.  Only apply when `single_item = 0` Example: `number_items = 5`.
number_items_desktop | Visible Items on Tablet Horizontal/Landscape (980 <= width <= 1199), default is 5.  Only apply when `single_item = 0`
number_items_desktop_small  | Visible Items on Tablet Vertical/Portrait (768 <= width <= 979) default is 4.  Only apply when `single_item = 0`
#number_items_tablet  | Visible Items on Tablet (480 <= width <= 768) default is 3.  Only apply when `single_item = 0` <-- Need to confirm about this param
number_items_tablet_small  | Visible Items on Mobile Horizontal/Landscape (480 <= width <= 767) default is 2.  Only apply when `single_item = 0`
number_items_mobile  | Visible Items on Mobile Vertical/Portrait (width <= 479) default is 1.  Only apply when `single_item = 0`
addition_class | CSS class addition. - Styles Supported: ub-style-1, ub-style-2