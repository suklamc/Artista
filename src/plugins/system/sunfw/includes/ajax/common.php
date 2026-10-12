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
 * Handle common Ajax requests from template admin.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
class SunFwAjaxCommon extends SunFwAjax
{
	/**
	 * Handle purchase action.
	 *
	 * @return  void
	 */
	public function purchaseAction()
	{
		// Check if this is the first time user clicked on purchase link.
		$params = SunFwHelper::getTemplateParams($this->templateName);

		if (empty($params['clickedPurchase'])) {
			$params['clickedPurchase'] = time();

			// Store the time the user first clicked on purchase link.
			SunFwHelper::updateExtensionParams($params, 'template', $this->templateName);
		}

		// Redirect user to JoomlaShine.
		JFactory::getApplication()->redirect(
			sprintf(
				SUNFW_TEMPLATE_URL,
				preg_replace('/^tpl_([^\d]+)\d*$/', '\\1', $this->template['id'])
			)
		);
	}

	/**
	 * Get discount message.
	 *
	 * @return  void
	 */
	public function getDiscountAction()
	{
		// Get template parameters.
		$params = SunFwHelper::getTemplateParams($this->templateName);

		if (empty($params['token'])) {
			return;
		}

		// Get discount message.
		$discount = SunFwHttp::get(
			SUNFW_GET_DISCOUNT_URL . "&identified_name={$this->template['id']}&token={$params['token']}"
			. '&lang=' . $this->app->getLanguage()->getTag()
		);

		if (empty($discount['error_code'])) {
			// Save discount.
			$params['currentDiscount'] = $discount['message'];

			SunFwHelper::updateExtensionParams($params, 'template', $this->templateName);
		}

		if (empty($discount['error_code']))
		{
			$this->setResponse($discount['message']);
		}
		else
		{
			throw new Exception($discount['message']);
		}
	}

	/**
	 * Get live chat.
	 *
	 * @return void
	 */
	function getLiveChatAction()
	{
		// Get template parameters.
		$params = SunFwHelper::getTemplateParams($this->templateName);

		if (empty($params['token'])) {
			return;
		}

		// Request live chat code from JoomlaShine server.
		$html = SunFwHttp::get(
			SUNFW_GET_LIVE_CHAT_URL . (strpos(SUNFW_GET_LIVE_CHAT_URL, '?') === false ? '?' : '&')
			. "identified_name={$this->template['id']}&token={$params['token']}"
			. '&lang=' . $this->app->getLanguage()->getTag()
		);

		$this->setResponse($html);
	}

	/**
	 * Get module position from template's manifest file.
	 *
	 * @return  void
	 */
	public function getTemplatePositionAction()
	{
		// Load language file for modules component.
		$this->app->getLanguage()->load('com_modules');

		// Generate path to the modules component.
		$com_modules = JPATH_ADMINISTRATOR . '/components/com_modules';

		// Register path to load the ModulesHelper class.
		JLoader::register('ModulesHelper', "{$com_modules}/helpers/modules.php");

		// Register path to load the HTML helpers of the modules component.
		JHtml::addIncludePath("{$com_modules}/helpers/html");

		// Get module positions from all installed templates.
		$positions = JHtml::_('modules.positions', 0, 1, '');

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
				throw new Exception(JText::_('SUNFW_POSITION_IS_EXISTED'));
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
			'message' => JText::_('SUNFW_SAVED_SUCCESSFULLY')
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

		jimport('');

		if (JLanguageMultilang::isEnabled())
		{
			$lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
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
		$categories = JHtml::_('category.options', $extension,
			array(
				'filter.published' => empty($state) ? null : explode(',', $state),
				'filter.language' => empty($lang) ? null : explode(',', $lang)
			));

		array_unshift($categories, JHtml::_('select.option', 'all', JText::_('SUNFW_ALL')));

		$this->setResponse($categories);
	}

	/**
	 * Get banner data.
	 *
	 * @return  void
	 */
	public function getBannerAction()
	{
		// Get request variable.
		$category = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;

		if (empty($category))
		{
			return;
		}

		// Get parameters.
		$params = SunFwHelper::getExtensionParams('template', $this->template['name']);

		if (empty($params['token']))
		{
			return;
		}

		// Get banner data.
		$banner = SUNFW_GET_BANNER_URL;
		$banner .= '&category_alias=' . ( $category == 'layout-footer' ? 'jsn-sunfw' : 'jsnsunfw' ) . "-{$category}";
		$banner .= "&token={$params['token']}";
		$banner = SunFwHttp::get($banner, 3600);

		$this->setResponse($banner);
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
