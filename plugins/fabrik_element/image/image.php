<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.image
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Plugin element to render an image already located on the server
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.image
 * @since       3.0
 */

class plgFabrik_ElementImage extends plgFabrik_Element
{

	var $ignoreFolders = array('cache', 'lib', 'install', 'modules', 'themes', 'upgrade', 'locks', 'smarty', 'tmp');

	protected $fieldDesc = 'TEXT';

	/**
	 * This really does get just the default value (as defined in the element's settings)
	 *
	 * @param   array  $data  form data
	 *
	 * @return mixed
	 */

	function getDefaultValue($data = array())
	{
		if (!isset($this->default))
		{
			$params = $this->getParams();
			$element = $this->getElement();
			$w = new FabrikWorker;
			$this->default = $params->get('imagepath');

			// $$$ hugh - this gets us the default image, with the root folder prepended.
			// But ... if the root folder option is set, we need to strip it.
			$rootFolder = $params->get('selectImage_root_folder', '/');
			$rootFolder = JString::ltrim($rootFolder, '/');
			$this->default = preg_replace("#^$rootFolder#", '', $this->default);
			$this->default = $w->parseMessageForPlaceHolder($this->default, $data);
			if ($element->eval == "1")
			{
				$this->default = @eval(stripslashes($this->default));
				FabrikWorker::logEval($this->default, 'Caught exception on eval in ' . $element->name . '::getDefaultValue() : %s');
			}
		}
		return $this->default;
	}

	/**
	 * Determines the value for the element in the form view
	 *
	 * @param   array  $data           form data
	 * @param   int    $repeatCounter  when repeating joinded groups we need to know what part of the array to access
	 * @param   array  $opts           options
	 *
	 * @return  string	value
	 */

	function getValue($data, $repeatCounter = 0, $opts = array())
	{
		if (!isset($this->defaults))
		{
			$this->defaults = array();
		}
		$this->defaults = (array) $this->defaults;
		if (!array_key_exists($repeatCounter, $this->defaults))
		{
			$groupModel = $this->_group;
			$group = $groupModel->getGroup();
			$joinid = $group->join_id;
			$formModel = $this->getForm();
			$element = $this->getElement();
			$params = $this->getParams();

			// $$$rob - if no search form data submitted for the search element then the default
			// selection was being applied instead
			if (array_key_exists('use_default', $opts) && $opts['use_default'] == false)
			{
				$default = '';
			}
			else
			{
				$default = $this->getDefaultValue($data);
			}

			$name = $this->getFullName(false, true, false);

			if ($groupModel->isJoin())
			{
				if ($groupModel->canRepeat())
				{
					if (array_key_exists('join', $data) && array_key_exists($joinid, $data['join']) && is_array($data['join'][$joinid])
						&& array_key_exists($name, $data['join'][$joinid]) && array_key_exists($repeatCounter, $data['join'][$joinid][$name]))
					{
						$default = $data['join'][$joinid][$name][$repeatCounter];
					}
				}
				else
				{
					if (array_key_exists('join', $data) && array_key_exists($joinid, $data['join']) && is_array($data['join'][$joinid])
						&& array_key_exists($name, $data['join'][$joinid]))
					{
						$default = $data['join'][$joinid][$name];
					}
				}
			}
			else
			{
				if ($groupModel->canRepeat())
				{
					//repeat group NO join
					if (array_key_exists($name, $data))
					{
						if (is_array($data[$name]))
						{
							//occurs on form submission for fields at least
							$a = $data[$name];
						}
						else
						{
							//occurs when getting from the db
							$a = json_decode($data[$name], true);
						}
						$default = JArrayHelper::getValue($a, $repeatCounter, $default);
					}

				}
				else
				{
					$default = JArrayHelper::getValue($data, $name, $default);
				}
			}
			if ($default === '')
			{ //query string for joined data
				$default = JArrayHelper::getValue($data, $name);
			}
			$element->default = $default;
			//stops this getting called from form validation code as it messes up repeated/join group validations
			if (array_key_exists('runplugins', $opts) && $opts['runplugins'] == 1)
			{
				FabrikWorker::getPluginManager()->runPlugins('onGetElementDefault', $formModel, 'form', $this);
			}
			if (is_array($element->default))
			{
				$element->default = implode(',', $element->default);
			}
			$this->defaults[$repeatCounter] = $element->default;

		}
		return $this->defaults[$repeatCounter];
	}

	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string  $data      elements data
	 * @param   object  &$thisRow  all the data in the lists current row
	 *
	 * @return  string	formatted value
	 */

	public function renderListData($data, &$thisRow)
	{
		$data = FabrikWorker::JSONtoData($data, true);
		$params = $this->getParams();
		$pathset = false;
		foreach ($data as $d)
		{
			if (strstr($d, '/'))
			{
				$pathset = true;
				break;
			}
		}
		if ($data === '' || empty($data) || !$pathset)
		{
			//no data so default to image (or simple image name stored).
			$iPath = $params->get('imagepath');
			if (!strstr($iPath, '/'))
			{
				//single file specified so find it in tmpl folder
				$data = (array) FabrikHelperHTML::image($iPath, 'list', @$this->tmpl, array(), true);
			}
			else
			{
				$data = (array) $iPath;
			}
		}
		$selectImage_root_folder = $params->get('selectImage_root_folder', '');
		// $$$ hugh - tidy up a bit so we don't have so many ///'s in the URL's
		$selectImage_root_folder = JString::ltrim($selectImage_root_folder, '/');
		$selectImage_root_folder = JString::rtrim($selectImage_root_folder, '/');
		$showImage = $params->get('show_image_in_table', 0);
		$linkURL = $params->get('link_url', '');
		if (empty($data) || $data[0] == '')
		{
			//$data[] = $params->get('imagefile');
			$data[] = $params->get('imagepath');
		}
		for ($i = 0; $i < count($data); $i++)
		{
			if ($showImage)
			{
				// $$$ rob 30/06/2011 - say if we import via csv a url to the image check that and use that rather than the relative path
				$src = JString::substr($data[$i], 0, 4) == 'http' ? $data[$i] : COM_FABRIK_LIVESITE . $selectImage_root_folder . '/' . $data[$i];
				$data[$i] = '<img src="' . $src . '" alt="' . $data[$i] . '" />';
			}
			if ($linkURL)
			{
				$data[$i] = '<a href="' . $linkURL . '" target="_blank">' . $data[$i] . '</a>';
			}
		}
		$data = json_encode($data);
		return parent::renderListData($data, $thisRow);
	}

	/**
	 * Manupulates posted form data for insertion into database
	 *
	 * @param   mixed  $val   this elements posted form data
	 * @param   array  $data  posted form data
	 *
	 * @return  mixed
	 */

	public function storeDatabaseFormat($val, $data)
	{
		$groupModel = $this->getGroup();
		$params = $this->getParams();
		$selectImage_root_folder = $params->get('selectImage_root_folder', '');

		$key = $this->getFullName(false, true, false);
		if (!array_key_exists($key, $data))
		{
			$element = $this->getElement();
			$key = $element->name;
		}

		if ($groupModel->canRepeat() && !$groupModel->isJoin())
		{
			if ($groupModel->isJoin())
			{
				// @TODO - not tested with join group data
			}
			if (!array_key_exists($key . '_folder', $data))
			{
				//$retval = implode(GROUPSPLITTER , $data[$key]);
				$retval = json_encode($data[$key]);
			}
			else
			{
				$retvals = array();
				foreach ($data[$key] as $k => $v)
				{
					$retvals[] = preg_replace("#^$selectImage_root_folder#", '', $data[$key . '_folder'][$k]) . $data[$key . '_image'][$k];
				}
				//$retval = implode(GROUPSPLITTER , $retvals);
				$retval = json_encode($retvals);
			}
		}
		else
		{

			// $$$ hugh - if we're using default image, no user selection,
			// the _folder and _image won't exist,
			// we'll just have the relative path in the element $key
			if (!array_key_exists($key . '_image', $data))
			{
				$retval = $data[$key];
			}
			else
			{
				//$retval = preg_replace("#^$selectImage_root_folder#", '', $data[$key]) . $data[$key . '_image'];
				$retval = preg_replace("#^$selectImage_root_folder#", '', $data[$key]);
			}
		}
		return $retval;
	}

	/**
	 * shows the data formatted for RSS export
	 * @param   stringing data
	 * @param   object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData_rss($data, $thisRow)
	{
		$params = $this->getParams();
		$selectImage_root_folder = $params->get('selectImage_root_folder', '');
		return '<img src="' . COM_FABRIK_LIVESITE . $selectImage_root_folder . '/' . $data . '" />';
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */

	function render($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$name = $this->getHTMLName($repeatCounter);
		$value = $this->getValue($data, $repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$rootFolder = $params->get('selectImage_root_folder');
		// $$$ hugh - tidy up a bit so we don't have so many ///'s in the URL's
		$rootFolder = JString::ltrim($rootFolder, '/');
		$rootFolder = JString::rtrim($rootFolder, '/');
		// $$$ rob - 30/062011 allow for full urls in the image. (e.g from csv import)
		$defaultImage = JString::substr($value, 0, 4) == 'http' ? $value : COM_FABRIK_LIVESITE . $rootFolder . '/' . $value;
		// $$$ rob - 30/06/2011 can only select an image if its not a remote image
		$canSelect = ($params->get('image_front_end_select', '0') && JString::substr($value, 0, 4) !== 'http');
		$float = $params->get('image_float');
		$float = $float != '' ? "style='float:$float;'" : '';
		$str = array();
		$str[] = '<div class="fabrikSubElementContainer" id="' . $id . '">';

		$rootFolder = str_replace('/', DS, $rootFolder);
		if ($canSelect && $this->_editable)
		{
			$str[] = '<img src="' . $defaultImage . '" alt="' . $value . '" ' . $float . ' class="imagedisplayor"/>';
			if (array_key_exists($name, $data))
			{
				if (trim($value) == '')
				{
					$path = "/";
				}
				else
				{
					$bits = explode("/", $value);
					if (count($bits) > 1)
					{
						$path = '/' . array_shift($bits) . '/';
						$path = $rootFolder . $path;
						$val = array_shift($bits);
					}
					else
					{
						$path = $rootFolder;
					}
				}
			}
			else
			{
				$path = $rootFolder;
			}
			$images = array();
			$imagenames = (array) JFolder::files(JPATH_SITE . '/' . $path);
			foreach ($imagenames as $n)
			{
				$images[] = JHTML::_('select.option', $n, $n);
			}
			// $$$rob not sure about his name since we are adding $repeatCounter to getHTMLName();
			$imageName = $this->_group->canRepeat() ? FabrikString::rtrimWord($name, "][$repeatCounter]") . "_image][$repeatCounter]" : $id
				. '_image';
			$image = array_pop(explode('/', $value));
			// $$$ hugh - append $rootFolder to JPATH_SITE, otherwise we're showing folders
			// they aren't supposed to be able to see.
			$folders = JFolder::folders(JPATH_SITE . DS . $rootFolder);
			// @TODO - if $folders is empty, hide the button/widget?  All they can do is select
			// from the initial image dropdown list, so no point having the widget for changing folder?
			$str[] = '<br/>' . JHTML::_('select.genericlist', $images, $imageName, 'class="inputbox imageselector" ', 'value', 'text', $image);
			$str[] = FabrikHelperHTML::folderAjaxSelect($folders);
			$str[] = '<input type="hidden" name="' . $name . '" value="' . $value . '" class="fabrikinput hiddenimagepath folderpath" />';
		}
		else
		{
			$linkURL = $params->get('link_url', '');
			$imgstr = '<img src="' . $defaultImage . '" alt="' . $value . '" ' . $float . ' class="imagedisplayor"/>' . "\n";
			if ($linkURL)
			{
				$imgstr = '<a href="' . $linkURL . '" target="_blank">' . $imgstr . '</a>';
			}
			$str[] = $imgstr;
			$str[] = '<input type="hidden" name="' . $name . '" value="' . $value . '" class="fabrikinput hiddenimagepath folderpath" />';
		}
		$str[] = '</div>';
		return implode("\n", $str);
	}

	function onAjax_files()
	{
		$folder = JRequest::getVar('folder');
		$pathA = JPath::clean(JPATH_SITE . '/' . $folder);
		$folder = array();
		$files = array();
		$images = array();
		FabrikWorker::readImages($pathA, "/", $folders, $images, $this->ignoreFolders);
		if (!array_key_exists('/', $images))
		{
			$images['/'] = array();
		}
		echo json_encode($images['/']);
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return  string
	 */

	public function elementJavascript($repeatCounter)
	{
		$app = JFactory::getApplication();
		$params = $this->getParams();
		$element = $this->getElement();
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->rootPath = $params->get('selectImage_root_folder', '');
		$opts->canSelect = (bool) $params->get('image_front_end_select', false);
		$opts->id = $element->id;
		$opts->ds = DS;
		$opts->dir = JPATH_SITE . DS . str_replace('/', DS, $opts->rootPath);
		$opts = json_encode($opts);
		return "new FbImage('$id', $opts)";
	}

	/**
	 *get admin lists
	 *
	 *@deprecated
	 */

	function getAdminLists(&$lists)
	{

		/**
		 * IMPORTANT NOTE FOR HACKERS!
		 * 	if your images folder contains massive sub directories which you dont want fabrik
		 * accessing (and hance slowing down to a crawl the loading of this page)
		 * then put the folders in the $ignoreFolders array
		 */
		$params = $this->getParams();
		$images = array();
		$folders = array();
		$path = $params->get('imagepath', '/');
		$file = $params->get('imagefile');
		$folders[] = JHTML::_('select.option', '/', '/');
		FabrikWorker::readImages(JPATH_SITE, "/", $folders, $images, $this->ignoreFolders);
		$lists['folders'] = JHTML::_('select.genericlist', $folders, 'params[imagepath]', 'class="inputbox" size="1" ', 'value', 'text', $path);
		$javascript = "onchange=\"previewImage()\" onfocus=\"previewImage()\"";
		$is = JArrayHelper::getValue($images, $path, array());
		$lists['imagefiles'] = JHTML::_('select.genericlist', $is, 'params[imagefile]',
			'class="inputbox" size="10" multiple="multiple" ' . $javascript, 'value', 'text', $file);
		$defRootFolder = $params->get('selectImage_root_folder', '');
		$lists['selectImage_root_folder'] = JHTML::_('select.genericlist', $folders, 'params[selectImage_root_folder]',
			"class=\"inputbox\"  size=\"1\" ", 'value', 'text', $defRootFolder);
	}

	/**
	 * @deprecated
	 */

	function renderAdminSettings(&$lists)
	{
		return;
		$params = $this->getParams();
		$pluginParams = $this->getPluginParams();
		$this->getAdminLists($lists);
?>
<script language="javascript" type="text/javascript">
			/* <![CDATA[ */
			function setImageName() {
				var image = document.adminForm.imagefiles;
				var linkurl = document.getElementsByName('params[image_path]')[0];
				linkurl.value =  (image).get('value');
			}

			function previewImage() {
				var root = '<?php echo COM_FABRIK_LIVESITE; ?>';
				var file = $('paramsimagefile').get('value');
				var folder = $('paramsimagepath').get('value');
				$('view_imagefiles').src = root + file;
			}

			head.ready(function() {
			$('paramsimagepath').addEvent('change', function(e) {
				var event = new Event(e);
				event.stop;
				var folder = '<?php echo $params->get('selectImage_root_folder', ''); ?>' + $(event.target).get(\'value\');
				var url = '<?php echo COM_FABRIK_LIVESITE; ?>index.php?option=com_fabrik&format=raw&view=plugin&task=pluginAjax&g=element&plugin=image&method=ajax_files';
				var myAjax = new Request({url:url, method:'post',
			'data':{'folder':folder},
			onComplete: function(r) {
				var opts = eval(r);
				var folder = '<?php echo $params->get('selectImage_root_folder', ''); ?>' + $(event.target).get(\'value\');
				$('paramsimagefile').empty()
				opts.each( function(opt) {
					$('paramsimagefile').adopt(
						new Element('option', {'value':folder + opt.value}).appendText(opt.text)
					);
				}.bind(this));
				previewImage();
			}.bind(this)
		}).request();

			});
			 previewImage();
			});
			/* ]]> */
		</script>
<div id="page-<?php echo $this->_name; ?>" class="elementSettings"
	style="display: none">
<table class="admintable">
	<tr>
		<td class="paramlist_key"><?php echo JText::_('Default image'); ?></td>
		<td><?php echo $lists['folders'];
		echo "<br />\n" . $lists['imagefiles']; ?>
		<img name="view_imagefiles" id="view_imagefiles" src="<?php echo COM_FABRIK_LIVESITE . $params->get('image_path'); ?>" width="100" alt="view imagefiles"/> <br />
		</td>
	</tr>
	<tr>
		<td class="paramlist_key"><?php echo JText::_('Root folder'); ?>:</td>
		<td><?php echo $lists['selectImage_root_folder']; ?></td>
	</tr>
</table>
		<?php echo $pluginParams->render(); ?></div>
		<?php
	}

	/**
	 * Used to format the data when shown in the form's email
	 *
	 * @param   mixed  $value          element's data
	 * @param   array  $data           form records data
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	formatted value
	 */

	function getEmailValue($value, $data, $c)
	{
		return $this->render($data);
	}
}
												   ?>