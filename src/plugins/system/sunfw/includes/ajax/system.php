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
use Joomla\CMS\Language\Text;


/**
 * Handle Ajax requests from advanced params pane.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
#[\AllowDynamicProperties]
class SunFwAjaxSystem extends SunFwAjax
{

	/**
	 * Get advanced data from database.
	 *
	 * @param   boolean  $return  Whether to return data or send response back immediately?
	 *
	 * @return  mixed
	 */
	public function getAction($return = false)
	{
		/**
		 * Get system data.
		 */
		$style = SunFwHelper::getSunFwStyle($this->styleID);

		/**
		 * Prepare response data.
		 */
		$data = array(
			'url' => $this->baseUrl,
			'data' => $style ? json_decode($style->system_data) : null,
			'settings' => SunFwHelper::findTemplateAdminJsonSettings(
				JPATH_ROOT . '/plugins/system/sunfw/assets/joomlashine/admin/js/system', 'settings.json', true),
			'textMapping' => array(
				'system' => Text::_('SUNFW_SYSTEM'),
				'save-system' => Text::_('SUNFW_SAVE_SYSTEM'),
				'general-group' => Text::_('SUNFW_GENERAL_SETTINGS_BUTTON_TEXT'),
				'fav-icon' => Text::_('SUNFW_ADVANCED_FAVICON'),
				'fav-icon-hint' => Text::_('SUNFW_ADVANCED_FAVICON_DESC'),
				'cdn-url' => Text::_('SUNFW_ADVANCED_CDN_URL'),
				'cdn-url-hint' => Text::_('SUNFW_ADVANCED_CDN_URL_DESC'),
				'assets-compression' => Text::_('SUNFW_ADVANCED_ASSETS_COMPRESSION'),
				'compression-target' => Text::_('SUNFW_ADVANCED_COMPRESSION_TARGET'),
				'compression-target-hint' => Text::_('SUNFW_ADVANCED_COMPRESSION_TARGET_DESC'),
				'compress-css' => Text::_('SUNFW_ADVANCED_COMPRESS_CSS'),
				'compress-js' => Text::_('SUNFW_ADVANCED_COMPRESS_JS'),
				'max-compression-size' => Text::_('SUNFW_ADVANCED_COMPRESSION_MAX_SIZE'),
				'max-compression-size-hint' => Text::_('SUNFW_ADVANCED_COMPRESSION_MAX_SIZE_DESC'),
				'cache-directory' => Text::_('SUNFW_ADVANCED_CACHE_DIRECTORY'),
				'cache-directory-hint' => Text::_('SUNFW_ADVANCED_CACHE_DIRECTORY_DESC'),
				'verify' => Text::_('SUNFW_VERIFY'),
				'exclude-from-compression' => Text::_('SUNFW_ADVANCED_EXLUCDE_FROM_COMPRESSION'),
				'exclude-from-compression-hint' => Text::_('SUNFW_ADVANCED_EXLUCDE_FROM_COMPRESSION_DESC'),
				'minify-html' => Text::_('SUNFW_ADVANCED_MINIFY_HTML'),
				'minify-html-hint' => Text::_('SUNFW_ADVANCED_MINIFY_HTML_DESC'),
				'move-js-to-bottom' => Text::_('SUNFW_ADVANCED_MOVE_JS_TO_BOTTOM'),
				'move-js-to-bottom-hint' => Text::_('SUNFW_ADVANCED_MOVE_JS_TO_BOTTOM_DESC'),
				'custom-code' => Text::_('SUNFW_ADVANCED_CUSTOM_CODE'),
				'at-begin-of-head-tag' => Text::_('SUNFW_ADVANCED_CUSTOM_AFTER_OPENING_HEAD_TAG'),
				'at-begin-of-head-tag-hint' => Text::_('SUNFW_ADVANCED_CUSTOM_AFTER_OPENING_HEAD_TAG_DESC'),
				'at-end-of-head-tag' => Text::_('SUNFW_ADVANCED_CUSTOM_BEFORE_ENDING_HEAD_TAG'),
				'at-end-of-head-tag-hint' => Text::_('SUNFW_ADVANCED_CUSTOM_BEFORE_ENDING_HEAD_TAG_DESC'),
				'at-begin-of-body-tag' => Text::_('SUNFW_ADVANCED_CUSTOM_AFTER_OPENING_BODY_TAG'),
				'at-begin-of-body-tag-hint' => Text::_('SUNFW_ADVANCED_CUSTOM_AFTER_OPENING_BODY_TAG_DESC'),
				'at-end-of-body-tag' => Text::_('SUNFW_ADVANCED_CUSTOM_BEFORE_ENDING_BODY_TAG'),
				'at-end-of-body-tag-hint' => Text::_('SUNFW_ADVANCED_CUSTOM_BEFORE_ENDING_BODY_TAG_DESC'),
				'custom-files' => Text::_('SUNFW_ADVANCED_CUSTOM_FILES'),
				'custom-css-files' => Text::_('SUNFW_ADVANCED_CUSTOM_FIELS_CSS'),
				'custom-css-files-hint' => Text::_('SUNFW_ADVANCED_CUSTOM_FIELS_CSS_DESC'),
				'custom-js-files' => Text::_('SUNFW_ADVANCED_CUSTOM_FIELS_JS'),
				'custom-js-files-hint' => Text::_('SUNFW_ADVANCED_CUSTOM_FIELS_JS_DESC'),

			    'system-fav-icon-popover-content' => Text::_('SUNFW_ADVANCED_FAVICON_POPOVER_CONTENT'),
			    'system-cdn-url-popover-content' => Text::_('SUNFW_ADVANCED_CDN_URL_POPOVER_CONTENT'),
			    'system-compression-target-popover-content' => Text::_('SUNFW_ADVANCED_COMPRESSION_TARGET_POPOVER_CONTENT'),
			    'system-max-compression-size-popover-content' => Text::_('SUNFW_ADVANCED_COMPRESSION_MAX_SIZE_POPOVER_CONTENT'),
			    'system-cache-directory-popover-content' => Text::_('SUNFW_ADVANCED_CACHE_DIRECTORY_POPOVER_CONTENT'),
			    'system-exclude-from-compression-popover-content' => Text::_('SUNFW_ADVANCED_EXLUCDE_FROM_COMPRESSION_POPOVER_CONTENT'),
			    'system-minify-html-popover-content' => Text::_('SUNFW_ADVANCED_MINIFY_HTML_POPOVER_CONTENT'),
			    'system-move-js-to-bottom-popover-content' => Text::_('SUNFW_ADVANCED_MOVE_JS_TO_BOTTOM_POPOVER_CONTENT'),
			    'system-at-begin-of-head-tag-popover-content' => Text::_('SUNFW_ADVANCED_CUSTOM_AFTER_OPENING_HEAD_TAG_POPOVER_CONTENT'),
			    'system-at-end-of-head-tag-popover-content' => Text::_('SUNFW_ADVANCED_CUSTOM_BEFORE_ENDING_HEAD_TAG_POPOVER_CONTENT'),
			    'system-at-begin-of-body-tag-popover-content' => Text::_('SUNFW_ADVANCED_CUSTOM_AFTER_OPENING_BODY_TAG_POPOVER_CONTENT'),
			    'system-at-end-of-body-tag-popover-content' => Text::_('SUNFW_ADVANCED_CUSTOM_BEFORE_ENDING_BODY_TAG_POPOVER_CONTENT'),
			    'system-custom-css-files-popover-content' => Text::_('SUNFW_ADVANCED_CUSTOM_FIELS_CSS_POPOVER_CONTENT'),
			    'system-custom-js-files-popover-content' => Text::_('SUNFW_ADVANCED_CUSTOM_FIELS_JS_POPOVER_CONTENT'),
			    'system-custom-css-files-invalid-file-format-message' => Text::_('SUNFW_ADVANCED_CUSTOM_FIELS_CSS_INVALID_FILE_FORMAT_MESSAGE'),
			    'add-buton-label' => Text::_('SUNFW_ADD_BUTTON_LABEL'),
			    'enter-file-name-or-file-url' => Text::_('SUNFW_ENTER_A_FILE_NAME_OR_A_FILE_URL')
			)
		);

		if ($return)
		{
			return $data;
		}

		$this->setResponse($data);
	}

	/**
	 * Save data to database
	 *
	 * @throws Exception
	 */
	public function saveAction()
	{
		// Prepare input data.
		$data = $this->input->get('data', '', 'raw');

		if (empty($data))
		{
			throw new Exception('Invalid Request');
		}

		// Build query to save advanced data.
		$style = SunFwHelper::getSunFwStyle($this->styleID);
		$query = $this->dbo->getQuery(true);

		if (count((array) $style))
		{
			$query->update($this->dbo->quoteName('#__sunfw_styles'))
				->set($this->dbo->quoteName('system_data') . '=' . $this->dbo->quote($data))
				->where($this->dbo->quoteName('style_id') . '=' . intval($this->styleID))
				->where($this->dbo->quoteName('template') . '=' . $this->dbo->quote($this->templateName));
		}
		else
		{
			$columns = array(
				'style_id',
				'system_data',
				'template'
			);
			$values = array(
				intval($this->styleID),
				$this->dbo->quote($data),
				$this->dbo->quote($this->templateName)
			);

			$query->insert($this->dbo->quoteName('#__sunfw_styles'))
				->columns($this->dbo->quoteName($columns))
				->values(implode(',', $values));
		}

		// Execute query to save advanced data.
		try
		{
			$this->dbo->setQuery($query);

			if (!$this->dbo->execute())
			{
				throw new Exception($this->dbo->getErrorMsg());
			}

            $data = json_decode($data, true);
			if ($data['cacheDirectory'] != '')
			{
				// Generate path to cache directory
				if (!preg_match('#^(/|\\|[a-z]:)#i', $data['cacheDirectory']))
				{
					$cacheDirectory = JPATH_ROOT . '/' . rtrim($data['cacheDirectory'], '\\/');
				}
				else
				{
					$cacheDirectory = rtrim($data['cacheDirectory'], '\\/');
				}

				// Remove entire cache directory
				!is_dir($cacheDirectory . '/' . $this->templateName) or SunFwFolder::delete($cacheDirectory . '/' . $this->templateName);
			}
		}
		catch (Exception $e)
		{
			throw $e;
		}

		$this->setResponse(array(
			'message' => Text::_('SUNFW_SYSTEM_CONFIG_SAVED_SUCCESSFULLY')
		));
	}

	public function verifyCacheFolderAction()
	{
		$folder = $this->input->getString('folder', '');

		if (empty($folder))
		{
			$this->setResponse(array(
				'pass' => false,
				'message' => Text::_('SUNFW_INVALID_REQUEST')
			));

			return;
		}

		if (!preg_match('#^(/|\\|[a-z]:)#i', $folder))
		{
			$folder = JPATH_ROOT . '/' . $folder;
		}

		// Check if directory exists?
		if (!is_dir($folder))
		{
			$this->setResponse(array(
				'pass' => false,
				'message' => Text::_('SUNFW_DIRECTORY_NOT_FOUND')
			));

			return;
		}

		// Check if directory is outside of document root?
		if (!realpath($folder) ||
			 ( realpath($_SERVER['DOCUMENT_ROOT']) && strpos(realpath($folder), realpath($_SERVER['DOCUMENT_ROOT'])) !== 0 ))
		{
			$this->setResponse(array(
				'pass' => false,
				'message' => Text::_('SUNFW_DIRECTORY_OUT_OF_ROOT')
			));

			return;
		}

		// Check if directory is writable
		$config = Factory::getConfig();

		if (!$config->get('ftp_enable') and !is_writable($folder))
		{
			$this->setResponse(array(
				'pass' => false,
				'message' => Text::_('SUNFW_DIRECTORY_NOT_WRITABLE')
			));

			return;
		}

		$this->setResponse(array(
			'pass' => true,
			'message' => Text::_('SUNFW_DIRECTORY_READY')
		));
	}
}
