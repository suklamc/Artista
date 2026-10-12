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

// Import necessary libraries.
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Widget for selecting media file.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
class SunFwAjaxMedia extends SunFwAjax
{

	/**
	 * Define widget name.
	 *
	 * @var  string
	 */
	protected $widget = 'media';

	/**
	 * Define relative path from Joomla root directory to start exploring.
	 *
	 * @var  string
	 */
	protected $root = '';

	/**
	 * Define supported file extensions for exploring and uploading, e.g. 'bmp,gif,jpg,png'.
	 *
	 * @var  string
	 */
	protected $extensions = '*';

	public function __construct()
	{
		parent::__construct();

		// Verify token.
		JSession::checkToken('get') or die('Invalid Token');

		// Make sure root is not outside of Joomla directory.
		$this->abs_root = realpath(JPATH_ROOT . '/' . $this->root);
		$this->work_dir = $this->abs_root;

		if (strpos($this->abs_root, realpath(JPATH_ROOT)) !== 0)
		{
			// Hacking attemp, die immediately.
			jexit('Invalid Root Directory');
		}

		if (empty($this->root))
		{
			$this->root = '/';
		}

		// Prepare request variables.
		$this->handler = $this->input->getString('handler');
		$this->element = $this->input->getString('element');

		$this->selected = trim($this->input->getString('selected'), '/');

		if ($this->selected)
		{
			$this->selected = realpath(JPATH_ROOT . '/' . $this->selected) ? realpath(JPATH_ROOT . '/' . $this->selected) : realpath(
				$this->work_dir . '/' . $this->selected);
		}

		if ($this->selected)
		{
			if (strpos($this->selected, $this->abs_root) !== 0)
			{
				$this->selected = null;
			}
		}

		if ($this->selected)
		{
			// Prepare working directory.
			$this->work_dir = is_file($this->selected) ? dirname($this->selected) : $this->selected;
		}

		// Prepare media filter.
		$this->filter = '.';

		if ($this->extensions != '*')
		{
			$this->filter = '^[\w\s\-\._]+\.(' . str_replace(',', '|', $this->extensions) . ')$';
		}

		// Get form token for the current session.
		$this->token = JSession::getFormToken();

		// Prepare widget links.
		$this->list_url = $this->baseUrl . '&action=list';
		$this->upload_url = $this->baseUrl . '&action=upload';
	}

	public function indexAction()
	{
		// Get Joomla document object.
		$doc = JFactory::getDocument();

		// Get base URLs.
		$root = JURI::root(true);

		// Load required stylesheets.
		$doc->addStylesheet("{$root}/media/jui/css/bootstrap.min.css");
		$doc->addStylesheet("{$root}/plugins/system/sunfw/assets/3rd-party/jquery-ui-1.12.0/jquery-ui.min.css");
		$doc->addStylesheet("{$root}/plugins/system/sunfw/assets/3rd-party/jquery-layout/css/layout-default-latest.css");
		$doc->addStylesheet("{$root}/plugins/system/sunfw/assets/3rd-party/vakata-jstree/themes/default/style.css");
		$doc->addStylesheet("{$root}/plugins/system/sunfw/assets/3rd-party/jquery-file-upload/uploadfile.css");
		$doc->addStylesheet("{$root}/plugins/system/sunfw/assets/3rd-party/font-awesome/css/font-awesome.min.css");
		$doc->addStylesheet("{$root}/plugins/system/sunfw/assets/joomlashine/admin/css/general.css?v=" . SUNFW_VERSION . '&d=' . SUNFW_RELEASED_DATE);

		// Load required scripts.
		JHtml::_('jquery.framework');

		$doc->addScript("{$root}/plugins/system/sunfw/assets/3rd-party/jquery-ui-1.12.0/jquery-ui.min.js");
		$doc->addScript("{$root}/plugins/system/sunfw/assets/3rd-party/jquery-layout/js/jquery.layout-latest.js");
		$doc->addScript("{$root}/plugins/system/sunfw/assets/3rd-party/vakata-jstree/jstree.min.js");
		$doc->addScript("{$root}/plugins/system/sunfw/assets/3rd-party/jquery-file-upload/jquery.uploadfile.min.js");

		$this->render('index');
	}

	public function listAction()
	{
		$this->render('list');
	}

	public function dirAction()
	{
		// Get root.
		if ($this->input->getString('selected', '') == '')
		{
			$list[] = array(
				'id' => str_replace(array(
					'/',
					'\\'
				), '-DS-', $this->root),
				'text' => $this->root,
				'children' => true
			);
		}

		// Get directory list.
		else
		{
			$list = JFolder::folders($this->work_dir);

			// Initialize return value.
			foreach ($list as $k => $v)
			{
				$t = $v;
				$v = trim(str_replace(realpath(JPATH_ROOT), '', $this->work_dir) . '/' . $v, '/\\');

				$list[$k] = array(
					'id' => str_replace(array(
						'/',
						'\\'
					), '-DS-', $v),
					'text' => $t,
					'children' => true
				);
			}
		}

		// Set necessary header.
		header('Content-type: application/json; charset=utf-8');
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Pragma: no-cache");

		// Send response back.
		jexit(json_encode($list));
	}

	public function uploadAction()
	{
		// Check if uploaded file is allowed?
		if (!preg_match("#{$this->filter}#", $_FILES['file']['name']) || !SunFwUtils::check_upload($_FILES['file']) ||
			 !SunFwUtils::check_xss($_FILES['file']['tmp_name']))
		{
			jexit(JText::_('SUNFW_UPLOADED_FILE_TYPE_NOT_SUPPORTED'));
		}

		// Move uploaded file to target directory.
		if (!JFile::upload($_FILES['file']['tmp_name'], $this->work_dir . '/' . $_FILES['file']['name']))
		{
			jexit(JText::_('SUNFW_MOVE_UPLOAD_FILE_FAIL'));
		}

		exit();
	}

	/**
	 * Render a template file.
	 *
	 * @param   string  $tmpl  Template file name to render.
	 * @param   array   $data  Data to pass to template file.
	 *
	 * @return  void
	 */
	protected function render($tmpl = 'index', $data = array())
	{
		$context = $this->input->getCmd('context', 'common');
		$tplFile = dirname(__FILE__) . '/tmpl/media/' . $tmpl . '.php';

		if (!is_file($tplFile) || !is_readable($tplFile))
		{
			throw new Exception('Template file not found: ' . $tplFile);
		}

		// Extract data to seperated variables.
		extract($data);

		// Start output buffer.
		ob_start();

		// Load template file.
		include $tplFile;

		// Send rendered content to client.
		$this->setResponse(ob_get_clean());
	}

	protected function get_media()
	{
		// Get media list
		$this->media = JFolder::files($this->work_dir, $this->filter);

		// Prepare URL for media files.
		foreach ($this->media as $k => $v)
		{
			$v = str_replace(realpath(JPATH_ROOT), JUri::root(), $this->work_dir) . '/' . $v;

			$this->media[$k] = str_replace('\\', '/', $v);
		}
	}
}
