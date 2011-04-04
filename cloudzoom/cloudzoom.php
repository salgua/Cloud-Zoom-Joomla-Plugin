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

jimport( 'joomla.plugin.plugin' );

class plgContentCloudzoom extends JPlugin {
	//parameter
	private $incjquery;
	private $noconflict;
	private $rel; //rel tag
	private $bigimage; //big image
	private $smallimage; //small image
	//private $pluginParams;
	private $idplg;
	function plgContentCloudzoom(&$subject, $params)
	{
		parent::__construct($subject, $params);
		$this->idplg=0;
	}
	
	/**
	 * Example prepare content method
	 *
	 * Method is called by the view
	 *
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The content object.  Note $article->text is also available
	 * @param	object	The content params
	 * @param	int		The 'page' number
	 * @since	1.6
	 */ 
	function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		global $mainframe;
		$document =& JFactory::getDocument();
		$plugin =& JPluginHelper::getPlugin('content', 'cloudzoom');
    	//read and set the plugin parameters (only Joomla 1.5
		//$pluginParams = new JParameter( $plugin->params );
		//$this->pluginParams = $pluginParams;
		//include jquery library if incjquery is set to true
		$this->incjquery = strtolower($this->params->get('incjquery')) == "yes";
		if ( $this->incjquery ) { 
			$document->addScript(JURI::root(true).'/plugins/content/cloudzoom/js/jquery-1.3.2.min.js');   
		}
		//enable jquery no-conflict mode if enabled
		$this->noconflict = strtolower($this->params->get('jquerynoconflict')) == "1";
		if ($this->incjquery && $this->noconflict) {
			$document->addScript(JURI::root(true).'/plugins/content/cloudzoom/js/no-conflict.js');
		}
		//include cloudzoom js library
		$document->addScript(JURI::root(true).'/plugins/content/cloudzoom/js/cloud-zoom.1.0.2.js'); 
		//include stylesheet
		$document->addStyleSheet(JURI::root(true).'/plugins/content/cloudzoom/css/cloud-zoom.css');
		
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
			if (!isset($params['width']))
			{
				if (intval($this->params->get('width'))>1)
				{
					$params['width']=$this->params->get('width');
				} else{
					$params['width']="150";
				}
				
			}
						$this->idplg++;
						$text = "<div class='zoom-small-image'><a href='".$params['img']."' class='cloud-zoom' 
						id='zoom".$this->idplg."' rel=\"".$params['rel']."\">
						<img src='".$this->getSmallImage($params['img'],$params['width'])."' alt='' title='".$params['title']."' />
						</a></div>";
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
		//includes
		require_once(JPATH_PLUGINS.DS.'content'.DS.'cloudzoom'.DS.'resizeimg.php');
		require_once(JPATH_PLUGINS.DS.'content'.DS.'cloudzoom'.DS.'urlparser.php');
		
		$up = new UrlParser();
		$imgpath = $up->url2filepath($imgurl); //retrieve the file path of the image
		
		$pos=strrpos($imgurl, "."); //find the last "." before the file extension
		$filename=substr($imgurl, 0, $pos); //exctract the url without extension
		$ext=substr($imgurl, $pos); //extract the file extension
		$imgsmallurl = $filename."_small".$ext; //add the suffix _small to the file name
		
		$imgsmallpath = $up->url2filepath($imgsmallurl); //convert the image small url to file path
		
		if (file_exists($imgsmallpath))
		{
			$imgsize = getimagesize($imgsmallpath);
			if ($imgsize[0]==$width) //check if the existing small image has the same width
			{
				return $imgsmallurl; //return the file_small url if the file already exists and it has with the same widht
			}
		}
		


		$image = new ResizeImg();
		$image->load($imgpath); //loath the image
		$image->resizeToWidth($width); //resize the image
		$image->save($imgsmallpath); //save the small image
		return $imgsmallurl; //return the small image url
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
				$value=str_replace(array("\""), "", substr(trim($tag), $pos + 1));
				$params[$parameter]=$value;
			}
		}
		return $params;
	}
}
