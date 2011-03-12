<?php
/**
 *  Salgua Cloud Zoom !Joomla Plugin
 *  Copyright 2011 - Salvatore Guarino - info@salgua.com
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  any later version.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>. 
 * 
 */
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

class ResizeImg {
   
   var $img;
   var $img_type;
 
   function load($filename) {
      $img_info = getimagesize($filename);
      $this->img_type = $img_info[2];
      if( $this->img_type == IMAGETYPE_JPEG ) {
         $this->img = imagecreatefromjpeg($filename);
      } elseif( $this->img_type == IMAGETYPE_GIF ) {
         $this->img = imagecreatefromgif($filename);
      } elseif( $this->img_type == IMAGETYPE_PNG ) {
         $this->img = imagecreatefrompng($filename);
      }
   }
   function save($filename, $img_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
      if( $img_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->img,$filename,$compression);
      } elseif( $img_type == IMAGETYPE_GIF ) {
         imagegif($this->img,$filename);         
      } elseif( $img_type == IMAGETYPE_PNG ) {
         imagepng($this->img,$filename);
      }   
      if( $permissions != null) {
         chmod($filename,$permissions);
      }
   }
   function output($img_type=IMAGETYPE_JPEG) {
      if( $img_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->img);
      } elseif( $img_type == IMAGETYPE_GIF ) {
         imagegif($this->img);         
      } elseif( $img_type == IMAGETYPE_PNG ) {
         imagepng($this->img);
      }   
   }
   function getWidth() {
      return imagesx($this->img);
   }
   function getHeight() {
      return imagesy($this->img);
   }
   function resizeToHeight($height) {
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100; 
      $this->resize($width,$height);
   }
   function resize($width,$height) {
      $new_img = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_img, $this->img, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->img = $new_img;   
   }      
}
?>