<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the HelloWorld Component
 *
 * @since  0.0.1
 */

 // <<component>>View<<viewname>>   viewname via view parameter, default component name
class gezc_logboekViewgezc_logboek extends JViewLegacy
{
	/**
	 * Display the Hello World view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */

	 // if task is not specifiek in the urel als parameter, display will be used
	function display($tpl = null)
	{
		// Assign data to the view
		$this->msg = 'GeZC logboek';

		// Display the view
		parent::display($tpl);
	}
}