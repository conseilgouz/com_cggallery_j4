<?php
/**
 * @component     CG Isotope
 * Version			: 1.1.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2022 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz 
**/
defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('sql');

class JFormFieldSQLnoerr extends JFormFieldSQL
{
	public $type = 'SQLnoerr';
	protected $keyField;
	protected $valueField;
	protected $translate = false;
	protected $query;

	/**
	 * Method to check if SQL query contains errors
	 * @return  array  The field option objects or empty (if error in query)
	 */
	protected function getOptions()
	{
		$options = array();

		// Initialize some field attributes.
		$key   = $this->keyField;
		$value = $this->valueField;
		$header = $this->header;

		if ($this->query)
		{
			// Get the database object.
			$db = JFactory::getDbo();

			// Set the query and get the result list.
			$db->setQuery($this->query);

			try
			{
				$items = $db->loadObjectlist();
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				 return $options; // SQL Error : return empty
			}
		}
		// No error : execute SQL
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
