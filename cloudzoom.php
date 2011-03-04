<?php
/**
 * @version     cloudzoom.php 0.1
 * @copyright   Copyright 2011 - Salvatore Guarino - info@salgua.com
 * @license     Licensed under the MIT License
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, 
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES 
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. 
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR 
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR 
 * THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgContentCloudzoom extends JPlugin {
	//parameter
	private $incjquery;
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
				$pos=strpos($params['img'], ".");
				$filename=substr($params['img'], 0, $pos);
				$ext=substr($params['img'], $pos);
				$params['imgsmall'] = $filename."_small".$ext;
			}
			$text = "<a href='".$params['img']."' class='cloud-zoom' 
						id='zoom1' rel='".$params['rel']."'>
						<img src='".$params['imgsmall']."' alt='' title='".$params['title']."' />
						</a>";
			return $text;
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
