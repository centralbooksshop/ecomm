<?php

/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\UbContentSlider\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Ubertheme\UbContentSlider\Model\Item\Image as ImageModel;

class Image extends AbstractHelper {

    /**
     * @var string $_imageBase
     */
    protected $_imageBase;

    /**
     * @var int $_quality
     */
    protected $_quality = 90;

    /**
     * @var string $_cachePath
     */
    protected $_cachePath;

    /**
     * @var string $_cacheURL
     */
    protected $_cacheURL;

    /**
     * @var string $_noImage
     */
    protected $_noImage = '';

    /**
     * @var string $_thumbMode
     */
    protected $_thumbMode = 'crop';

    /**
     * @var bool $_isCrop
     */
    protected $_isCrop = false;

    /**
     * @var bool $_isResize
     */
    protected $_isResize = false;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Filesystem $_fileSystem
     */
    protected $_fileSystem;

    /**
     * @var ImageModel
     */
    protected $_imageModel;

    public function __construct(
        ImageModel $imageModel,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $fileSystem
    ) {
        $this->_fileSystem = $fileSystem;
        $this->_storeManager = $storeManager;
        $this->_imageModel = $imageModel;

        $cacheFolder = "frontend/ub-images-resized";
        $this->_cachePath = $this->_fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::STATIC_VIEW)->getAbsolutePath() . $cacheFolder;
        $this->_cacheURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC) . $cacheFolder;

        parent::__construct($context);
    }

    /**
     * crop or resize image
     *
     *
     * @param string $image path of source.
     * @param integer $width width of thumnail
     * @param integer $height height of thumnail
     * @param boolean $crop whether to use crop image to render thumnail.
     * @param boolean $aspect whether to render thumnail base on the ratio
     * @access public
     */
    public function resize($image, $width, $height = null, $crop = true, $aspect = true) {
        $imagePath = $this->_imageModel->getBaseDir().$image;

        if (!file_exists($imagePath) OR !is_file($imagePath)) {
            return '';
        }
        
        if (!$width)
            return '';
        
        $size = getimagesize($imagePath);
        
        // if it's not a image.
        if (!$size) {
            return '';
        }
        
        if (!$height) $height = $size[1];
        
        // case 1: render image base on the ratio of source.
        $x_ratio = $width / $size[0];
        $y_ratio = $height / $size[1];

        // set dst, src
        $dst = new \stdClass();
        $src = new \stdClass();
        $src->y = $src->x = 0;
        $dst->y = $dst->x = 0;

        if ($width > $size[0])
            $width = $size[0];
        if ($height > $height)
            $height = $size[1];


        if ($crop) { // processing crop image
            $dst->w = $width;
            $dst->h = $height;
            if (($size[0] <= $width) && ($size[1] <= $height)) {
                $src->w = $width;
                $src->h = $height;
            } else {
                if ($x_ratio < $y_ratio) {
                    $src->w = ceil($width / $y_ratio);
                    $src->h = $size[1];
                } else {
                    $src->w = $size[0];
                    $src->h = ceil($height / $x_ratio);
                }
            }
            $src->x = floor(($size[0] - $src->w) / 2);
            $src->y = floor(($size[1] - $src->h) / 2);
        } else { // processing resize image.
            $src->w = $size[0];
            $src->h = $size[1];
            if ($aspect) { // using ratio
                if (($size[0] <= $width) && ($size[1] <= $height)) {
                    $dst->w = $size[0];
                    $dst->h = $size[1];
                } else if (($size[0] <= $width) && ($size[1] <= $height)) {
                    $dst->w = $size[0];
                    $dst->h = $size[1];
                } else if (($x_ratio * $size[1]) < $height) {
                    $dst->h = ceil($x_ratio * $size[1]);
                    $dst->w = $width;
                } else {
                    $dst->w = ceil($y_ratio * $size[0]);
                    $dst->h = $height;
                }
            } else { // resize image without the ratio of source.
                $dst->w = $width;
                $dst->h = $height;
            }
        }
        
        $ext = substr(strrchr($image, '.'), 1);
        $thumbnail = substr($image, 0, strpos($image, '.')) . "_{$width}_{$height}." . $ext;
        $imageCache = $this->_cachePath . $thumbnail;
        
        if (file_exists($imageCache)) {
            $smallImg = getimagesize($imageCache);
            if (($smallImg [0] == $dst->w && $smallImg [1] == $dst->h)) {
                return $this->_cacheURL . $thumbnail;
            }
        }

        if (!file_exists($this->_cachePath) && !mkdir($this->_cachePath, 0775, true)) {
            return '';
        }

        if (!$this->makeDir($image)) {
            return '';
        }

        // resize image
        $this->_resizeImage($imagePath, $src, $dst, $size, $imageCache);

        return $this->_cacheURL . $thumbnail;
    }
    
    public function resizeThumb($image, $width, $height) {
        if ($this->_thumbMode == 'none' || empty($this->_thumbMode)) {
            return $image;
        }
        return $this->resize($image, $width, $height, $this->_isCrop, $this->_isResize);
    }

    public static function getFileExtension($imagePath) {
        $image_mime = image_type_to_mime_type(exif_imagetype($imagePath));
        switch ($image_mime) {
            case "image/gif":
                $ext = 'gif';
                break;
            case "image/jpeg":
                $ext = 'jpg';
                break;
            case "image/png":
                $ext = 'png';
                break;
            case "image/bmp":
                $ext = 'bmp';
                break;
        }
        return $ext;
    }

    /**
     *  process render image
     *
     * @param string $imagePath is path of the image source.
     * @param stdClass $src the setting of image source
     * @param stdClass $dst the setting of image dts
     * @param string $imageCache path of image cache ( it's thumbnail).
     * @access public,
     */
    protected function _resizeImage($imagePath, $src, $dst, $size, $imageCache) {

        $imageMime = image_type_to_mime_type(exif_imagetype($imagePath));
        $imageType = str_replace("image/", "",$imageMime);

        // create image from source.
        $image = call_user_func("imagecreatefrom{$imageType}", $imagePath);

        if (function_exists("imagecreatetruecolor") && ($newImage = imagecreatetruecolor($dst->w, $dst->h))) {
            if ($imageType == 'gif' || $imageType == 'png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $dst->w, $dst->h, $transparent);
            }
            imagecopyresampled($newImage, $image, $dst->x, $dst->y, $src->x, $src->y, $dst->w, $dst->h, $src->w, $src->h);
        } else {
            $newImage = imagecreate($dst->w, $dst->h);
            imagecopyresized($newImage, $image, $dst->x, $dst->y, $src->x, $src->y, $dst->w, $dst->h, $size[0], $size[1]);
        }

        switch ($imageType) {
            case 'jpeg' :
                call_user_func('image' . $imageType, $newImage, $imageCache, $this->_quality);
                break;
            default:
                call_user_func('image' . $imageType, $newImage, $imageCache);
                break;
        }
        // free memory
        imagedestroy($image);
        imagedestroy($newImage);
    }

    /**
     * set quality image will render.
     */
    public function setQuality($number = 75) {
        $this->_quality = $number;
        return $this;
    }

    public function setConfig($thumbnailMode, $ratio = true) {
        $this->_thumbMode = $thumbnailMode;

        if ($thumbnailMode != 'none') {
            $this->_isCrop = $thumbnailMode == 'crop' ? true : false;
            $this->_isResize = $ratio;
        }
        return $this;
    }
    
    /**
     *  check the folder is existed, if not make a directory and set permission is 755 or 775
     *
     * @param array $path
     * @access public,
     * @return boolean.
     */
    public function makeDir($path) {
        $folders = explode('/', ( $path));
        $tmpPath = $this->_cachePath;
        for ($i = 0; $i < count($folders) - 1; $i ++) {
            if (!file_exists($tmpPath . $folders [$i]) && !mkdir($tmpPath . $folders [$i], 0775)) {
                return false;
            }
            $tmpPath = $tmpPath . $folders [$i] . '/';
        }
        return true;
    }

    /**
     * check the image source is existed ?
     *
     * @param string $text the path of image source.
     * @access public,
     * @return mixed,
     */
    public function parseImage($text) {
        $regex = "/\<img.+src\s*=\s*\"([^\"]*)\"[^\>]*\>/";
        preg_match($regex, $text, $matches);
        $images = (count($matches)) ? $matches : array();
        $image = count($images) > 1 ? $images [1] : '';
        return $image;
    }

    /**
     * @param $id
     * @param string $thumbSize (thumbnail_large, thumbnail_medium, thumbnail_small)
     * @return mixed
     */
    public static function getVimeoThumb($id, $thumbSize = 'thumbnail_medium') {
        //if (self::checkRemoteFile("https://vimeo.com/api/oembed.json?url=http://vimeo.com/{$id}")) {
        if (self::checkRemoteFile("http://vimeo.com/api/v2/video/{$id}.json")) {
            $data = file_get_contents("http://vimeo.com/api/v2/video/{$id}.json");
            $data = json_decode($data);
            return str_replace('http://', '//', $data[0]->$thumbSize);
        } else {
            return '';
        }
    }

    /**
     * @param $id
     * @param string $thumbSize (0, 1, 2, 3, default, hqdefault, mqdefault, sddefault, )
     * @return mixed
     */
    public static function getYoutubeThumb($id, $thumbSize = 'default') {
        if (self::checkRemoteFile("http://img.youtube.com/vi/{$id}/{$thumbSize}.jpg")) {
            $rs = "//img.youtube.com/vi/{$id}/{$thumbSize}.jpg";
        } else {
            $rs = "";
        }
        return $rs;
    }

    /**
     * @param $url
     * @return bool
     */
    public static function checkRemoteFile($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        // don't download content
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(curl_exec($ch)!== FALSE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $videoType
     * @param $videoId
     * @return bool
     */
    public static function isValidVideoId($videoType, $videoId){
        $isValid = false;
        if ($videoType AND $videoId) {
            switch ($videoType) {
                case 'youtube_video':
                    if (strlen($videoId) == 11 AND !is_numeric($videoId)) {
                        $isValid = self::checkRemoteFile("http://img.youtube.com/vi/{$videoId}/default.jpg");
                    }
                    break;
                case 'vimeo_video':
                    if (is_numeric($videoId)) {
                        $isValid = self::checkRemoteFile("https://vimeo.com/api/oembed.json?url=http://vimeo.com/{$videoId}");
                    }
                    break;
            }
        }
        return $isValid;
    }
}

