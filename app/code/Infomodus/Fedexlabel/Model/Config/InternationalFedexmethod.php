<?php
namespace Infomodus\Fedexlabel\Model\Config;

class InternationalFedexmethod implements \Magento\Framework\Option\ArrayInterface
{
    protected $fedexmethod;

    public function __construct(\Infomodus\Fedexlabel\Model\Config\Fedexmethod $fedexmethod)
    {
        $this->fedexmethod = $fedexmethod;
    }

    public function toOptionArray()
    {
        $c = $this->fedexmethod->toOptionArray(true);
        return $c;
    }
}
