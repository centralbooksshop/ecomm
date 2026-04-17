<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Items;

class Printer extends \Infomodus\Fedexlabel\Controller\Adminhtml\Items
{
    public function execute()
    {
        $imname = $this->getRequest()->getParam('imname');
        $path_url = $this->_handy->_conf->getBaseUrl('media') . '/fedexlabel/label/';
        $content = '<html>
            <head>
            <title>Print Shipping Label</title>
            </head>
            <body>
            <img src="' . $path_url . $imname . '" />
            <script>
            window.onload = function(){window.print();}
            </script>
            </body>
            </html>';
        $this->getResponse()
            ->setContent($content);
        return;
    }
}
