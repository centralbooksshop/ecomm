<?php
namespace Ecom\Ecomexpress\Model;

class Source
{
  public function toOptionArray()
  {
    return array(
      array('value' => 0, 'label' =>'Enable'),
      array('value' => 1, 'label' => 'Disable'),
    );
  }
}