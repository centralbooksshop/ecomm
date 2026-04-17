<?php
/**
 * Grid GridInterface.
 * @category  Webkul
 * @package   Retailinsights_Rmareasonlayer
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Retailinsights\Rmareasonlayer\Api\Data;

interface GridInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'id';
    const TITLE = 'title';
    const POSITION = 'position';
    const STATUS = 'status';
    
   /**
    * Get EntityId.
    *
    * @return int
    */
    public function getId();

   /**
    * Set EntityId.
    */
    public function setId($id);

   /**
    * Get Title.
    *
    * @return varchar
    */
    public function getTitle();

   /**
    * Set Title.
    */
    public function setTitle($title);

   /**
    * Get Content.
    *
    * @return varchar
    */
    public function getPosition();

   /**
    * Set Content.
    */
    public function setPosition($position);

   /**
    * Get Publish Date.
    *
    * @return varchar
    */
    public function getStatus();

   /**
    * Set PublishDate.
    */
    public function setStatus($status);
}
