<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Pollen 8 Design Ltd. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
if (!defined('COM_FABRIK_FRONTEND')) {
	JError::raiseError(400, JText::_('COM_FABRIK_SYSTEM_PLUGIN_NOT_ACTIVE'));
}
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'string.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'parent.php');//leave in as for some reason content plguin isnt loading the fabrikworker class
/**
 * Fabrik Component HTML Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Fabrik
 * @since 1.5
 */
class FabrikHelperHTML
{

	protected static $framework = null;

	protected static $modals = array();

	protected static $tips = array();

	protected static $jsscript = false;

	protected static $ajaxCssFiles = array();

	protected static $debug = null;

	protected static $autocomplete = null;

	protected static $facebookgraphapi = null;

	protected static $helperpaths = array();

	/**
	 * load up mocha window code - should be run in ajax loaded pages as well
	 * might be an issue in that we may be re-observing some links when loading in mocha - need to check
	 * @deprecated use windows() instead
	 * @param string element select to auto create windows for  - was default = a.modal
	 */

	function mocha($selector='', $params = array())
	{
		FabrikHelperHTML::windows($selector, $params);
	}

	function windows($selector='', $params = array())
	{
		$script = '';

		$document = JFactory::getDocument();

		$sig = md5(serialize(array($selector,$params)));
		if (isset(self::$modals[$sig]) && (self::$modals[$sig])) {
			return;
		}

		$script .= "head.ready(function() {";

		if ($selector == '') {
			return;
		}

		// Setup options object
		$opt['ajaxOptions']	= (isset($params['ajaxOptions']) && (is_array($params['ajaxOptions']))) ? $params['ajaxOptions'] : null;
		$opt['size']		= (isset($params['size']) && (is_array($params['size']))) ? $params['size'] : null;
		$opt['onOpen']		= (isset($params['onOpen'])) ? $params['onOpen'] : null;
		$opt['onClose']		= (isset($params['onClose'])) ? $params['onClose'] : null;
		$opt['onUpdate']	= (isset($params['onUpdate'])) ? $params['onUpdate'] : null;
		$opt['onResize']	= (isset($params['onResize'])) ? $params['onResize'] : null;
		$opt['onMove']		= (isset($params['onMove'])) ? $params['onMove'] : null;
		$opt['onShow']		= (isset($params['onShow'])) ? $params['onShow'] : null;
		$opt['onHide']		= (isset($params['onHide'])) ? $params['onHide'] : null;

		$options = json_encode($opt);
		// Attach modal behavior to document
		//set default values which can be overwritten in <a>'s rel attribute

		$opts 							= new stdClass();
		$opts->id 					= 'fabwin';
		$opts->title 				= JText::_('COM_FABRIK_ADVANCED_SEARCH');
		$opts->loadMethod 	= 'xhr';
		$opts->minimizable 	= false;
		$opts->collapsible 	= true;
		$opts->width 				= 500;
		$opts->height 			= 150;
		$opts 							= json_encode($opts);

		$script .= <<<EOD

  $$('$selector').each(function(el, i) {
    el.addEvent('click', function(e) {
    	var opts = $opts;
    	e.stop();
      opts2 = JSON.decode(el.get('rel'));
      opts = Object.merge(opts, opts2 || {});
      opts.contentURL = el.href;
      if (opts.id === 'fabwin') {
      	opts.id += i;
      }
      opts.onContentLoaded = function() {
  			Fabrik.Windows[opts.id].fitToContent()
			};
      Fabrik.getWindow(opts);
    });
  });
});
EOD;

		FabrikHelperHTML::addScriptDeclaration($script);

		self::$modals[$sig] = true;
		return;
	}

	/**
	 * show form to allow users to email form to a friend
	 * @param object form
	 */

	function emailForm($formModel, $template='')
	{
		$document = JFactory::getDocument();
		$form = $formModel->getForm();
		$document->setTitle($form->label);
		$document->addStyleSheet("templates/'. $template .'/css/template_css.css");
		//$url = JRoute::_('index.php?option=com_fabrik&view=emailform&tmpl=component');
		?>
<form method="post" action="index.php" name="frontendForm">
<table>
	<tr>
		<td><label for="email"><?php echo JText::_('COM_FABRIK_YOUR_FRIENDS_EMAIL' ) ?>:</label>
		</td>
		<td><input type="text" size="25" name="email" id="email" /></td>
	</tr>
	<tr>
		<td><label for="yourname"><?php echo JText::_('COM_FABRIK_YOUR_NAME'); ?>:</label>
		</td>
		<td><input type="text" size="25" name="yourname" id="yourname" /></td>
	</tr>
	<tr>
		<td><label for="youremail"><?php echo JText::_('COM_FABRIK_YOUR_EMAIL'); ?>:</label>
		</td>
		<td><input type="text" size="25" name="youremail" id="youremail" /></td>
	</tr>
	<tr>
		<td><label for="subject"><?php echo JText::_('COM_FABRIK_MESSAGE_SUBJECT'); ?>:</label>
		</td>
		<td><input type="text" size="40" maxlength="40" name="subject"
			id="subject" /></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" name="submit" class="button"
			value="<?php echo JText::_('COM_FABRIK_SEND_EMAIL'); ?>" />
		&nbsp;&nbsp; <input type="button" name="cancel"
			value="<?php echo JText::_('COM_FABRIK_CANCEL'); ?>" class="button"
			onclick="window.close();" /></td>
	</tr>
</table>
<input name="referrer"
	value="<?php echo JRequest::getVar('referrer' )?>" type="hidden" /> <input
	type="hidden" name="option" value="com_fabrik" /> <input type="hidden"
	name="view" value="emailform" /> <input type="hidden" name="tmpl"
	value="component" /> <?php echo JHTML::_('form.token'); ?></form>
		<?php
	}

	/**
	 * once email has been sent to a frind show this message
	 */

	function emailSent($to)
	{
		$config = JFactory::getConfig();
		$document = JFactory::getDocument();
		$document->setTitle($config->getValue('sitename'));
		?>
<span class="contentheading"><?php echo JText::_('COM_FABRIK_THIS_ITEM_HAS_BEEN_SENT_TO')." $to";?></span>
<br />
<br />
<br />
<a href='javascript:window.close();'> <span class="small"><?php echo JText::_('COM_FABRIK_CLOSE_WINDOW');?></span>
</a>
		<?php
	}

	/**
	 * writes a print icon
	 * @param object form
	 * @param object parameters
	 * @param int row id
	 * @return string print html icon/link
	 */

	function printIcon($formModel, $params, $rowid = '')
	{
		$app = JFactory::getApplication();
		$config	= JFactory::getConfig();
		$form = $formModel->getForm();
		$table = $formModel->getTable();
		if ($params->get('print')) {
			$status = "status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=400,height=350,directories=no,location=no";
			$url = COM_FABRIK_LIVESITE."index.php?option=com_fabrik&tmpl=component&view=details&formid=". $form->id . "&listid=" . $table->id . "&rowid=" . $rowid.'&iframe=1&print=1';
			// $$$ hugh - @TODO - FIXME - if they were using rowid=-1, we don't need this, as rowid has already been transmogrified
			// to the correct (PK based) rowid.  but how to tell if original rowid was -1???
			if (JRequest::getVar('usekey') !== null) {
				$url .= "&usekey=" . JRequest::getVar('usekey');
			}
			$link = JRoute::_($url);
			$link = str_replace('&', '&amp;', $link); // $$$ rob for some reason JRoute wasnt doing this ???
			if ($params->get('icons', true)) {

				if ($app->isAdmin()) {
					$image = "<img src=\"".COM_FABRIK_LIVESITE."images/M_images/printButton.png\" alt=\"".JText::_('COM_FABRIK_PRINT')."\" />";
				} else {
					$attribs = array();
					$image = JHTML::_('image.site', 'printButton.png', '/images/M_images/', NULL, NULL, JText::_('COM_FABRIK_PRINT'), JText::_('COM_FABRIK_PRINT'));
				}
			} else {
				$image = '&nbsp;'. JText::_('COM_FABRIK_PRINT');
			}
			if ($params->get('popup', 1)) {
				$ahref = '<a href="javascript:void(0)" onclick="javascript:window.print(); return false" title="' . JText::_('COM_FABRIK_PRINT') . '">';
			} else {
				$ahref = "<a href=\"#\" onclick=\"window.open('$link','win2','$status;');return false;\"  title=\"" .  JText::_('COM_FABRIK_PRINT') . "\">";
			}
			$return = $ahref .
			$image .
			"</a>";
			return $return;
		}
	}

	/**
	 * Writes Email icon
	 * @param object form
	 * @param object parameters
	 * @return string email icon/link html
	 */

	function emailIcon($formModel, $params)
	{
		$app = JFactory::getApplication();
		$config	= JFactory::getConfig();
		$popup = $params->get('popup', 1);
		if ($params->get('email') && !$popup) {
			$status = "status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=400,height=250,directories=no,location=no";

			$url = COM_FABRIK_LIVESITE."index.php?option=com_fabrik&view=emailform&tmpl=component&formid=". $formModel->get('id')."&rowid=$formModel->_rowId";
			if (JRequest::getVar('usekey') !== null) {
				$url .= "&usekey=" . JRequest::getVar('usekey');
			}
			$url .= '&referrer='.urlencode(JFactory::getURI()->toString());
			$link = JRoute::_($url);
			if ($params->get('icons', true)) {
				if ($app->isAdmin()) {
					$image = "<img src=\"".COM_FABRIK_LIVESITE."images/M_images/emailButton.png\" alt=\"".JText::_('COM_FABRIK_EMAIL')."\" />";
				} else {
					$image = JHTML::_('image.site', 'emailButton.png', '/images/M_images/', NULL, NULL, JText::_('COM_FABRIK_EMAIL' ), JText::_('COM_FABRIK_EMAIL'));
				}
			} else {
				$image = '&nbsp;'. JText::_('COM_FABRIK_EMAIL');
			}
			return "<a href=\"#\" onclick=\"window.open('$link','win2','$status;');return false;\"  title=\"" .  JText::_('COM_FABRIK_EMAIL') . "\">
			$image
			</a>\n";
		}
	}

	/**
	 * get a list of condition options - used in advanced search
	 * @param int table id
	 * @param string selected value
	 * @return string html select list
	 */

	function conditonList($listid, $sel = '')
	{
		$conditions = array();
		$conditions[] = JHTML::_('select.option', 'AND', JText::_('COM_FABRIK_AND'));
		$conditions[] = JHTML::_('select.option', 'OR', JText::_('COM_FABRIK_OR'));
		return JHTML::_('select.genericlist', $conditions, 'fabrik___filter[list_'.$listid.'][join][]', "class=\"inputbox\" size=\"1\" ", 'value', 'text', $sel);
	}


	function tableList($sel = '')
	{
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('id, label')->from('#__{package}_lists')->where('published = 1');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ($db->getErrorNum()) {
			JError::raiseError(500, $db->getErrorMsg());
		}
		return JHTML::_('select.genericlist', $rows, 'fabrik__swaptable', 'class="inputbox" size="1" ', 'id', 'label', $sel);
	}

	/**
	 * load the css and js files once only (using calendar-eightsix)
	 * @param string $theme
	 */

function loadCalendar()
	{
		JHtml::_('behavior.calendar');
		/*return;
		static $calendarLoaded;

		// Only load once
		if ($calendarLoaded) {
			return;
		}

		$calendarLoaded = true;

		$document = JFactory::getDocument();
		// $$$ hugh - if 'raw' and we output the JS stuff, it screws things up by echo'ing stuff ahead
		// of the raw view display() method's JSON echo
		if ($document->getType() == 'raw') {
			return;
		}

		$config = &JFactory::getConfig();
		$debug = $config->getValue('config.debug');

		FabrikHelperHTML::stylesheet('calendar-jos.css', 'media/system/css/', array(' title' => JText::_('green') ,' media' => 'all'));
		// $$$ hugh - need to just use JHTML::script() for these, to avoid recursion issues if anything else
		// includes these files, and Fabrik is using merged JS, which means page ends up with two copies,
		// causing a "too much recursion" error (calendar.js overrides some date object functions)
		FabrikHelperHTML::script('calendar.js', 'media/system/js/');
		FabrikHelperHTML::script('calendar-setup.js', 'media/system/js/');
		//JHTML::script('calendar.js', 'media/system/js/');
		//JHTML::script('calendar-setup.js', 'media/system/js/');
		$translation = FabrikHelperHTML::_calendartranslation();
		if ($translation) {
			FabrikHelperHTML::addScriptDeclaration($translation);
		}
*/
	}

	/**
	 * Internal method to translate the JavaScript Calendar
	 *
	 * @return	string	JavaScript that translates the object
	 * @since	1.5
	 */
	function _calendartranslation()
	{

		/*
		 * 		Calendar._TT["ABOUT"] =
		 "DHTML Date/Time Selector\n" +
		 "(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" +
		 "For latest version visit: http://www.dynarch.com/projects/calendar/\n" +
		 "Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
		 "\n\n" +
		 "Date selection:\n" +
		 "- Use the \xab, \xbb buttons to select year\n" +
		 "- Use the " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " buttons to select month\n" +
		 "- Hold mouse button on any of the above buttons for faster selection.";
		 Calendar._TT["ABOUT_TIME"] = "\n\n" +
		 "Time selection:\n" +
		 "- Click on any of the time parts to increase it\n" +
		 "- or Shift-click to decrease it\n" +
		 "- or click and drag for faster selection.";
		 */
		if(self::$jsscript == 0)
		{
			$return = 'Calendar._DN = new Array ("'.JText::_('Sunday').'", "'.JText::_('Monday').'", "'.JText::_('Tuesday').'", "'.JText::_('Wednesday').'", "'.JText::_('Thursday').'", "'.JText::_('Friday').'", "'.JText::_('Saturday').'", "'.JText::_('Sunday').'");Calendar._SDN = new Array ("'.JText::_('Sun').'", "'.JText::_('Mon').'", "'.JText::_('Tue').'", "'.JText::_('Wed').'", "'.JText::_('Thu').'", "'.JText::_('Fri').'", "'.JText::_('Sat').'", "'.JText::_('Sun').'"); Calendar._FD = 0;	Calendar._MN = new Array ("'.JText::_('January').'", "'.JText::_('February').'", "'.JText::_('March').'", "'.JText::_('April').'", "'.JText::_('May').'", "'.JText::_('June').'", "'.JText::_('July').'", "'.JText::_('August').'", "'.JText::_('September').'", "'.JText::_('October').'", "'.JText::_('November').'", "'.JText::_('December').'");	Calendar._SMN = new Array ("'.JText::_('January_short').'", "'.JText::_('February_short').'", "'.JText::_('March_short').'", "'.JText::_('April_short').'", "'.JText::_('May_short').'", "'.JText::_('June_short').'", "'.JText::_('July_short').'", "'.JText::_('August_short').'", "'.JText::_('September_short').'", "'.JText::_('October_short').'", "'.JText::_('November_short').'", "'.JText::_('December_short').'");Calendar._TT = {};Calendar._TT["INFO"] = "'.JText::_('About the calendar').'";
		Calendar._TT["PREV_YEAR"] = "'.JText::_('Prev. year (hold for menu)').'";Calendar._TT["PREV_MONTH"] = "'.JText::_('Prev. month (hold for menu)').'";	Calendar._TT["GO_TODAY"] = "'.JText::_('Go Today').'";Calendar._TT["NEXT_MONTH"] = "'.JText::_('Next month (hold for menu)').'";Calendar._TT["NEXT_YEAR"] = "'.JText::_('Next year (hold for menu)').'";Calendar._TT["SEL_DATE"] = "'.JText::_('Select date').'";Calendar._TT["DRAG_TO_MOVE"] = "'.JText::_('Drag to move').'";Calendar._TT["PART_TODAY"] = "'.JText::_('(Today)').'";Calendar._TT["DAY_FIRST"] = "'.JText::_('Display %s first').'";Calendar._TT["WEEKEND"] = "0,6";Calendar._TT["CLOSE"] = "'.JText::_('Close').'";Calendar._TT["TODAY"] = "'.JText::_('Today').'";Calendar._TT["TIME_PART"] = "'.JText::_('(Shift-)Click or drag to change value').'";Calendar._TT["DEF_DATE_FORMAT"] = "'.JText::_('%Y-%m-%d').'"; Calendar._TT["TT_DATE_FORMAT"] = "'.JText::_('%a, %b %e').'";Calendar._TT["WK"] = "'.JText::_('wk').'";Calendar._TT["TIME"] = "'.JText::_('Time:').'";';
			self::$jsscript = 1;
			return $return;
		} else {
			return false;
		}
	}

	/**
	 * fabrik script to load in a style sheet
	 * takes into account if you are viewing the page in raw format
	 * if so sends js code back to webpage to inject css file into document head
	 * If not raw format then apply standard J stylesheet
	 * @param $filename
	 * @param $path
	 * @param $attribs
	 * @return null
	 */

	function stylesheet($file, $attribs = array())
	{
		if ((JRequest::getVar('format') == 'raw' || JRequest::getVar('tmpl') == 'component') && JRequest::getVar('print') != 1) {
			$attribs = json_encode(JArrayHelper::toObject($attribs));
			// $$$rob TEST!!!! - this may mess up stuff
			//send an inline script back which will inject the css file into the doc head
			// note your ajax call must have 'evalScripts':true set in its properties
			if (!in_array($file, self::$ajaxCssFiles)) {
				// $$$ rob added COM_FABRIK_LIVESITE to make full path name other wise style sheets gave 404 error
				// when loading from site with sef urls.
				echo "<script type=\"text/javascript\">var v = new Asset.css('{$file}', {});</script>\n";
				self::$ajaxCssFiles[] = $file;
			}
		} else {
			// $$$ rob 27/04/2011 changed from JHTML::styleSheet as that doesn't work loading
			// php style sheets with querystrings in them
			$document = JFactory::getDocument();
			$document->addStylesheet($file);
		}
	}

	/**
	 * check for a custom css file and include it if it exists
	 * @param string $path NOT including JPATH_SITE (so relative too root dir
	 * @return failse
	 */

	function stylesheetFromPath($path)
	{
		if (JFile::exists(JPATH_SITE.DS.$path)) {
			$parts = explode(DS, $path);
			$file = array_pop($parts);
			$path = implode('/', $parts) .'/';
			FabrikHelperHTML::stylesheet($path.$file);
		}
	}

	/**
	 * Generates an HTML radio list
	 * @param array An array of objects
	 * @param string The value of the HTML name attribute
	 * @param string Additional HTML attributes for the <select> tag
	 * @param mixed The key that is selected
	 * @param string The name of the object variable for the option value
	 * @param string The name of the object variable for the option text
	 * @param int number of options to show per row @since 2.0.5
	 * @returns string HTML for the select list
	 */

	function radioList(&$arr, $tag_name, $tag_attribs, $selected=null, $key='value', $text='text', $options_per_row = 0)
	{
		return FabrikHelperHTML::aList('radio', $arr, $tag_name, $tag_attribs, $selected, $key, $text, $options_per_row);
	}

	/**
	 * Generates an HTML radio OR checkbox list
	 * @param string type - radio or checkbox
	 * @param array An array of objects
	 * @param string The value of the HTML name attribute
	 * @param string Additional HTML attributes for the <select> tag
	 * @param mixed The key that is selected
	 * @param string The name of the object variable for the option value
	 * @param string The name of the object variable for the option text
	 * @param int number of options to show per row @since 2.0.5
	 * @param bool is the list editable or not @since 2.1.1
	 * @returns string HTML for the select list
	 */

	public function aList($type, &$arr, $tag_name, $tag_attribs, $selected=null, $key='value', $text='text', $options_per_row = 0, $editable=true)
	{
		reset($arr);
		if ($options_per_row > 0) {
			$percentageWidth = floor(floatval(100) / $options_per_row) - 2;
			$div = "<div class=\"fabrik_subelement\" style=\"float:left;width:" . $percentageWidth . "%\">\n";
		} else {
			$div = '<div class="fabrik_subelement">';
		}
		$html = "";
		if ($editable) {
			$selectText = $type == 'checkbox' ? " checked=\"checked\"" : " selected=\"selected\"";
		} else {
			$selectText = '';
		}
		for ($i=0, $n=count($arr); $i < $n; $i++) {

			$k = $arr[$i]->$key;
			$t = $arr[$i]->$text;
			$id = isset($arr[$i]->id) ? @$arr[$i]->id : null;

			$extra = '';
			$extra .= $id ? " id=\"" . $arr[$i]->id . "\"" : '';
			$found = false;
			if (is_array($selected)) {
				foreach ($selected as $obj) {
					if (is_object($obj)) {
						$k2 = $obj->$key;
						if ($k == $k2) {
							$found = true;
							$extra .= $selected;
							break;
						}
					} else {
						if ($k == $obj) { //checkbox from db join
							$extra .= $selectText;
							$found = true;
							break;
						}
					}
				}
			} else {
				$extra .= $k == $selected ? " checked=\"checked\"" : '';
			}
			$html .= $div;

			if ($editable) {
				$html .= '<label>';
				$html .= '<input type="'.$type.'" value="'.$k.'" name="'.$tag_name.'" class="fabrikinput" ' . $extra. '/>';
			}
			if ($editable || $found) {
				$html .= '<span>'.$t.'</span>';
			}
			if ($editable) {
				$html .= '</label>';
			}
			$html .= '</div>';
		}
		$html .= "\n";
		return $html;
	}

	/**
	 *
	 */

	function PdfIcon($model, $params, $rowId = 0, $attribs = array())
	{
		$app = JFactory::getApplication();
		$url = '';
		$text	= '';
		// $$$ rob changed from looks at the view as if rendering the table as a module when rendering a form
		// view was form, but $Model is a table
		$modelClass = get_class($model);
		$task = JRequest::getVar('task');
		if ($task == 'form' || $modelClass == 'FabrikModelForm') {
			$form = $model->getForm();
			$table = $model->getTable();
			$user = JFactory::getUser();
			$url = COM_FABRIK_LIVESITE."index.php?option=com_fabrik&amp;view=details&amp;format=pdf&amp;formid=". $form->id . "&amp;listid=" . $table->id . "&amp;rowid=" . $rowId;
		} else {
			$table = $model->getTable();
			$url = COM_FABRIK_LIVESITE."index.php?option=com_fabrik&amp;view=list&amp;format=pdf&amp;listid=" . $table->id;
		}
		if (JRequest::getVar('usekey') !== null) {
			$url .= "&amp;usekey=" . JRequest::getVar('usekey');
		}
		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		// checks template image directory for image, if non found default are loaded
		if ($app->isAdmin()) {
			$text = "<img src=\"".COM_FABRIK_LIVESITE."images/M_images/pdf_button.png\" alt=\"".JText::_('PDF')."\" />\n";
		} else {
			$text = JHTML::_('image.site', 'pdf_button.png', '/images/M_images/', NULL, NULL, JText::_('PDF'));
		}
		$attribs['title']	= JText::_('PDF');
		$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
		$attribs['rel']     = 'nofollow';
		$url = JRoute::_($url);
		$output = JHTML::_('link', $url, $text, $attribs) . "\n";
		return $output;
	}

	/**
	 * Keep session alive, for example, while editing or creating an article.
	 */

	function keepalive()
	{
		//test since 2.0b3 dont do anything if loading from mocha win
		if (JRequest::getVar('tmpl') == 'component') {
			return;
		}
		JHtml::_('behavior.keepalive');
		return;
		//end test
		JHtml::_('behavior.framework');

		$config 	 = JFactory::getConfig();
		$lifetime 	 = ( $config->getValue('lifetime') * 60000);
		$refreshTime =  ( $lifetime <= 60000 ) ? 30000 : $lifetime - 60000;
		//refresh time is 1 minute less than the liftime assined in the configuration.php file

		$document = JFactory::getDocument();
		$script  = '';
		$script .= 'function keepAlive() {';
		$script .=  '	var myAjax = new Request( "index.php", { method: "get" }).send();';
		$script .=  '}';
		$script .= 	' head.ready(function() {';
		$script .= 	'{ keepAlive.periodical('.$refreshTime.'); }';
		$script .=  ');';
		FabrikHelperHTML::addScriptDeclaration($script);
		return;
	}

	/**
	 */

	public function framework(){
		if (!self::$framework) {
			$config = JFactory::getConfig();
			$debug = $config->get('debug');
			//$uncompressed	= $debug ? '-uncompressed' : '';
			$src = array();
			//loading here as well as normal J behavior call makes document.body not found for gmap element when
			// rendered in the form
			if (JRequest::getInt('ajax') !== 1 && JRequest::getVar('tmpl') !== 'component') {
			//	$src[] = 'media/system/js/mootools-core'.$uncompressed.'.js';
			//	$src[] = 'media/system/js/mootools-more'.$uncompressed.'.js';
			}

			JHtml::_('behavior.framework', true);

			if (!FabrikHelperHTML::inAjaxLoadedPage()) {
				JDEBUG ? JHtml::_('script', 'media/com_fabrik/js/lib/head/head.js'): JHtml::_('script', 'media/com_fabrik/js/lib/head/head.min.js');
			}

			$src[] = 'media/com_fabrik/js/mootools-ext.js';
			$src[] = 'media/com_fabrik/js/lib/art.js';
			$src[] = 'media/com_fabrik/js/icons.js';
			$src[] = 'media/com_fabrik/js/icongen.js';
			$src[] = 'media/com_fabrik/js/fabrik.js';
			$src[] = 'media/com_fabrik/js/lib/tips/floatingtips.js';
			$src[] = 'media/com_fabrik/js/window.js';

			FabrikHelperHTML::styleSheet(COM_FABRIK_LIVESITE.'/media/com_fabrik/css/fabrik.css');
			FabrikHelperHTML::addScriptDeclaration("head.ready(function() { Fabrik.liveSite = '".COM_FABRIK_LIVESITE."';});");
			FabrikHelperHTML::script($src, true, "window.fireEvent('fabrik.framework.loaded');");
			self::$framework = true;
		}
	}

	/**
	 * @deprecated use ::framework instead
	 */

	function mootools()
	{
		FabrikHelperHTML::framework();
	}

	/**
	 * @param string js $script
	 * @return null
	 */

	function addScriptDeclaration($script)
	{
		if (JRequest::getCmd('format') == 'raw') {
			echo "<script type=\"text/javascript\">".$script."</script>";
		} else {
			JFactory::getDocument()->addScriptDeclaration($script);
		}
	}

	public function addStyleDeclaration($style) {
	if (JRequest::getCmd('format') == 'raw') {
			echo "<style type=\"text/css\">".$style."</script>";
		} else {
			JFactory::getDocument()->addStyleDeclaration($style);
		}
	}

	/**
	 * sometimes you want to load a page in an iframe and want to use tmpl=component - in this case
	 * append iframe=1 to the url to ensure that we dont try to add the scripts via FBAsset()
	 */

	function inAjaxLoadedPage()
	{
		if (class_exists('JSite')) {
			$menus	= &JSite::getMenu();
			$menu	= $menus->getActive();
			//popup menu item so not in ajax loaded page even if tmpl=component
			// $$$ hugh - nope, browserNav of '1' is not a popup, just a new tab, see ...
			// http://fabrikar.com/forums/showthread.php?p=111771#post111771
			// if (is_object($menu) && ($menu->browserNav == 2 || $menu->browserNav == 1)) {
			if (is_object($menu) && ($menu->browserNav == 2)) {
				return false;
			}
		}
		return JRequest::getVar('format') == 'raw' || (JRequest::getVar('tmpl') == 'component' && JRequest::getInt('iframe') != 1);
	}

	/**
	 * wrapper for JHTML::Script()
	 * @param mixed, string or array of files to load
	 * @param bool should mootools be loaded
	 * @param string optional js to run if format=raw (as we first load the $file via Asset.Javascript()
	 */

	function script($file, $framework = true, $onLoad = '')
	{
		if (empty($file)) {
			return;
		}

		$config = JFactory::getConfig();
		$debug = $config->get('debug');
		//$uncompressed	= $debug ? '-uncompressed' : '';
		$ext = $debug || JRequest::getInt('fabrikdebug', 0) === 1 ? '.js' : '-min.js';

		$file = (array)$file;
		$src = array();
		foreach ($file as $f) {
			if (!(stristr($f, 'http://') || stristr($f, 'https://'))) {
				if (!JFile::exists(COM_FABRIK_BASE.DS.$f)) {
					continue;
				}
			}
			if (stristr($f, 'http://') || stristr($f, 'https://')) {
				$f = $f;
			} else {
				$compressedFile = str_replace('.js', $ext, $f);

				if (JFile::exists($compressedFile)) {
					$f = $compressedFile;
				}
				$f = COM_FABRIK_LIVESITE.$f;
			}
			if (JRequest::getCmd('format') == 'raw') {
				$opts = trim($onLoad) !== '' ? '\'onLoad\':function(){'.$onLoad.'}' : '';
				echo '<script type="text/javascript">Asset.javascript(\''.$f.'\', {'.$opts.'});</script>';
			} else {
				$src[] = "'".$f."'";
			}
		}
		if ($onLoad !== '' && JRequest::getCmd('format') != 'raw') {
			$onLoad = "head.ready(function() {\n " . $onLoad . "\n});\n";
			FabrikHelperHTML::addScriptDeclaration($onLoad);
		}
		if (!empty($src)) {
			JFactory::getDocument()->addScriptDeclaration('head.js('.implode(', ', array_unique($src)).');'."\n");
		}
	}

	function slimbox()
	{
		$fbConfig = JComponentHelper::getParams('com_fabrik');
		if ($fbConfig->get('include_lightbox_js', 1) == 0) {
			return;
		}
		if ($fbConfig->get('use_mediabox', false)) {
			$folder = 'components/com_fabrik/libs/mediabox/';
			JHTML::stylesheet('mediabox.css', $folder . 'css/');
			FabrikHelperHTML::script($folder.'mediabox.js', true);
		}
		else {
			JHTML::stylesheet('slimbox.css', 'components/com_fabrik/libs/slimbox1.64/css/');
			FabrikHelperHTML::script('components/com_fabrik/libs/slimbox1.64/js/slimbox.js', true);
		}
	}

	/**
	 * @param $selector string class name of tips
	 * @param $params array paramters
	 * @param $selectorPrefix limit the tips selection to those contained within an id
	 * @return unknown_type
	 */

	function tips($selector='.hasTip', $params = array(), $selectorPrefix = 'document')
	{

		$sig = md5(serialize(array($selector,$params)));
		if (isset(self::$tips[$sig]) && (self::$tips[$sig])) {
			return;
		}

		// Setup options object
		$opt['maxTitleChars']	= (isset($params['maxTitleChars']) && ($params['maxTitleChars'])) ? (int)$params['maxTitleChars'] : 50;
		$opt['offsets']			= (isset($params['offsets'])) ? (int)$params['offsets'] : null;
		$opt['showDelay']		= (isset($params['showDelay'])) ? (int)$params['showDelay'] : null;
		$opt['hideDelay']		= (isset($params['hideDelay'])) ? (int)$params['hideDelay'] : null;
		$opt['className']		= (isset($params['className'])) ? $params['className'] : null;
		$opt['fixed']			= (isset($params['fixed']) && ($params['fixed'])) ? '\\true' : '\\false';
		$opt['onShow']			= (isset($params['onShow'])) ? '\\'.$params['onShow'] : null;
		$opt['onHide']			= (isset($params['onHide'])) ? '\\'.$params['onHide'] : null;

		$options = json_encode($opt);

		// Attach tooltips to document
		//force the zindex to 9999 so that it appears above the popup window.
		//$event = (JRequest::getVar('tmpl') == 'component') ? 'load' : 'domready';
		$tooltipInit = 'head.ready(function() {if(typeOf('.$selectorPrefix.') !== \'null\' && '.$selectorPrefix.'.getElements(\''.$selector.'\').length !== 0) {window.JTooltips = new Tips('.$selectorPrefix.'.getElements(\''.$selector.'\'), '.$options.');$$(".tool-tip").setStyle("z-index", 999999);}});';
		FabrikHelperHTML::addScriptDeclaration($tooltipInit);

		self::$tips[$sig] = true;
		return;
	}

	/**
	 * add a debug out put section
	 * @param mixed string/object $content
	 * @param string $title
	 */

	function debug($content, $title = 'output:')
	{
		$config = JComponentHelper::getParams('com_fabrik');
		if ($config->get('use_fabrikdebug') == 0) {
			return;
		}
		if (JRequest::getBool( 'fabrikdebug', 0, 'request') != 1) {
			return;
		}
		if (JRequest::getVar('format') == 'raw') {
			return;
		}
		echo "<div class=\"fabrikDebugOutputTitle\">$title</div>";
		echo "<div class=\"fabrikDebugOutput fabrikDebugHidden\">";
		if (is_object($content) || is_array($content)) {
			echo "<pre>" . htmlspecialchars(print_r($content, true)) . "</pre>";
		} else {
			echo htmlspecialchars($content);
		}
		echo "</div>";

		if (!isset(self::$debug)) {
			self::$debug = true;
			$document = JFactory::getDocument();
			$style = ".fabrikDebugOutputTitle{padding:5px;background:#efefef;color:#333;border:1px solid #999;cursor:pointer}";
			$style .= ".fabrikDebugOutput{padding:5px;background:#efefef;color:#999;}";
			$style .= ".fabrikDebugOutput pre{padding:5px;background:#efefef;color:#999;}";
			$style .= ".fabrikDebugHidden{display:none}";
			FabrikHelperHTML::addStyleDeclaration($style);
			$script = "head.ready(function() {
			$$('.fabrikDebugOutputTitle').each(function(title) {
				title.addEvent('click', function(e) {
					title.getNext().toggleClass('fabrikDebugHidden');
				});
			});
			})";
			FabrikHelperHTML::addScriptDeclaration($script);
		}
	}

	/**
	 * create html for ajax folder browser (used by fileupload and image elements)
	 * @param array folders
	 * @param string start path
	 * @return string html snippet
	 */

	function folderAjaxSelect($folders, $path = 'images')
	{
		$str = array();
		$str[] = "<a href=\"#\" class=\"toggle\" title=\"".JText::_('COM_FABRIK_BROWSE_FOLDERS')."\">";
		$str[] = "<img src=\"".COM_FABRIK_LIVESITE."/media/com_fabrik/images/control_play.png\" alt=\"".JText::_('COM_FABRIK_BROWSE_FOLDERS')."\"/>";
		$str[] = "</a>";
		$str[] = "<div class=\"folderselect-container\">";
		$str[] = "<span class=\"breadcrumbs\"><a href=\"#\">" . JText::_('HOME') . "</a><span> / </span>";
		$i = 1;
		$path = explode("/", $path);
		foreach ($path as $p) {
			$str[] = "<a href=\"#\" class=\"crumb".$i."\">" . $p . "</a><span> / </span>";
			$i ++;
		}
		$str[] = "</span>";
		$str[] = "<ul class=\"folderselect\">";
		settype($folders, 'array');
		foreach ($folders as $folder) {
			if (trim($folder) != '') {
				$str[] = "<li class=\"fileupload_folder\"><a href=\"#\">$folder</a></li>";
			}
		}
		//for html validation
		if (empty($folder)) {
			$str[] =  "<li></li>";
		}
		$str[] = "</ul></div>";
		return implode("\n", $str);
	}

	/**
	 * Add autocomplete JS code to head
	 * @param string $htmlid of element to turn into autocomplete
	 * @param int $elementid
	 * @param string $plugin
	 * @param array $opts (currently only takes 'onSelection')
	 */

	public function autoComplete($htmlid, $elementid, $plugin = 'field', $opts = array())
	{
		FabrikHelperHTML::autoCompleteScript();
		$json = FabrikHelperHTML::autoCompletOptions($htmlid, $elementid, $plugin, $opts);
		$str = json_encode($json);
		FabrikHelperHTML::addScriptDeclaration(
		"head.ready(function() { new FbAutocomplete('$htmlid', $str); });"
		);
	}

	/**
	 * Gets auto complete js options (needed separate from autoComplete as db js class needs these values for repeat group duplication)
	 * @param string $htmlid of element to turn into autocomplete
	 * @param int $elementid
	 * @param string $plugin
	 * @param array $opts (currently only takes 'onSelection')
	 * @return array autocomplete options (needed for elements so when duplicated we can create a new FabAutocomplete object
	 */

	public function autoCompletOptions($htmlid, $elementid, $plugin = 'fabrikfield', $opts = array())
	{
		$json = new stdClass();
		$json->url = COM_FABRIK_LIVESITE.'index.php?option=com_fabrik&format=raw&view=plugin&task=pluginAjax&g=element&element_id='.$elementid.'&plugin='.$plugin.'&method=autocomplete_options';
		$c = JArrayHelper::getValue($opts, 'onSelection');
		if ($c != '') {
			$json->onSelections = $c;
		}
		$json->container = JArrayHelper::getValue($opts, 'container', 'fabrikElementContainer');
		return $json;
	}

	/**
	 *Load the autocomplete script once
	 */

	public function autoCompleteScript() {
		if (!isset(self::$autocomplete)) {
			self::$autocomplete = true;
			FabrikHelperHTML::script('media/com_fabrik/js/autocomplete.js');
		}
	}

	public function facebookGraphAPI($appid, $locale = 'en_US', $meta = array())
	{
		if (!isset(self::$facebookgraphapi)) {
			self::$facebookgraphapi = true;
			return "<div id=\"fb-root\"></div>
<script>
  window.fbAsyncInit = function() {
    FB.init({appId: '$appid', status: true, cookie: true,
             xfbml: true});
  };
  (function() {
    var e = document.createElement('script'); e.async = true;
    e.src = document.location.protocol +
      '//connect.facebook.net/$locale/all.js';
    document.getElementById('fb-root').appendChild(e);
  }());
</script>";
		}
		$document = JFactory::getDocument();
		$data = array('custom'=>array());
		$typeFound = false;
		foreach ($meta as $k => $v) {
			$v = strip_tags($v);
			//og:type required
			if ($k == 'og:type') {
				$typeFound = true;
				if ($v == '') {
					$v = 'article';
				}
			}
			$data['custom'][] = "<meta property=\"$k\" content=\"$v\"/>";

		}
		if (!$typeFound) {
			$data['custom'][] = "<meta property=\"og:type\" content=\"article\"/>";
		}
		$document->setHeadData($data);
	}

	/**
	 * add path for image() function
	 * @since 3.0
	 * @param string $path to add to list of folders to search
	 * @param string $type of path set to load (currently only image is used)
	 * @param string $view are we looking at loading form or list images?
	 * @param bool $highPriority should the added $path take precedence over previously added paths (default true)
	 */

	public function addPath($path = '', $type = 'image', $view = 'form', $highPriority = true)
	{
		if (!array_key_exists($type, self::$helperpaths)) {
			self::$helperpaths[$type] = array();
			$app = JFactory::getApplication();
			$template = $app->getTemplate();
			if ($app->isAdmin()) {
				self::$helperpaths[$type][] = JPATH_SITE."/administrator/templates/$template/images/";
			}
			self::$helperpaths[$type][] = COM_FABRIK_BASE."templates/$template/html/com_fabrik/$view/%s/images/";
			self::$helperpaths[$type][] = COM_FABRIK_BASE."templates/$template/html/com_fabrik/$view/images/";
			self::$helperpaths[$type][] = COM_FABRIK_BASE."templates/$template/html/com_fabrik/images/";
			self::$helperpaths[$type][] = COM_FABRIK_FRONTEND."views/$view/tmpl/%s/images/";
			self::$helperpaths[$type][] = COM_FABRIK_BASE."media/com_fabrik/images/";
		}
		if (!array_key_exists($path, self::$helperpaths[$type]) && $path !== '') {
			$highPriority ? array_unshift(self::$helperpaths[$type], $path) : self::$helperpaths[$type][] =  $path;
		}
		return self::$helperpaths[$type];
	}

	/**
	 * Search various folder locations for a template image
	 * @since 3.0
	 * @param string file name
	 * @param string type e.g. form/list/element
	 * @param string tempalte folder name
	 * @param string image alt text
	 * @param array assoc list of properties
	 */

	public function image($file, $type = 'form', $tmpl = '', $alt = '', $srcOnly = false, $properties = array())
	{

		$app = JFactory::getApplication();
		$template = $app->getTemplate();
		$paths = FabrikHelperHTML::addPath('', 'image', $type);
		$alt = $alt == '' ? $file : $alt;
		$src = '';
		foreach ($paths as $path) {
			$path = sprintf($path, $tmpl);
			if (JFile::exists($path.$file)) {
				$src = str_replace(COM_FABRIK_BASE, COM_FABRIK_LIVESITE, $path.$file);
				$src = str_replace("\\", "/", $src);
				break;
			}
		}
		if ($srcOnly) {
			return $src;
		}
		$bits = array();
		$bits['alt'] = $alt;
		$bits['title'] = $alt;
		foreach ($properties as $key => $val) {
			$bits[$key] = $val;
		}
		$p = '';
		foreach ($bits as $key => $val) {
			$p .= "$key=\"$val\" ";
		}
		return $src == '' ? '' : "<img src=\"$src\" $p/>";
	}

}
?>
