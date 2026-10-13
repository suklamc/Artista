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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Handle Ajax requests from social share pane.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
#[\AllowDynamicProperties]
class SunFwAjaxSocialShare extends SunFwAjax
{

	/**
	 * Get social share data from database.
	 *
	 * @param   boolean  $return  Whether to return data or send response back immediately?
	 *
	 * @return  mixed
	 */
	public function getAction($return = false)
	{
		// Get root URL.
		$root = Uri::root(true);

		/**
		 * Get social share data.
		 */
		$style = SunFwHelper::getSunFwStyle($this->styleID);

		/**
		 * Prepare response data.
		 */
		$data = array(
			'url' => $this->baseUrl,
			'data' => $style ? json_decode($style->social_share_data) : null,
			'settings' => SunFwHelper::findTemplateAdminJsonSettings(
				JPATH_ROOT . '/plugins/system/sunfw/assets/joomlashine/admin/js/social-share', 'settings.json', true),
			'textMapping' => array(
				'social-share' => Text::_('SUNFW_SOCIAL_SHARE'),
				'save-social-share' => Text::_('SUNFW_SAVE_SOCIAL_SHARE'),
				'social-share-content-title' => Text::_('SUNFW_SOCIAL_SHARE_PREVIEW_TITLE'),
				'social-share-not-enabled' => Text::_('SUNFW_SOCIAL_SHARE_NOT_ENABLED_MESSAGE'),
				'social-share-settings' => Text::_('SUNFW_SOCIAL_SHARE_SETTINGS'),
				'enable-social-share' => Text::_('SUNFW_ENABLE_SOCIAL_SHARE'),
				'enable-social-share-hint' => Text::_('SUNFW_ENABLE_SOCIAL_SHARE_HINT'),
				'social-share-text' => Text::_('SUNFW_SOCIAL_SHARE_TEXT'),
				'social-share-text-hint' => Text::_('SUNFW_SOCIAL_SHARE_TEXT_HINT'),
				'social-share-buttons' => Text::_('SUNFW_SOCIAL_SHARE_BUTTONS'),
				'social-share-buttons-hint' => Text::_('SUNFW_SOCIAL_SHARE_BUTTONS_HINT'),
				'social-share-buttons-position' => Text::_('SUNFW_SOCIAL_SHARE_BUTTONS_POSITION'),
				'social-share-buttons-position-hint' => Text::_('SUNFW_SOCIAL_SHARE_BUTTONS_POSITION_HINT'),
				'top-left' => Text::_('SUNFW_TOP_LEFT'),
				'top-center' => Text::_('SUNFW_TOP_CENTER'),
				'top-right' => Text::_('SUNFW_TOP_RIGHT'),
				'bottom-left' => Text::_('SUNFW_BOTTOM_LEFT'),
				'bottom-center' => Text::_('SUNFW_BOTTOM_CENTER'),
				'bottom-right' => Text::_('SUNFW_BOTTOM_RIGHT'),
				'social-share-category' => Text::_('SUNFW_SOCIAL_SHARE_CATEGORY'),
				'social-share-category-hint' => Text::_('SUNFW_SOCIAL_SHARE_CATEGORY_HINT'),
				'select-content-category' => Text::_('SUNFW_SELECT_CONTENT_CATEGORY'),
			    'enable-social-share-popover-content' => Text::_('SUNFW_ENABLE_SOCIAL_SHARE_POPOVER_CONTENT'),
			    'social-share-text-popover-content' => Text::_('SUNFW_SOCIAL_SHARE_TEXT_POPOVER_CONTENT'),
			    'social-share-buttons-popover-content' => Text::_('SUNFW_SOCIAL_SHARE_BUTTONS_POPOVER_CONTENT'),
			    'social-share-buttons-position-popover-content' => Text::_('SUNFW_SOCIAL_SHARE_BUTTONS_POSITION_POPOVER_CONTENT'),
			    'social-share-category-popover-content' => Text::_('SUNFW_SOCIAL_SHARE_CATEGORY_POPOVER_CONTENT'),

			)
		);

		if ($return)
		{
			return $data;
		}

		$this->setResponse($data);
	}

	/**
	 * Save style data to database.
	 *
	 * @return  void
	 */
	public function saveAction()
	{
		// Prepare input data.
		$data = $this->input->get('data', '', 'raw');

		if (empty($data))
		{
			throw new Exception('Invalid Request');
		}

		// Build query to save style data.
		$style = SunFwHelper::getSunFwStyle($this->styleID);
		$query = $this->dbo->getQuery(true);

		if ($style)
		{
			$query->update($this->dbo->quoteName('#__sunfw_styles'))
				->set($this->dbo->quoteName('social_share_data') . '=' . $this->dbo->quote($data))
				->where($this->dbo->quoteName('style_id') . '=' . intval($this->styleID))
				->where($this->dbo->quoteName('template') . '=' . $this->dbo->quote($this->templateName));
		}
		else
		{
			$columns = array(
				'style_id',
				'template',
				'social_share_data'
			);
			$values = array(
				intval($this->styleID),
				$this->dbo->quote($this->templateName),
				$this->dbo->quote($data)
			);

			$query->insert($this->dbo->quoteName('#__sunfw_styles'))
				->columns($this->dbo->quoteName($columns))
				->values(implode(', ', $values));
		}

		// Execute query to save social share data.
		try
		{
			$this->dbo->setQuery($query);

			if (!$this->dbo->execute())
			{
				throw new Exception($this->dbo->getErrorMsg());
			}
		}
		catch (Exception $e)
		{
			throw $e;
		}

		$this->setResponse(array(
			'message' => Text::_('SUNFW_SOCIAL_SHARE_SAVED_SUCCESSFULLY')
		));
	}
}
