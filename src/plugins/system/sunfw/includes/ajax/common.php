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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;

/**
 * Handle common Ajax requests from template admin.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
#[\AllowDynamicProperties]
class SunFwAjaxCommon extends SunFwAjax
{
	/**
	 * Get module position from template's manifest file.
	 *
	 * @return  void
	 */
	public function getTemplatePositionAction()
	{
		// Load language file for modules component.
		$this->app->getLanguage()->load('com_modules');

		// Booting the modules component registers its 'modules.*' HTML helpers.
		$this->app->bootComponent('com_modules');

		// Get module positions from all installed templates.
		$positions = HTMLHelper::_('modules.positions', 0, 1, '');

		$this->setResponse($positions);
	}

	/**
	 * Save a new module position to template's mainifest file.
	 *
	 * @throws  Exception
	 */
	public function saveTemplatePositionAction()
	{
		// Prepare input data.
		$position = $this->input->getString('position', '');

		if (empty($position))
		{
			throw new Exception('Invalid Request');
		}

		// Prepare template's XML manifest data.
		$manifest = SunFwHelper::getManifest($this->templateName, 'template', null, true);

		foreach ($manifest->xpath('positions/position') as $pos)
		{
			if ((string) $pos == $position)
			{
				throw new Exception(Text::_('SUNFW_POSITION_IS_EXISTED'));
			}
		}

		// Add new position then save updated XML data to manifest file.
		$manifest->positions->addChild('position', $position);

		try
		{
			SunFwHelper::updateManifest($this->templateName, $manifest);
		}
		catch (Exception $e)
		{
			throw $e;
		}

		$this->setResponse(array(
			'message' => Text::_('SUNFW_SAVED_SUCCESSFULLY')
		));
	}

	/**
	 * Get all available menu items.
	 *
	 * @return  void
	 */
	public function getMenuItemsAction()
	{
		foreach (SunFwHelper::getAllAvailableMenus(true, 10) as $menu)
		{
			$menus[$menu->value] = $menu;
		}

		$this->setResponse(is_array($menus) ? $menus : array());
	}

	/**
	 * Get menu type.
	 *
	 * @return  void
	 */
	public function getMenuTypeAction()
	{
		// Get all available menus.
		$menus = SunFwHelper::getAllAvailableMenus();

		$this->setResponse(is_array($menus) ? $menus : array());
	}

	/**
	 * Get Module style .
	 *
	 * @return  void
	 */
	public function getModuleStyleAction()
	{
		$moduleStyle = array();
		$defaultModuleStyles = SunFwHelper::getDefaultModuleStyle($this->styleID);

		if (count((array) $defaultModuleStyles))
		{
			foreach ($defaultModuleStyles['appearance']['modules'] as $key => $value)
			{
				$tmp = array();
				$tmp['text'] = ucfirst(str_replace('-', ' ', $key));
				$tmp['value'] = $key;

				$moduleStyle[] = $tmp;
			}
		}

		$this->setResponse(is_array($moduleStyle) ? $moduleStyle : array());
	}

	/**
	 * Get an article.
	 *
	 * @return  void
	 */
	public function getArticleAction()
	{
		// Get default language if multi-language is enabled.
		$lang = '';


		if (Multilanguage::isEnabled())
		{
			$lang = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		// Get requested article.
		$id = $this->input->getInt('articleId');

		if (!$id)
		{
			throw new Exception('Invalid Request');
		}

		$article = SunFwSiteHelper::getArticle($id, $lang);

		$this->setResponse($article);
	}

	/**
	 * Get all available content categories.
	 *
	 * @return  void
	 */
	public function getContentCategoryAction()
	{
		// Get request variables.
		$extension = $this->input->getCmd('extension', 'com_content');
		$state = $this->input->getString('state', '1');
		$lang = $this->input->getString('lang', '');

		// Get list of content category.
		$categories = HTMLHelper::_('category.options', $extension,
			array(
				'filter.published' => empty($state) ? null : explode(',', $state),
				'filter.language' => empty($lang) ? null : explode(',', $lang)
			));

		array_unshift($categories, HTMLHelper::_('select.option', 'all', Text::_('SUNFW_ALL')));

		$this->setResponse($categories);
	}

	/**
	 * Save plugin parameters
	 *
	 * @return  void
	 */
	public function savePluginParamsAction()
	{
		// Get request parameters.
		$params = isset($_REQUEST['params']) ? $_REQUEST['params'] : null;

		if (empty($params))
		{
			throw new Exception('Invalid Request');
		}

		// Update plugin params.
		SunFwHelper::updateExtensionParams($params, 'plugin', 'sunfw', 'system');
	}

	/**
	 * Get FontAwesome 4.7 icon list.
	 *
	 * @return void
	 */
	public function getFontAwesomeIconsAction()
	{
		// Read FontAwesome icon list from file.
		$list = json_decode(file_get_contents(SUNFW_PATH . '/assets/3rd-party/font-awesome/font-list.json'));

		$this->setResponse($list);
	}
}
