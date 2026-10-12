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

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');

/**
 * Class for requesting feedback.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
class SunFwAjaxFeedback extends SunFwAjax
{
	/**
	 * Get data for rendering feedback modal.
	 *
	 * @param   array  $options  Custom parameters.
	 *
	 * @return  void
	 */
	public function getAction($options = array())
	{
		// Get current settings.
		$params = SunFwHelper::getExtensionParams('template', $this->templateName);

		if (empty($params) || empty($params['token']))
		{
			throw new Exception(JText::_('SUNFW_ERROR_MISSING_TOKEN_KEY'));
		}

		// Get predefined answers.
		$answers = SunFwHttp::get(SUNFW_GET_FEEDBACK_URL . "&token={$params['token']}", 3600);

		// Generate link to save feedback and uninstall the specified template.
		$uninstall = "action=send-uninstall-feedback&template_name={$this->templateName}&" . JSession::getFormToken() . '=1';
		$uninstall = JRoute::_("index.php?option=com_ajax&format=json&plugin=sunfw&context=feedback&{$uninstall}", false);

		// Get root URL.
		$root = JUri::root();

		// Get all input components.
		$inputs = array();
		$path = JPATH_ROOT . '/plugins/system/sunfw/assets/joomlashine/admin/js/inputs';

		foreach (glob("{$path}/*.js") as $input)
		{
			$inputs[substr(basename($input), 0, -3)] = $root . str_replace(JPATH_ROOT, '', $input);
		}

		// Prepare parameters.
		$this->setResponse(array_merge(array(
			'modalTitle' => JText::sprintf('SUNFW_FEEDBACK_MODAL_TITLE', $this->template['realName']),
			'modalMessage' => JText::sprintf('SUNFW_FEEDBACK_MODAL_MESSAGE', $this->template['realName']),
			'formTitle' => JText::_('SUNFW_FEEDBACK_MODAL_FORM_TITLE'),
			'question' => JText::_('SUNFW_FEEDBACK_MODAL_QUESTION'),
			'answers' => $answers,
			'customAnswerTextLabel' => JText::_('SUNFW_FEEDBACK_MODAL_CUSTOM_ANSWER_LABEL'),
			'cancelButtonTextLabel' => JText::_('SUNFW_FEEDBACK_MODAL_CANCEL_BUTTON_LABEL'),
			'cancelButtonClassName' => 'btn-default',
			'submitButtonTextLabel' => JText::_('SUNFW_FEEDBACK_MODAL_UNINSTALL_BUTTON_LABEL'),
			'submitButtonClassName' => 'btn-danger',
			'feedbackReceiver' => $uninstall,
			'reloadPageWhenFinish' => true,
			'urls' => array(
				'root' => $root,
				'plugin' => "{$root}plugins/system/sunfw",
				'chosen' => array(
					'css' => ( JFile::exists(JPATH_ROOT . '/media/legacy/css/chosen.min.css') ? '/media/legacy/css/chosen.min.css' : '/media/jui/css/chosen.css' ),
					'js' => ( JFile::exists(JPATH_ROOT . '/media/legacy/js/chosen.min.js') ? '/media/legacy/js/chosen.min.js' : '/media/jui/js/chosen.jquery.min.js' )
				)
			),
			'inputs' => $inputs
		), $options));
	}

	/**
	 * Send feedback then uninstall the specified component.
	 *
	 * @param   boolean  $doNotUninstall  Whether to just send feedback but don't uninstall the component.
	 *
	 * @return  void
	 */
	public function sendUninstallFeedbackAction($doNotUninstall = false)
	{
		// Prevent recursion call.
		static $uninstalling;

		if (isset($uninstalling) && $uninstalling === $this->templateName)
		{
			return;
		}

		if (JFactory::getUser()->authorise('core.delete', 'com_installer'))
		{
			// Get current settings.
			$params = SunFwHelper::getExtensionParams('template', $this->templateName);

			if ($params && !empty($params['token']))
			{
				// Get feedback data.
				$feedback_option  = $this->input->getString('reason');
				$feedback_content = $this->input->getString('experience');

				// Build URL for posting uninstall feedback.
				$link = SUNFW_POST_FEEDBACK_URL;
				$link .= "&identified_name={$this->template['id']}";
				$link .= '&domain=' . JUri::getInstance()->toString(array('host'));
				$link .= "&token={$params['token']}";

				// Send uninstall feedback.
				try
				{
					SunFwHttp::post($link, compact('feedback_option', 'feedback_content'));
				}
				catch (\Exception $e)
				{
					// Do nothing.
				}
			}

			// Get the extension ID of the template being uninstalled.
			if (!$doNotUninstall)
			{
				$eid = $this->dbo->setQuery(
					$this->dbo->getQuery(true)
						->select('extension_id')
						->from('#__extensions')
						->where("type = 'template'")
						->where("element = '{$this->templateName}'")
				)->loadResult();

				// Uninstall the component.
				$uninstalling = $this->templateName;

				JInstaller::getInstance()->uninstall('template', $eid);

				unset($uninstalling);
			}
		}
		else
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
		}
	}
}
