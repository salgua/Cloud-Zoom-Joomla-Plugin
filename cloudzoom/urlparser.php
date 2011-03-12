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

class UrlParser {
	
	private $slash;
	private $WIMPY_BASE;
	
	function __construct() {
		// this sets the sytem / or \ :
		strstr( PHP_OS, "WIN") ? $this->slash = "\\" : $this->slash = "/";
		
		// This is the location of the php file that contains this
		// function. Usually this request is made to files/folders
		// down the directory structure, so the php file that
		// contains these functions is a good "where am i"
		// reference point:
		$this->WIMPY_BASE['path']['physical'] = getcwd();
		$this->WIMPY_BASE['path']['www'] = "http://".$_SERVER['HTTP_HOST'];
	}
	

	public function url2filepath($theURL){
	    $AtheFile = explode ("/", $theURL);
	    $theFileName = array_pop($AtheFile);
	    $AwimpyPathWWW = explode ("/", $this->WIMPY_BASE['path']['www']);
	    $AtheFilePath = array_values (array_diff ($AtheFile, $AwimpyPathWWW));
	    if($AtheFilePath){
	        $theFilePath = $this->slash.implode($this->slash, $AtheFilePath).$this->slash.$theFileName;
	    } else {
	        $theFilePath = implode($this->slash, $AtheFilePath).$this->slash.$theFileName;
	    }
	    return ($this->WIMPY_BASE['path']['physical'].$theFilePath);
	}
	
	public function filepath2url ($theFilepath){
	    $AtheFile = explode ($this->slash, $theFilepath);
	    $theFileName = array_pop($AtheFile);
	    $AwimpyPathFILE = explode ($this->slash, $this->WIMPY_BASE['path']['physical']);
	    $AtheFilePath = array_values (array_diff ($AtheFile, $AwimpyPathFILE));
	    $thFileURL = implode("/", $AtheFilePath)."/".$theFileName;
	    return ($this->WIMPY_BASE['path']['www']."$thFileURL");
	}

}
?>