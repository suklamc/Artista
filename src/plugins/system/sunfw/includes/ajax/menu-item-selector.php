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
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;

/**
 * Menu item selector widget.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
#[\AllowDynamicProperties]
class SunFwAjaxMenuItemSelector extends SunFwAjaxSelector
{

	public function indexAction()
	{
		// Get Joomla language object.
		$lang = Factory::getLanguage();

		// Load menus component language.
		$lang->load('com_menus', JPATH_ADMINISTRATOR);

		// Get the menu items model.
		$this->model = Factory::getApplication()->bootComponent('com_menus')->getMVCFactory()
			->createModel('Items', 'Administrator', array('ignore_request' => true));

		// Prepare states.
		$this->model->setState('filter.menutype', $this->input->getString('menutype'));
		$this->model->setState('filter.published', $this->input->getString('published'));
		$this->model->setState('filter.access', $this->input->getString('access'));
		$this->model->setState('filter.language', $this->input->getString('language'));
		$this->model->setState('filter.level', $this->input->getString('level'));
		$this->model->setState('filter.parent_id', $this->input->getString('parent_id'));

		// Prepare list limit.
		$this->limit = 20;
		$this->total = $this->model->getTotal();
		$this->start = $this->input->getInt('limitstart', 0);

		if ($this->start >= $this->total)
		{
			$this->start = ( ceil($this->total / $this->limit) - 1 ) * $this->limit;
		}

		// Get items.
		$this->model->setState('list.limit', $this->limit);
		$this->model->setState('list.start', $this->start);

		$this->items = $this->model->getItems();

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as $item)
		{
			// Item type text
			switch ($item->type)
			{
				case 'url':
					$value = Text::_('COM_MENUS_TYPE_EXTERNAL_URL');
				break;

				case 'alias':
					$value = Text::_('COM_MENUS_TYPE_ALIAS');
				break;

				case 'separator':
					$value = Text::_('COM_MENUS_TYPE_SEPARATOR');
				break;

				case 'heading':
					$value = Text::_('COM_MENUS_TYPE_HEADING');
				break;

				case 'container':
					$value = Text::_('COM_MENUS_TYPE_CONTAINER');
				break;

				case 'component':
				default:
					// Load language
					$lang->load($item->componentname . '.sys', JPATH_ADMINISTRATOR, null, false, true) || $lang->load(
						$item->componentname . '.sys', JPATH_ADMINISTRATOR . '/components/' . $item->componentname, null, false, true);

					if (!empty($item->componentname))
					{
						$titleParts = array();
						$titleParts[] = Text::_($item->componentname);
						$vars = null;

						parse_str($item->link, $vars);

						if (isset($vars['view']))
						{
							// Attempt to load the view xml file.
							$file = JPATH_SITE . '/components/' . $item->componentname . '/views/' . $vars['view'] . '/metadata.xml';

							if (!is_file($file))
							{
								$file = JPATH_SITE . '/components/' . $item->componentname . '/view/' . $vars['view'] . '/metadata.xml';
							}

							if (is_file($file) && $xml = simplexml_load_file($file))
							{
								// Look for the first view node off of the root node.
								if ($view = $xml->xpath('view[1]'))
								{
									// Add view title if present.
									if (!empty($view[0]['title']))
									{
										$viewTitle = trim((string) $view[0]['title']);

										// Check if the key is valid. Needed due to B/C so we don't show untranslated keys. This check should be removed with Joomla 4.
										if ($lang->hasKey($viewTitle))
										{
											$titleParts[] = Text::_($viewTitle);
										}
									}
								}
							}

							$vars['layout'] = isset($vars['layout']) ? $vars['layout'] : 'default';

							// Attempt to load the layout xml file.
							// If Alternative Menu Item, get template folder for layout file
							if (strpos($vars['layout'], ':') > 0)
							{
								// Use template folder for layout file
								$temp = explode(':', $vars['layout']);
								$file = JPATH_SITE . '/templates/' . $temp[0] . '/html/' . $item->componentname . '/' . $vars['view'] . '/' .
									 $temp[1] . '.xml';

								// Load template language file
								$lang->load('tpl_' . $temp[0] . '.sys', JPATH_SITE, null, false, true) ||
									 $lang->load('tpl_' . $temp[0] . '.sys', JPATH_SITE . '/templates/' . $temp[0], null, false, true);
							}
							else
							{
								// Get XML file from component folder for standard layouts
								$file = JPATH_SITE . '/components/' . $item->componentname . '/views/' . $vars['view'] . '/tmpl/' .
									 $vars['layout'] . '.xml';

								if (!file_exists($file))
								{
									$file = JPATH_SITE . '/components/' . $item->componentname . '/view/' . $vars['view'] . '/tmpl/' .
										 $vars['layout'] . '.xml';
								}
							}

							if (is_file($file) && $xml = simplexml_load_file($file))
							{
								// Look for the first view node off of the root node.
								if ($layout = $xml->xpath('layout[1]'))
								{
									if (!empty($layout[0]['title']))
									{
										$titleParts[] = Text::_(trim((string) $layout[0]['title']));
									}
								}

								if (!empty($layout[0]->message[0]))
								{
									$item->item_type_desc = Text::_(trim((string) $layout[0]->message[0]));
								}
							}

							unset($xml);

							// Special case if neither a view nor layout title is found
							if (count((array) $titleParts) == 1)
							{
								$titleParts[] = $vars['view'];
							}
						}
						$value = implode(' » ', $titleParts);
					}
					else
					{
						if (preg_match("/^index.php\?option=([a-zA-Z\-0-9_]*)/", $item->link, $result))
						{
							$value = Text::sprintf('COM_MENUS_TYPE_UNEXISTING', $result[1]);
						}
						else
						{
							$value = Text::_('COM_MENUS_TYPE_UNKNOWN');
						}
					}
				break;
			}

			$item->item_type = $value;
			$item->protected = $item->menutype == 'main';
		}

		parent::indexAction();
	}

	/**
	 * Render a list of menu types.
	 *
	 * @param   string  $name        Name of input field.
	 * @param   string  $selected    Currently selected option.
	 * @param   string  $parameters  Additional attributes of input field.
	 *
	 * @return  void
	 */
	public function renderMenuTypeOptions($name, $selected, $parameters = '')
	{
		// Get a list of available menu types.
		$menus = array();

		foreach (SunFwHelper::getAllAvailableMenus() as $menu)
		{
			$menu->text = trim(current(explode('[', $menu->text)));

			$menus[] = $menu;
		}

		// Render menu types select box.
		$types = array_merge(array(
			HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_MENU'))
		), $menus);

		echo HTMLHelper::_('select.genericlist', $types, $name, $parameters, 'value', 'text', $selected);
	}

	/**
	 * Render a list of max. levels.
	 *
	 * @param   string  $name        Name of input field.
	 * @param   string  $selected    Currently selected option.
	 * @param   string  $parameters  Additional attributes of input field.
	 *
	 * @return  void
	 */
	public function renderMaxLevelOptions($name, $selected, $parameters = '')
	{
		// Render max. levels select box.
		echo HTMLHelper::_('select.genericlist',
			array(
				HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_MAX_LEVELS')),
				HTMLHelper::_('select.option', '1', Text::_('J1')),
				HTMLHelper::_('select.option', '2', Text::_('J2')),
				HTMLHelper::_('select.option', '3', Text::_('J3')),
				HTMLHelper::_('select.option', '4', Text::_('J4')),
				HTMLHelper::_('select.option', '5', Text::_('J5')),
				HTMLHelper::_('select.option', '6', Text::_('J6')),
				HTMLHelper::_('select.option', '7', Text::_('J7')),
				HTMLHelper::_('select.option', '8', Text::_('J8')),
				HTMLHelper::_('select.option', '9', Text::_('J9')),
				HTMLHelper::_('select.option', '10', Text::_('J10'))
			), $name, $parameters, 'value', 'text', $selected);
	}

	/**
	 * Render a list of parent menu items.
	 *
	 * @param   string  $name        Name of input field.
	 * @param   string  $selected    Currently selected option.
	 * @param   string  $parameters  Additional attributes of input field.
	 *
	 * @return  void
	 */
	public function renderParentItemOptions($name, $selected, $parameters = '')
	{
		// Get selected menu type.
		$menuType = $this->input->getString('menutype', $this->app->getUserState('com_menus.items.menutype', ''));

		// Get the menu items.
		$items = MenusHelper::getMenuLinks($menuType);

		// Build group for a specific menu type.
		$groups = array(
			0 => array(
				HTMLHelper::_('select.option', '', Text::_('COM_MENUS_FILTER_SELECT_PARENT_MENU_ITEM'))
			)
		);

		if ($menuType)
		{
			// If the menutype is empty, group the items by menutype.
			$this->dbo->setQuery(
				$this->dbo->getQuery(true)
					->select('title')
					->from('#__menu_types')
					->where('menutype = ' . $this->dbo->quote($menuType)));

			try
			{
				$menuTitle = $this->dbo->loadResult();
			}
			catch (RuntimeException $e)
			{
				$menuTitle = $menuType;
			}

			// Initialize the group.
			$groups[$menuTitle] = array();

			// Build the options array.
			foreach ($items as $key => $link)
			{
				// Unset if item is menu_item_root
				if ($link->text === 'Menu_Item_Root')
				{
					unset($items[$key]);

					continue;
				}

				$levelPrefix = str_repeat('- ', max(0, $link->level - 1));

				// Displays language code if not set to All
				if ($link->language !== '*')
				{
					$lang = ' (' . $link->language . ')';
				}
				else
				{
					$lang = '';
				}

				$groups[$menuTitle][] = HTMLHelper::_('select.option', $link->value, $levelPrefix . $link->text . $lang, 'value', 'text');
			}
		}
		// Build groups for all menu types.
		else
		{
			// Build the groups arrays.
			foreach ($items as $menu)
			{
				// Initialize the group.
				$groups[$menu->title] = array();

				// Build the options array.
				foreach ($menu->links as $link)
				{
					$levelPrefix = str_repeat('- ', $link->level - 1);

					// Displays language code if not set to All
					if ($link->language !== '*')
					{
						$lang = ' (' . $link->language . ')';
					}
					else
					{
						$lang = '';
					}

					$groups[$menu->title][] = HTMLHelper::_('select.option', $link->value, $levelPrefix . $link->text . $lang, 'value', 'text');
				}
			}
		}

		// Render module types select box.
		echo HTMLHelper::_('select.groupedlist', $groups, $name,
			array(
				'list.attr' => $parameters,
				'list.select' => $selected,
				'group.items' => null,
				'option.key.toHtml' => false,
				'option.text.toHtml' => false
			));
	}
}
