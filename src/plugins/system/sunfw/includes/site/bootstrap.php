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

/**
 * Class for migrating template from Bootstrap v3 to v4.
 *
 * @package  SUN Framework
 * @since    2.2.25
 */
class SunFwSiteBootstrap
{
	/**
	 * Define Bootstrap class mapping for v3 and v4.
	 *
	 * @var   array
	 */
	protected static $mapping = array(
		'hidden' => array(
			'3' => 'hidden',
			'4' => 'd-none'
		),
		'img-responsive' => array(
			'3' => 'img-responsive',
			'4' => 'img-fluid'
		),
		'margin-bottom-0' => array(
			'3' => 'margin-bottom-0',
			'4' => 'mb-0'
		),
		'list-inline' => array(
			'3' => 'list-inline',
			'4' => 'list-inline'
		),
		'list-inline-item' => array(
			'3' => '',
			'4' => 'list-inline-item'
		),
		'caret' => array(
			'3' => 'caret',
			'4' => 'dropdown-toggle'
		),
		'dropdown-toggle' => array(
			'3' => 'dropdown-toggle',
			'4' => ''
		),
		'carousel-item' => array(
			'3' => 'item',
			'4' => 'carousel-item'
		),
		'left carousel-control' => array(
			'3' => 'left carousel-control',
			'4' => 'carousel-control-prev'
		),
		'glyphicon glyphicon-chevron-left' => array(
			'3' => 'glyphicon glyphicon-chevron-left',
			'4' => 'carousel-control-prev-icon'
		),
		'right carousel-control' => array(
			'3' => 'right carousel-control',
			'4' => 'carousel-control-next'
		),
		'glyphicon glyphicon-chevron-right' => array(
			'3' => 'glyphicon glyphicon-chevron-right',
			'4' => 'carousel-control-next-icon'
		),
		'hidden-small-smartphone' => array(
			'3' => '',
			'4' => 'd-none'
		),
		'hidden-xs' => array(
			'3' => 'hidden-xs',
			'4' => 'd-sm-none'
		),
		'hidden-sm' => array(
			'3' => 'hidden-sm',
			'4' => 'd-md-none'
		),
		'hidden-md' => array(
			'3' => 'hidden-md',
			'4' => 'd-lg-none'
		),
		'hidden-lg' => array(
			'3' => 'hidden-lg',
			'4' => 'd-xl-none'
		),
		'visible-small-smartphone' => array(
			'3' => '',
			'4' => 'd-block'
		),
		'visible-xs' => array(
			'3' => 'visible-xs',
			'4' => 'd-sm-block'
		),
		'visible-sm' => array(
			'3' => 'visible-sm',
			'4' => 'd-md-block'
		),
		'visible-md' => array(
			'3' => 'visible-md',
			'4' => 'd-lg-block'
		),
		'visible-lg' => array(
			'3' => 'visible-lg',
			'4' => 'd-xl-block'
		),
		'col-small-smartphone' => array(
			'3' => '',
			'4' => 'col'
		),
		'col-xs' => array(
			'3' => 'col-xs',
			'4' => 'col-sm'
		),
		'col-sm' => array(
			'3' => 'col-sm',
			'4' => 'col-md'
		),
		'col-md' => array(
			'3' => 'col-md',
			'4' => 'col-lg'
		),
		'col-lg' => array(
			'3' => 'col-lg',
			'4' => 'col-xl'
		),
		'navbar-default' => array(
			'3' => 'navbar-default',
			'4' => 'navbar-light'
		),
		'navbar-expand' => array(
			'3' => '',
			'4' => 'navbar-expand'
		),
		'navbar-expand-md' => array(
			'3' => '',
			'4' => 'navbar-expand-md'
		),
		'navbar-expand-lg' => array(
			'3' => '',
			'4' => 'navbar-expand-lg'
		),
		'navbar-expand-xl' => array(
			'3' => '',
			'4' => 'navbar-expand-xl'
		),
		'navbar-toggle' => array(
			'3' => 'navbar-toggle',
			'4' => 'navbar-toggler'
		),
	);

	/**
	 * Define screen keys.
	 *
	 * @var   array
	 */
	protected static $screens = array('small-smartphone', 'xs', 'sm', 'md', 'lg');

	/**
	 * Loaded Bootstrap version.
	 *
	 * @var   array
	 */
	protected static $versions;

	/**
	 * Method to get loaded Bootstrap version.
	 *
	 * @param   string  $tpl  Name of JoomlaShine template to get version of Bootstrap that is in use.
	 *                        If a template is not provided, loaded Bootstrap version will be retrived
	 *                        from the active template.
	 *
	 * @return  string
	 */
	public static function getVersion($tpl = null)
	{
		// If template not specified, get active template.
		if (empty($tpl))
		{
			$tpl = PlgSystemSunFw::$template ?: JFactory::getApplication()->getTemplate(true);
			$tpl = $tpl->template;
		}

		// Get Bootstrap version once.
		if (!isset(self::$versions[$tpl]))
		{
			// Get template manifest.
			$xml = SunFwHelper::getManifest($tpl);

			// Get Bootstrap version.
			if (!isset($xml->bootstrapVersion))
			{
				$version = '3';
			}
			else
			{
				$version = preg_replace(
					'/^v*(\d+)$/', '\\1', (string) $xml->bootstrapVersion
				);
			}

			// Store Bootstrap version per template.
			self::$versions[$tpl] = $version;
		}

		return self::$versions[$tpl];
	}

	/**
	 * Method to get a valid Bootstrap classes.
	 *
	 * @param   string  $keys  Keys to get appropriated Bootstrap classes.
	 *
	 * @return  string
	 */
	public static function getClass($keys)
	{
		// Get loaded Bootstrap version.
		$version = self::getVersion();

		// Loop thru keys to get valid Bootstrap classes.
		$classes = array();

		foreach ((array) $keys as $key)
		{
			if (array_key_exists($key, self::$mapping) && array_key_exists($version, self::$mapping[$key]))
			{
				$classes[] = self::$mapping[$key][$version];
			}
			elseif (strpos($key, ' ') !== false)
			{
				$classes[] = self::getClass(array_filter(array_map('trim', explode(' ', $key))));
			}
			else
			{
				$classes[] = $key;
			}
		}

		return implode(' ', array_filter($classes));
	}

	/**
	 * Method to get classes to show an element on certain screens.
	 *
	 * @param   array  $screens  Array of screens to show element on.
	 *
	 * @return  string
	 */
	public static function showOn($screens)
	{
		if (!count((array) $screens))
		{
			return '';
		}

		// Get loaded Bootstrap version.
		$version = self::getVersion();

		// Get classes to show an element on the specified screens.
		$classes = array();

		if ($version === '4')
		{
			// If loaded Bootstrap version is v4, get classes to
			// hide an element on screens that are not specified.
			foreach (self::$screens as $screen)
			{
				if (!in_array($screen, $screens))
				{
					$classes[] = self::getClass("hidden-{$screen}");
				}
			}
		}

		foreach ((array) $screens as $screen)
		{
			$classes[] = self::getClass("visible-{$screen}");
		}

		return implode(' ', array_filter($classes));
	}

	/**
	 * Method to get classes to hide an element on certain screens.
	 *
	 * @param   array  $screens  Array of screens to hide element on.
	 *
	 * @return  string
	 */
	public static function hideOn($screens)
	{
		if (!count((array) $screens))
		{
			return '';
		}

		// Get classes to hide an element on the specified screens.
		$classes = array();

		foreach ((array) $screens as $screen)
		{
			$classes[] = self::getClass("hidden-{$screen}");
		}

		return implode(' ', array_filter($classes));
	}

	/**
	 * Method to get column class.
	 *
	 * @param   array  $config  Column width config.
	 *
	 * @return  string
	 */
	public static function colWidth($config)
	{
		$classes = array();

		if (is_array($config))
		{
			foreach ($config as $k => $v)
			{
				$classes[] = ($k = self::getClass($k)) !== '' ? "{$k}-{$v}" : '';
			}
		}

		return implode(' ', array_filter($classes));
	}
}
