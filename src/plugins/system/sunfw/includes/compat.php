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

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;


/**
 * General helper class.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
#[\AllowDynamicProperties]
class SunFwCompat
{

	/**
	 * Method to check Joomla version.
	 *
	 * @param   string  $version  Version string to check against.
	 *
	 * @return  boolean
	 */
	public static function isJoomla($version)
	{
		// Make sure the given version is a string.
		if (!is_string($version))
		{
			$version = (string) $version;
		}

		return ( $version === substr(preg_replace('/[^\d]+/', '', SunFwHelper::getJoomlaVersion()), 0, strlen($version)) );
	}

	/**
	 * Method to check if the requested page belongs to a certain client.
	 *
	 * @param   string  $client  Either 'admin' or 'site'.
	 *
	 * @return  boolean
	 */
	public static function isClient($client)
	{
		// Get Joomla application object.
		$app = Factory::getApplication();

		switch ($client)
		{
			case 'site':
				return $app->isClient('site');
			break;

			case 'admin':
			case 'administrator':
				return $app->isClient('administrator');
			break;
		}

		return false;
	}

	/**
	 * Load jQuery. Joomla 4 and later ship it at media/vendor/jquery.
	 *
	 * @return  void
	 */
	public static function loadJquery()
	{
		if (self::isClient('admin'))
		{
			Factory::getDocument()->addScript(Uri::root(true) . '/media/vendor/jquery/js/jquery.min.js');
		}
		else
		{
			HTMLHelper::_('jquery.framework');
		}
	}
}
