<?php
/**
 * @version    $Id$
 * @package    SUN Framework
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

/**
 * Editor widget
 *
 * @package     SUN Framework
 * @subpackage  Template
 * @since       1.0.0
 */
#[\AllowDynamicProperties]
class SunFwAjaxEditor extends SunFwAjax
{

	public function indexAction()
	{
		// Get Joomla document object.
		$doc = Factory::getDocument();

		// Load the site template's Bootstrap CSS.
		{
			$doc->addStyleSheet(Uri::base(true) . '/templates/' . (PlgSystemSunFw::$template ? PlgSystemSunFw::$template->template : $this->app->getTemplate()) . '/css/bootstrap.css');
		}

		// Load admin template's stylesheet.
		$doc->addStylesheet(
			Uri::base(true) . '/templates/' . (PlgSystemSunFw::$template ? PlgSystemSunFw::$template->template : $this->app->getTemplate()) . '/css/template' . ( $doc->direction == 'rtl' ? '-rtl' : '' ) .
				 '.css');

		// Load required scripts.
		HTMLHelper::_('behavior.core');
		HTMLHelper::_('bootstrap.framework');

		$this->render('index');
	}
}
