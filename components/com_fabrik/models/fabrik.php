<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

$version = new JVersion();
if ($version->RELEASE == '1.6') {
	class FabModel extends JModel
	{
		/**
		 * Method to load and return a model object.
		 *
* @param   string	The name of the view
* @param   string  The class prefix. Optional.
		 * @return  mixed	Model object or boolean false if failed
		 */
		
		private function _createTable($name, $prefix = 'Table', $config = array())
		{
			// Clean the model name
			$name	= preg_replace('/[^A-Z0-9_]/i', '', $name);
			$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

			//Make sure we are returning a DBO object
			if (!array_key_exists('dbo', $config))
			{
				$config['dbo'] = $this->getDbo();
			}
			return FabTable::getInstance($name, $prefix, $config);;
		}

		/**
		 * Method to get a table object, load it if necessary.
		 *
* @param   string The table name. Optional.
* @param   string The class prefix. Optional.
* @param   array	Configuration array for model. Optional.
		 * @return  object	The table
		 */
		
		public function getTable($name='', $prefix='Table', $options = array())
		{
			if (empty($name))
			{
				$name = $this->getName();
			}

			if ($table = $this->_createTable($name, $prefix, $options))
			{
				return $table;
			}
			JError::raiseError(JText::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name));
			return null;
		}
	}
} else {
	// Joomla 1.7 onwards
	class FabModel extends JModel
	{
		/**
		 * Method to load and return a model object.
		 *
* @param   string	The name of the view
* @param   string  The class prefix. Optional.
		 * @return  mixed	Model object or boolean false if failed
		 */
		
		protected function _createTable($name, $prefix = 'Table', $config = array())
		{
			// Clean the model name
			$name = preg_replace('/[^A-Z0-9_]/i', '', $name);
			$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

			//Make sure we are returning a DBO object
			if (!array_key_exists('dbo', $config))
			{
				$config['dbo'] = $this->getDbo();
			}
			return FabTable::getInstance($name, $prefix, $config);;
		}

		/**
		 * Method to get a table object, load it if necessary.
		 *
* @param   string The table name. Optional.
* @param   string The class prefix. Optional.
* @param   array	Configuration array for model. Optional.
		 * @return  object	The table
		 */
		
		public function getTable($name='', $prefix='Table', $options = array())
		{
			if (empty($name))
			{
				$name = $this->getName();
			}

			if ($table = $this->_createTable($name, $prefix, $options))  {
				return $table;
			}
			JError::raiseError(JText::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name));
			return null;
		}
	}
}
