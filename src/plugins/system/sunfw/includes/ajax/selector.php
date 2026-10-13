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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Menu item selector widget.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
#[\AllowDynamicProperties]
class SunFwAjaxSelector extends SunFwAjax
{

	public function indexAction()
	{
		// Get Joomla document object.
		$doc = Factory::getDocument();

		// Get base URLs.
		$root = Uri::root(true);

		// Load required stylesheets.
		$doc->addStylesheet("{$root}/administrator/templates/" . (PlgSystemSunFw::$template ? PlgSystemSunFw::$template->template : $this->app->getTemplate()) . '/css/template.css');
		$doc->addStylesheet("{$root}/plugins/system/sunfw/assets/3rd-party/bootstrap/bootstrap.min.css");
		$doc->addStylesheet("{$root}/plugins/system/sunfw/assets/3rd-party/font-awesome/css/font-awesome.min.css");

		$doc->addStylesheet(
			"{$root}/plugins/system/sunfw/assets/joomlashine/admin/css/style.css?v=" . SUNFW_VERSION . '&d=' . SUNFW_RELEASED_DATE);
		$doc->addStylesheet(
			"{$root}/plugins/system/sunfw/assets/joomlashine/admin/css/module.css?v=" . SUNFW_VERSION . '&d=' . SUNFW_RELEASED_DATE);

		// Load required scripts.
		SunFwCompat::loadJquery();
		HTMLHelper::_('behavior.core');

		// Get items based on specified filters.
		if (!isset($this->items))
		{
			$this->items = $this->getItems();
		}

		$this->render();
	}

	/**
	 * Render a list of filter options for the state of an item.
	 *
	 * @param   string  $name        Name of input field.
	 * @param   string  $selected    Currently selected option.
	 * @param   string  $parameters  Additional attributes of input field.
	 *
	 * @return  void
	 */
	public function renderStatusOptions($name, $selected, $parameters = '')
	{
		echo HTMLHelper::_('select.genericlist',
			array(
				HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_PUBLISHED')),
				HTMLHelper::_('select.option', '-2', Text::_('JTRASHED')),
				HTMLHelper::_('select.option', '0', Text::_('JUNPUBLISHED')),
				HTMLHelper::_('select.option', '1', Text::_('JPUBLISHED')),
				HTMLHelper::_('select.option', '*', Text::_('JALL'))
			), $name, $parameters, 'value', 'text', $selected);
	}

	/**
	 * Render a list of access options.
	 *
	 * @param   string  $name        Name of input field.
	 * @param   string  $selected    Currently selected option.
	 * @param   string  $parameters  Additional attributes of input field.
	 *
	 * @return  void
	 */
	public function renderAccessOptions($name, $selected, $parameters = '')
	{
		echo HTMLHelper::_('access.level', $name, $selected, $parameters,
			array(
				HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_ACCESS'))
			));
	}

	/**
	 * Render a list of language options.
	 *
	 * @param   string  $name        Name of input field.
	 * @param   string  $selected    Currently selected option.
	 * @param   string  $parameters  Additional attributes of input field.
	 *
	 * @return  void
	 */
	public function renderLanguageOptions($name, $selected, $parameters = '')
	{
		echo HTMLHelper::_('select.genericlist',
			array_merge(
				array(
					HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_LANGUAGE')),
					HTMLHelper::_('select.option', '*', Text::_('JALL'))
				), HTMLHelper::_('contentlanguage.existing')), $name, $parameters, 'value', 'text', $selected);
	}

	/**
	 * Get items.
	 *
	 * @return  array
	 */
	public function getItems()
	{
		// Get total items.
		$query = explode('FROM', (string) $this->getListQuery(), 2);
		$query = 'SELECT COUNT(*) FROM' . current(explode('GROUP BY', $query[1]));

		$this->total = (int) $this->dbo->setQuery($query)->loadResult();

		// Prepare query limitation.
		$this->limit = 20;
		$this->start = $this->input->getInt('limitstart', 0);

		if ($this->start >= $this->total)
		{
			$this->start = ( ceil($this->total / $this->limit) - 1 ) * $this->limit;
		}

		return $this->dbo->setQuery($this->getListQuery(), $this->start, $this->limit)
			->loadObjectList();
	}
}
