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

/**
 * Image selector widget.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
class SunFwAjaxImageSelector extends SunFwAjaxMedia
{

	/**
	 * Define widget name.
	 *
	 * @var  string
	 */
	protected $widget = 'images';

	/**
	 * Define relative path from Joomla root directory to start exploring.
	 *
	 * @var  string
	 */
	protected $root = 'images';

	/**
	 * Define supported file extensions for exploring and uploading, e.g. 'bmp,gif,jpg,png'.
	 *
	 * @var  string
	 */
	protected $extensions = 'bmp,gif,ico,jpg,jpeg,png';
}
