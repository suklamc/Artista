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
 * Handle Ajax requests from about pane.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
#[\AllowDynamicProperties]
class SunFwAjaxAbout extends SunFwAjax
{

	/**
	 * Get data for about pane.
	 *
	 * @param   boolean  $return  Whether to return data or send response back immediately?
	 *
	 * @return  mixed
	 */
	public function getAction($return = false)
	{
		// Get root URL.
		$root = Uri::root(true);
		
		// Get template manifest.
		$templateManifest = SunFwHelper::getManifest($this->templateName);
		
		/**
		 * Prepare response data.
		 */
		$data = array(
			'frameworkThumb' => "{$root}/plugins/system/sunfw/assets/images/sunfw_preview.png",
			'frameworkRelease' => date('M d, Y', strtotime(SUNFW_RELEASED_DATE)),
			'templateThumb' => "{$root}/templates/{$this->templateName}/template_preview.png",
			'templateRelease' => date('M d, Y', strtotime($templateManifest->creationDate)),
			'enableThumbnailLink' => SunFwHelper::getBrandSetting('showTplThumbnailLink', 1),
			// The copyright line links to joomlashine.com, which JoomlaShine no longer owns.
			// Attribution stays in the source file headers.
			'showCopyright' => 0,
			'textMapping' => array(
				'framework' => Text::_('SUNFW_FRAMEWORK'),
				'template' => Text::_('SUNFW_TEMPLATE'),
				'version' => Text::_('SUNFW_VERSION'),
				'update-available' => Text::_('SUNFW_FRAMEWORK_UPDATE_AVAILABEL'),
				'latest-version' => Text::_('SUNFW_FRAMEWORK_LATEST_VERSION'),
				'release-date' => Text::_('SUNFW_TEMPLATE_RELEASED_DATE'),
				'copyright-by' => Text::_('SUNFW_COPYRIGHT')
			)
		);
		
		if ($return)
		{
			return $data;
		}
		
		$this->setResponse($data);
	}
}
