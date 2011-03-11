<?php
/**
 *  Cloud Zoom Joomla Plugin
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

jimport( 'joomla.plugin.plugin' );

class plgContentCloudzoom extends JPlugin {
	//parameter
	private $incjquery;
	private $noconflict;
	private $rel; //rel tag
	private $bigimage; //big image
	private $smallimage; //small image
	function plgContentCloudzoom(&$subject, $params)
	{
		parent::__construct($subject, $params);
	}
	
/**
	 * Example prepare content method
	 *
	 * Method is called by the view
	 *
	 * @param 	object		The article object.  Note $article->text is also available
	 * @param 	object		The article params
	 * @param 	int			The 'page' number
	 */
	function onPrepareContent( &$article, &$params, $limitstart=0 )
	{
		global $mainframe;
		$document =& JFactory::getDocument();
		$plugin =& JPluginHelper::getPlugin('content', 'cloudzoom');
    	//read and set the plugin parameters
		$pluginParams = new JParameter( $plugin->params );
		//include jquery library if incjquery is set to true
		$this->incjquery = (strtolower($pluginParams->get('incjquery', 1)) == 'yes');
		if ( $this->incjquery ) { 
			$document->addScript('/plugins/content/cloudzoom/js/jquery-1.3.2.min.js');   
		}
		//enable jquery no-conflict mode if enabled
		$this->noconflict = (strtolower($pluginParams->get('jquerynoconflict', 1)) == '1');
		if ($this->incjquery && $this->noconflict) {
			$document->addScript('/plugins/content/cloudzoom/js/no-conflict.js');
		}
		//include cloudzoom js library
		$document->addScript('/plugins/content/cloudzoom/js/cloud-zoom.1.0.2.js'); 
		//include stylesheet
		$document->addStyleSheet($this->get_plugin_web_path('css/cloud-zoom.css'));
		
		//process the zoomable content
		$regex = "/{cloudzoom.+?}/";
		$contents = $article->text;
		// find all instances of plugin and put in $matches
		preg_match_all( $regex, $article->text, $matches );
		// Number of plugins
 		$count = count( $matches[0] );
 		// plugin only processes if there are any instances of the plugin in the text
 		if ( $count ) {
			// perform the replacement
			$article->text = preg_replace_callback($regex, array($this, 'plgContentCloudzoom_replacer'), $article->text );	
 		}
		return true;
	}
	
	function plgContentCloudzoom_replacer(&$matches)
	{
			$params = $this->getParams($matches[0]);
			if (isset($params['img']))
			{
				$pos=strrpos($params['img'], "."); //find the last "." before the file extension
				$filename=substr($params['img'], 0, $pos);
				$ext=substr($params['img'], $pos);
				$params['imgsmall'] = $filename."_small".$ext;
			}
			$path=JPATH_PLUGINS;
			$text = "<a href='".$params['img']."' class='cloud-zoom' 
						id='zoom1' rel='".$params['rel']."'>
						<img src='".$params['imgsmall']."' alt='' title='".$params['title']."' />
						</a>";
			return $text;
	}
	
	/**
	 * 
	 * Check if the small image exists and if it
	 * doesn't exist, it create a new one. Return
	 * the url of the small image 
	 * @param string $imgurl
	 * @param int $width
	 * @return string
	 */
	function getSmallImage($imgurl, $width) {
		$pos=strrpos($imgurl, "."); //find the last "." before the file extension
		$filename=substr($imgurl, 0, $pos); //exctract the url without extension
		$ext=substr($imgurl, $pos); //extract the file extension
		$imgsmallurl = $filename."_small".$ext; //add the suffix _small to the file name
		if (file_exists($imgsmallurl))
		{
			$imgsize = getimagesize($imgsmallurl);
			if ($imgsize[0]==$width) //check if the existing small image has the same width
			{
				return $imgsmallurl; //return the file_small url if the file already exists and it has with the same widht
			}
		}
		
	}
	
	/**
	 * 
	 * Resize the image
	 * @param unknown_type $originalImage
	 * @param unknown_type $toWidth
	 * @param unknown_type $toHeight
	 */
	
	function resizeImage($originalImage,$toWidth,$toHeight)
	{

	    list($width, $height) = getimagesize($originalImage);
	    $xscale=$width/$toWidth;
	    $yscale=$height/$toHeight;
	
	    if ($yscale>$xscale){
	        $new_width = round($width * (1/$yscale));
	        $new_height = round($height * (1/$yscale));
	    }
	    else {
	        $new_width = round($width * (1/$xscale));
	        $new_height = round($height * (1/$xscale));
	    }
	   
	   
	    $imageResized = imagecreatetruecolor($new_width, $new_height);
	    $imageTmp     = imagecreatefromjpeg ($originalImage);
	    imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	
	    return $imageResized;
	} 
	
	
	function getParams($text) {
		$text = str_replace(array("{","}"), "", $text);
		$tags = explode("|", $text);
		$params = array();
		//return $text;
		foreach ($tags as $tag)
		{
			if (trim($tag)!="cloudzoom") {
				$pos=strpos(trim($tag), "=");
				$parameter=substr(trim($tag), 0, $pos);
				$value=str_replace(array("'"), "", substr(trim($tag), $pos + 1));
				$params[$parameter]=$value;
			}
		}
		return $params;
	}
	
	  /***************************************************************************/
	  /* General utility													     */
	  /* These are practical for reuse in other plugins.                         */
	  /***************************************************************************/
	
	  function get_web_path($relpath) {
	    return JURI::root(true) . ($relpath[0] != '/' ? '/' : '') . $relpath;
	  }
	
	  function get_plugin_web_path($relpath) {
	    return $this->get_web_path('/plugins/content/cloudzoom' . ($relpath[0] != '/' ? '/' : '') . $relpath);
	  }
}
