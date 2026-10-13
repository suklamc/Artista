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

/**
 * Handle Ajax requests from coming soon pane.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
#[\AllowDynamicProperties]
class SunFwAjaxComingSoon extends SunFwAjax
{
	/**
	 * Get coming soon data from database.
	 *
	 * @param   boolean  $return  Whether to return data or send response back immediately?
	 *
	 * @return  mixed
	 */
	public function getAction($return = false)
	{
		// Get style data.
		$style = SunFwHelper::getSunFwStyle($this->styleID);

		// Prepare response data.
		$data = array(
			'url' => $this->baseUrl,
			'data' => $style ? json_decode($style->coming_soon_data) : null,
			'settings' => SunFwHelper::findTemplateAdminJsonSettings(
				JPATH_ROOT . '/plugins/system/sunfw/assets/joomlashine/admin/js/coming-soon', 'settings.json', true),
			'textMapping' => array(
				'coming-soon' => Text::_('SUNFW_COMING_SOON'),
				'save-coming-soon' => Text::_('SUNFW_SAVE_COMING_SOON'),
				'coming-soon-settings' => Text::_('SUNFW_COMING_SOON_SETTINGS'),
				'coming-soon-not-enabled' => Text::_('SUNFW_COMING_SOON_NOT_ENABLED_MESSAGE'),
				'preview-coming-soon' => Text::_('SUNFW_COMING_SOON_PREVIEW_LABEL'),
				'enable-coming-soon' => Text::_('SUNFW_ENABLE_COMING_SOON'),
				'enable-coming-soon-hint' => Text::_('SUNFW_ENABLE_COMING_SOON_HINT'),
				'coming-soon-page-title' => Text::_('SUNFW_COMING_SOON_PAGE_TITLE'),
				'coming-soon-page-title-hint' => Text::_('SUNFW_COMING_SOON_PAGE_TITLE_HINT'),
				'coming-soon-page-content' => Text::_('SUNFW_COMING_SOON_PAGE_CONTENT'),
				'coming-soon-page-content-hint' => Text::_('SUNFW_COMING_SOON_PAGE_CONTENT_HINT'),
				'coming-soon-count-down' => Text::_('SUNFW_COMING_SOON_COUNT_DOWN'),
				'coming-soon-logo' => Text::_('SUNFW_COMING_SOON_LOGO'),
				'coming-soon-background-image' => Text::_('SUNFW_COMING_SOON_BACKGROUND_IMAGE'),

			    'enable-coming-soon-popover-content' => Text::_('SUNFW_ENABLE_COMING_SOON_POPOVER_CONTENT'),
                'coming-soon-page-title-popover-content' => Text::_('SUNFW_COMING_SOON_PAGE_TITLE_POPOVER_CONTENT'),
			    'coming-soon-page-content-popover-content' => Text::_('SUNFW_COMING_SOON_PAGE_CONTENT_POPOVER_CONTENT'),
                'coming-soon-count-down-popover-content' => Text::_('SUNFW_COMING_SOON_COUNT_DOWN_POPOVER_CONTENT'),
			    'coming-soon-logo-popover-content' => Text::_('SUNFW_COMING_SOON_LOGO_POPOVER_CONTENT'),
                'coming-soon-background-image-popover-content'  => Text::_('SUNFW_COMING_SOON_BACKGROUND_IMAGE_POPOVER_CONTENT'),
			)
		);

		if ($return)
		{
			return $data;
		}

		$this->setResponse($data);
	}

	/**
	 * Save coming soon data to database.
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
				->set($this->dbo->quoteName('coming_soon_data') . '=' . $this->dbo->quote($data))
				->where($this->dbo->quoteName('style_id') . '=' . intval($this->styleID))
				->where($this->dbo->quoteName('template') . '=' . $this->dbo->quote($this->templateName));
		}
		else
		{
			$columns = array(
				'style_id',
				'coming_soon_data',
				'template'
			);
			$values = array(
				intval($this->styleID),
				$this->dbo->quote($data),
				$this->dbo->quote($this->templateName)
			);

			$query->insert($this->dbo->quoteName('#__sunfw_styles'))
				->columns($this->dbo->quoteName($columns))
				->values(implode(', ', $values));
		}

		// Execute query to save Coming Soon data.
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
			'message' => Text::_('SUNFW_COMING_SOON_SAVED_SUCCESSFULLY')
		));
	}
}
