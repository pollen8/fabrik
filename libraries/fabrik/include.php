<?php
/**
 * Fabrik Autoloader Class
 *
 * @package     Fabrik
 * @copyright   Copyright (C) 2014 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabble\Helpers\Factory;
use Joomla\String\Normalise;
use Joomla\String\Inflector;

/**'
 * Autoloader Class
 *
 * @package  Fabble
 * @since    1.0
 */
class FabrikAutoloader
{
	public function __construct()
	{
		// @TODO - at some point allow auto-loading of these as per Fabble
		/*spl_autoload_register(array($this, 'controller'));
		spl_autoload_register(array($this, 'model'));
		spl_autoload_register(array($this, 'view'));
		spl_autoload_register(array($this, 'library'));
		*/
		spl_autoload_register(array($this, 'formPlugin'));
		spl_autoload_register(array($this, 'element'));
		spl_autoload_register(array($this, 'helper'));
	}

	/**
	 * Load plugin class
	 *
	 * @param   string $class Class name
	 */
	private function formPlugin($class)
	{
		if (!strstr(($class), 'Fabrik\Plugins\Form'))
		{
			return;
		}

		$class = str_replace('\\', '/', $class);
		$file  = explode('/', $class);
		$file  = strtolower(array_pop($file));
		$path  = JPATH_SITE . '/plugins/fabrik_form/' . $file . '/' . $file . '.php';

		if (file_exists($path))
		{
			require_once $path;
		}
	}

	/**
	 * Load element plugin class
	 *
	 * @param   string $class Class name
	 */
	private function element($class)
	{
		if (!strstr(($class), 'Fabrik\Plugins\Element'))
		{
			return;
		}

		$class = str_replace('\\', '/', $class);
		$file  = explode('/', $class);
		$file  = strtolower(array_pop($file));
		$path  = JPATH_SITE . '/plugins/fabrik_element/' . $file . '/' . $file . '.php';

		if (file_exists($path))
		{
			require_once $path;
		}
	}

	private function helper($class)
	{
		if (!strstr(($class), 'Fabrik\Helpers'))
		{
			return;
		}

		$class = str_replace('\\', '/', $class);
		$file  = explode('/', $class);
		$file  = strtolower(array_pop($file));
		$path  = JPATH_SITE . '/components/com_fabrik/helpers/' . $file . '.php';

		require_once $path;
	}

	/**
	 * Load model class
	 *
	 * @param   string $class Class name
	 */
	private function model($class)
	{
		if (!strstr(strtolower($class), 'model'))
		{
			return;
		}

		$kls      = explode('\\', $class);
		$class    = array_pop($kls);
		$scope    = Factory::getApplication()->scope;
		$isFabble = strtolower(substr($class, 0, 11)) === 'fabblemodel';

		if ($this->appName($class) === $scope || $isFabble)
		{
			$path        = JPATH_SITE . '/libraries/fabble/';
			$defaultPath = JPATH_SITE . '/libraries/fabble/';
			$plural      = Inflector::getInstance();
			$parts       = Normalise::fromCamelCase($class, true);
			unset($parts[0]);
			$parts = array_values($parts);

			foreach ($parts as &$part)
			{
				$part = strtolower($part);

				if ($plural->isPlural($part))
				{
					$part = $plural->toSingular($part);
				}

				$part = JString::ucfirst(strtolower($part));
			}

			$path .= implode('/', $parts) . '.php';

			if (file_exists($path))
			{
				require_once $path;
				$type = array_pop($parts);

				if (!$isFabble)
				{
					class_alias('\\Fabble\\Model\\FabbleModel' . JString::ucfirst($type), $class);
				}

				return;
			}

			// IF no actual model name found try loading default model
			$parts[count($parts) - 1] = 'Default';
			$defaultPath .= implode('/', $parts) . '.php';

			if (file_exists($defaultPath))
			{
				require_once $defaultPath;
				$type = array_pop($parts);
				class_alias("\\Fabble\\Model\\FabbleModel" . JString::ucfirst($type), $class);

				return;
			}
		}
	}

	/**
	 * Load view class
	 *
	 * @param   string $class Class name
	 */
	private function view($class)
	{
		if (!strstr(strtolower($class), 'view'))
		{
			return;
		}

		$scope = Factory::getApplication()->scope;

		// Load component specific files
		if ($this->appName($class) === $scope)
		{
			$parts    = Normalise::fromCamelCase($class, true);
			$type     = array_pop($parts);
			$path     = JPATH_SITE . '/libraries/fabble/Views/' . JString::ucfirst($type) . '.php';
			$original = $type;

			if (file_exists($path))
			{
				require_once $path;
				class_alias('\\Fabble\\Views\\' . $original, $class);

				return;
			}
		}
	}

	private function appName($class)
	{
		$scope = Factory::getApplication()->scope;

		return 'com_' . strtolower(substr($class, 0, strlen($scope) - 4));
	}

	/**
	 * Load controller file
	 *
	 * @param   string $class Class name
	 */
	private function controller($class)
	{
		if (!strstr(strtolower($class), 'controller'))
		{
			return;
		}

		$scope = Factory::getApplication()->scope;

		if ($this->appName($class) === $scope)
		{
			$plural = Inflector::getInstance();
			$parts  = Normalise::fromCamelCase($class, true);
			unset($parts[0]);

			foreach ($parts as &$part)
			{
				$part = strtolower($part);

				if ($plural->isPlural($part))
				{
					$part = strtolower($plural->toSingular($part));
				}

				$part = JString::ucfirst($part);
			}

			// Check custom controller
			$name = array_pop($parts);
			$path = JPATH_COMPONENT . '/controller/' . $name . '.php';

			if (file_exists($path))
			{
				require_once $path;

				return;
			}

			foreach ($parts as &$part)
			{
				$part = JString::ucfirst($part);
			}

			// Load Fabble default controllers
			$path = JPATH_SITE . '/libraries/fabble/';
			$path .= implode('/', $parts) . '/' . JString::ucfirst($name) . '.php';

			if (file_exists($path))
			{
				require_once $path;
				class_alias('\\Fabble\\Controller\\' . JString::ucfirst($name), $class);

				return;
			}
		}
	}

	/**
	 * Load library files, and possible helpers
	 *
	 * @param   string $class Class Name
	 */
	private function library($class)
	{

		if (strstr($class, '\\'))
		{
			return;
		}

		if (strtolower(substr($class, 0, 3)) === 'fab')
		{
			$class = (substr($class, 3));

			// Change from camel cased (e.g. ViewHtml) into a lowercase array (e.g. 'view','html') taken from FOF
			$class = preg_replace('/(\s)+/', '_', $class);
			$class = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class));
			$class = explode('_', $class);

			$file      = (count($class) === 1) ? $class[0] : array_pop($class);
			$path      = JPATH_SITE . '/libraries/fabble/' . implode('/', $class);
			$classFile = $path . '/' . $file . '.php';
			$helper    = $path . '/helper.php';

			if (file_exists($classFile))
			{
				include_once $classFile;
			}

			if (file_exists($helper))
			{
				include_once $helper;
			}
		}
	}
}

// PSR-4 Auto-loader.
$loader     = require JPATH_LIBRARIES . '/fabrik/vendor/autoload.php';
$autoLoader = new FabrikAutoloader();
