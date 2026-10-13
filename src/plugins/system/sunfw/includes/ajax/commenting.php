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
 * Handle Ajax requests from commenting pane.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
#[\AllowDynamicProperties]
class SunFwAjaxCommenting extends SunFwAjax
{

	/**
	 * Get commenting data from database.
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
		 * Get commenting data.
		 */
		$style = SunFwHelper::getSunFwStyle($this->styleID);

		/**
		 * Prepare response data.
		 */
		$data = array(
			'url' => $this->baseUrl,
			'data' => $style ? json_decode($style->commenting_data) : null,
			'settings' => SunFwHelper::findTemplateAdminJsonSettings(
				JPATH_ROOT . '/plugins/system/sunfw/assets/joomlashine/admin/js/commenting', 'settings.json', true),
			'textMapping' => array(
				'assignment' => Text::_('SUNFW_MENU_ASSIGNMENT'),
				'save-assignment' => Text::_('SUNFW_SAVE_ASSIGNMENT'),
				'commenting' => Text::_('SUNFW_COMMENTING'),
				'save-commenting' => Text::_('SUNFW_SAVE_COMMENTING'),
				'commenting-content-title' => Text::_('SUNFW_COMMENTING_PREVIEW_TITLE'),
				'commenting-not-enabled' => Text::_('SUNFW_COMMENTING_NOT_ENABLED_MESSAGE'),
				'commenting-settings' => Text::_('SUNFW_COMMENTING_SETTINGS'),
				'enable-commenting' => Text::_('SUNFW_ENABLE_COMMENTING'),
				'enable-commenting-hint' => Text::_('SUNFW_ENABLE_COMMENTING_HINT'),
				'commenting-type' => Text::_('SUNFW_COMMENTING_TYPE'),
				'commenting-type-hint' => Text::_('SUNFW_COMMENTING_TYPE_HINT'),
				'commenting-disqus-subdomain' => Text::_('SUNFW_COMMENTING_DISQUS_SUBDOMAIN'),
				'commenting-disqus-subdomain-hint' => Text::_('SUNFW_COMMENTING_DISQUS_SUBDOMAIN_HINT'),
				'commenting-facebook-app-id' => Text::_('SUNFW_COMMENTING_FACEBOOK_APP_ID'),
				'commenting-facebook-app-id-hint' => Text::_('SUNFW_COMMENTING_FACEBOOK_APP_ID_HINT'),
				'commenting-facebook-num-posts' => Text::_('SUNFW_COMMENTING_FACEBOOK_NUM_POSTS'),
				'commenting-facebook-num-posts-hint' => Text::_('SUNFW_COMMENTING_FACEBOOK_NUM_POSTS_HINT'),
				'commenting-intensedebate-site-account' => Text::_('SUNFW_COMMENTING_INTENSEDEBATE_SITE_ACCOUNT'),
				'commenting-intensedebate-site-account-hint' => Text::_('SUNFW_COMMENTING_INTENSEDEBATE_SITE_ACCOUNT_HINT'),
				'commenting-google-comments-box-width' => Text::_('SUNFW_COMMENTING_GOOGLE_COMMENTS_BOX_WIDTH'),
				'commenting-google-comments-box-width-hint' => Text::_('SUNFW_COMMENTING_GOOGLE_COMMENTS_BOX_WIDTH_HINT'),
				'commenting-show-text' => Text::_('SUNFW_COMMENTING_SHOW_TEXT'),
				'commenting-show-text-hint' => Text::_('SUNFW_COMMENTING_SHOW_TEXT_HINT'),
				'commenting-show-counter' => Text::_('SUNFW_COMMENTING_SHOW_COUNTER'),
				'commenting-show-counter-hint' => Text::_('SUNFW_COMMENTING_SHOW_COUNTER_HINT'),
				'commenting-category' => Text::_('SUNFW_COMMENTING_CATEGORY'),
				'commenting-category-hint' => Text::_('SUNFW_COMMENTING_CATEGORY_HINT'),

			    'enable-commenting-popover-content' => Text::_('SUNFW_ENABLE_COMMENTING_POPOVER_CONTENT'),
			    'commenting-type-popover-content' => Text::_('SUNFW_COMMENTING_TYPE_POPOVER_CONTENT'),
			    'commenting-disqus-subdomain-popover-content' => Text::_('SUNFW_COMMENTING_DISQUS_SUBDOMAIN_POPOVER_CONTENT'),
			    'commenting-facebook-app-id-popover-content' => Text::_('SUNFW_COMMENTING_FACEBOOK_APP_ID_POPOVER_CONTENT'),
			    'commenting-facebook-num-posts-popover-content' => Text::_('SUNFW_COMMENTING_FACEBOOK_NUM_POSTS_POPOVER_CONTENT'),
			    'commenting-intensedebate-site-account-popover-content' => Text::_('SUNFW_COMMENTING_INTENSEDEBATE_SITE_ACCOUNT_POPOVER_CONTENT'),
			    'commenting-google-comments-box-width-popover-content' => Text::_('SUNFW_COMMENTING_GOOGLE_COMMENTS_BOX_WIDTH_POPOVER_CONTENT'),
			    'commenting-show-text-popover-content' => Text::_('SUNFW_COMMENTING_SHOW_TEXT_POPOVER_CONTENT'),
			    'commenting-show-counter-popover-content' => Text::_('SUNFW_COMMENTING_SHOW_COUNTER_POPOVER_CONTENT'),
			    'commenting-category-popover-content' => Text::_('SUNFW_COMMENTING_CATEGORY_POPOVER_CONTENT')
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
				->set($this->dbo->quoteName('commenting_data') . '=' . $this->dbo->quote($data))
				->where($this->dbo->quoteName('style_id') . '=' . intval($this->styleID))
				->where($this->dbo->quoteName('template') . '=' . $this->dbo->quote($this->templateName));
		}
		else
		{
			$columns = array(
				'style_id',
				'template',
				'commenting_data'
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

		// Execute query to save commenting data.
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
			'message' => Text::_('SUNFW_COMMENTING_SAVED_SUCCESSFULLY')
		));
	}
}
