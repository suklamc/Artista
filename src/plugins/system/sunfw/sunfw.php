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

// Define neccessary constants.
require_once dirname(__FILE__) . '/sunfw.defines.php';

/**
 * Plugin class.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
class PlgSystemSunFw extends JPlugin
{
	/**
	 * The name of the plugin.
	 *
	 * @var  string
	 */
	public $_name = 'sunfw';

	/**
	 * The plugin type.
	 *
	 * @var  string
	 */
	public $_type = 'system';

	/**
	 * Whether to automatically load language files.
	 *
	 * @var  boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * Joomla application instance.
	 *
	 * @var  JApplication
	 */
	protected $app;

	/**
	 * Joomla input instance.
	 *
	 * @var  JInput
	 */
	protected $input;

	/**
	 * Joomla user instance.
	 *
	 * @var  JUser
	 */
	protected $user;

	/**
	 * Requested component.
	 *
	 * @var  string
	 */
	protected $option;

	/**
	 * Requested view.
	 *
	 * @var  string
	 */
	protected $view;

	/**
	 * Requested task.
	 *
	 * @var  string
	 */
	protected $task;

	/**
	 * Define prefix for all classes of our framework.
	 *
	 * @var  string
	 */
	protected static $prefix = 'SunFw';

	/**
	 * Variable for holding data of the active template.
	 *
	 * @var  object
	 */
	public static $template;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @return  void
	 */
	public function __construct($subject, $option = array())
	{
		parent::__construct($subject, $option);

		// Register class auto-loader.
		spl_autoload_register(array(
			__CLASS__,
			'autoload'
		));

		// Load plugin language file.
		$this->loadLanguage();

		// Get Joomla's application instance.
		$this->app = JFactory::getApplication();

		// Get Joomla's input object.
		$this->input = $this->app->input;

		// Get Joomla's user instance.
		$this->user = JFactory::getUser();

		// Get necessary request variables.
		$this->option = $this->input->getCmd('option');
		$this->view = $this->input->getCmd('view');
		$this->task = $this->input->getCmd('task');

		// Check if user want to preview the coming soon page.
		if ($this->input->getInt('preview-coming-soon'))
		{
			JFactory::$config->set('offline', '1');
		}
	}

	/**
	 * Implement onAfterInitialise event handler.
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
		if (SunFwCompat::isClient('administrator'))
		{
			if ($this->option == 'com_installer')
			{
				// If there is no any template based on Sun Framework installed, uninstall the Sun Framework plugin.
				if (!$this->hasSunFwBasedTemplate())
				{
					// Get the extension ID of the Sun Framework plugin.
					$db = JFactory::getDbo();
					$qr = $db->getQuery(true)
						->select('extension_id')
						->from('#__extensions')
						->where("type = 'plugin'")
						->where("folder = 'system'")
						->where("element = 'sunfw'");

					if ($eid = $db->setQuery($qr)->loadResult())
					{
						// Unprotect the Sun Framework plugin first.
						$qr = $db->getQuery(true)
							->update('#__extensions')
							->set('protected = 0')
							->where("extension_id = {$eid}");

						if ($db->setQuery($qr)->execute())
						{
							// Get an installer object to uninstall the Sun Framework plugin.
							$installer = JInstaller::getInstance();

							return $installer->uninstall('plugin', $eid);
						}
					}
				}
			}

			// Load library for compliling SCSS to CSS
			include_once dirname(__FILE__) . '/libraries/3rd-party/scssphp/scss.inc.php';
		}
		elseif (SunFwCompat::isClient('site'))
		{
			// Reorder the execution of onAfterRoute event handler if necessary.
			if (JPluginHelper::isEnabled('system', 'jsntplframework'))
			{
				$this->app->registerEvent('onAfterRoute', array(
					&$this,
					'onAfterRoute'
				));
			}
		}
	}

	/**
	 * Implement onAfterRoute event handler to load class overrides.
	 *
	 * @return  void
	 */
	public function onAfterRoute()
	{
		// Make sure this event handler is executed at last order if necessary.
		if (JPluginHelper::isEnabled('system', 'jsntplframework') && !isset($this->onAfterRouteReordered))
		{
			$this->onAfterRouteReordered = true;

			return;
		}

		// Get the template style assigned to the requested frontend page.
		if (SunFwCompat::isClient('site') && !isset(self::$template))
		{
			// Check if the Metaman system plugin is installed and enabled?
			if (JPluginHelper::isEnabled('system', 'metaman'))
			{
				/**
				 * Because the Metaman system plugin calls the JHtml::_ method to load the Bootstrap
				 * CSS framework too early, Joomla might detect a wrong active template style.
				 *
				 * This affects our Sun Framework as a side effect and causes it render the template
				 * style which is set as default for all frontend pages instead of the correct template
				 * style assigned to the requested frontend page.
				 *
				 * To solve this problem, we need to manually get the correct template style instead of
				 * rely on the active template style returned by Joomla's application object.
				 */
				$menu = $this->app->getMenu();
				$item = $menu->getActive();

				if (!$item)
				{
					$item = $menu->getItem($this->input->getInt('Itemid', null));
				}

				$id = 0;

				if (is_object($item))
				{
					// Valid item retrieved.
					$id = $item->template_style_id;
				}

				$tid = $this->input->getUint('templateStyle', 0);

				if (is_numeric($tid) && (int) $tid > 0)
				{
					$id = (int) $tid;
				}

				$cache = JFactory::getCache('com_templates', '');

				if ($this->app->getLanguageFilter())
				{
					$tag = $this->app->getLanguage()->getTag();
				}
				else
				{
					$tag = '';
				}

				$cacheId = 'templates0' . $tag;

				if ($cache->contains($cacheId))
				{
					$templates = $cache->get($cacheId);
				}
				else
				{
					// Load styles.
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true)
						->select('id, home, template, s.params')
						->from('#__template_styles as s')
						->where('s.client_id = 0')
						->where('e.enabled = 1')
						->join('LEFT', '#__extensions as e ON e.element=s.template AND e.type=' . $db->quote('template') . ' AND e.client_id=s.client_id');

					$db->setQuery($query);
					$templates = $db->loadObjectList('id');

					foreach ($templates as &$template)
					{
						// Create home element.
						if ($template->home == 1 && !isset($template_home) || $this->app->getLanguageFilter() && $template->home == $tag)
						{
							$template_home = clone $template;
						}

						$template->params = new JRegistry($template->params);
					}

					// Unset the $template reference to the last $templates[n] item cycled in the foreach above to avoid editing it later.
					unset($template);

					// Add home element, after loop to avoid double execution.
					if (isset($template_home))
					{
						$template_home->params = new JRegistry($template_home->params);
						$templates[0]          = $template_home;
					}

					$cache->store($templates, $cacheId);
				}

				if (isset($templates[$id]))
				{
					$template = $templates[$id];
				}
				else
				{
					$template = $templates[0];
				}

				// Allows for overriding the active template from the request.
				$template_override = $this->input->getCmd('template', '');

				// Only set template override if it is a valid template (= it exists and is enabled).
				if (!empty($template_override))
				{
					if (file_exists(JPATH_THEMES . '/' . $template_override . '/index.php'))
					{
						foreach ($templates as $tmpl)
						{
							if ($tmpl->template === $template_override)
							{
								$template = $tmpl;
								break;
							}
						}
					}
				}

				// Need to filter the default value as well.
				$template->template = JFilterInput::getInstance()->clean($template->template, 'cmd');

				// Fallback template.
				if (!file_exists(JPATH_THEMES . '/' . $template->template . '/index.php'))
				{
					$this->app->enqueueMessage(JText::_('JERROR_ALERTNOTEMPLATE'), 'error');

					// Try to find data for 'beez3' template.
					$original_tmpl = $template->template;

					foreach ($templates as $tmpl)
					{
						if ($tmpl->template === 'beez3')
						{
							$template = $tmpl;
							break;
						}
					}

					// Check, the data were found and if template really exists.
					if (!file_exists(JPATH_THEMES . '/' . $template->template . '/index.php'))
					{
						throw new InvalidArgumentException(JText::sprintf('JERROR_COULD_NOT_FIND_TEMPLATE', $original_tmpl));
					}
				}

				// Cache the result.
				self::$template = $template;
			}
			else
			{
				self::$template = $this->app->getTemplate(true);
			}
		}

		// Instantiate Sun Framework's site renderer if necessary.
		if (SunFwCompat::isClient('site') && SunFwRecognization::detect())
		{
			SunFwSite::getInstance();
		}
	}

	/**
	 * Implement onContentPrepareForm event handler to initialize SunFw template admin.
	 *
	 * @param   object $context Form context.
	 * @param   object $data Form data.
	 *
	 * @return  void
	 */
	public function onContentPrepareForm($context, $data)
	{
		switch ($context->getName())
		{
			case 'com_templates.style':
				if (!empty($data))
				{
					// Read manifest to check if template depends on our framework.
					$templateName = is_object($data) ? $data->template : $data['template'];

					// If editing a style of a SunFw based template, initialize template admin.
					if (SunFwRecognization::detect($templateName))
					{
						SunFwAdmin::getInstance();
					}
				}
			break;

			case 'com_menus.item':
			case 'com_content.article':
				// Get the default site template.
				if (SunFwCompat::isClient('administrator'))
				{
					$dbo = JFactory::getDbo()->setQuery('SELECT * FROM #__template_styles WHERE client_id = 0 AND home = 1;');
					$tpl = $dbo->loadObject();
				}
				else
				{
					$tpl = self::$template;
				}

				// If the default site template is a SunFw based template, load additional form.

				if (SunFwRecognization::detect($tpl->template) && SunFwCompat::isClient('admin'))
				{
					if (SunFwCompat::isClient('site'))
					{
						if (is_string($data->attribs))
						{
							$data->attribs = json_decode($data->attribs, true);
						}
					}
					// Register additional form path.
					JForm::addFormPath(JPATH_PLUGINS . '/system/sunfw/includes/admin/forms');

					// Load additional form fields.
					$context->loadFile($context->getName() == 'com_menus.item' ? 'menu' : 'article', false);

					// Load necessary assets.
					SunFwHelper::loadAssets(false);

					$this->renderExtraOptions = $tpl;
				}
			break;
		}
	}

	/**
	 * Implement onBeforeRender event handler.
	 *
	 * @return  void
	 */
	public function onBeforeRender()
	{
		// Check if we should ask user feedback for why they want to uninstall our product.
		if ($this->user->id && ($request = $this->input->get('jsn', null, 'array')))
		{
			if ($request['action'] === 'feedback' && $request['type'] === 'uninstall' && !empty($request['template']))
			{
				// Make sure component is not uninstalled.
				$dbo = JFactory::getDbo();
				$eid = $dbo->setQuery(
					$dbo->getQuery(true)
						->select('extension_id')
						->from('#__extensions')
						->where("type = 'template'")
						->where('element = ' . $dbo->quote($request['template']))
				)->loadResult();

				if ((int) $eid)
				{
					// Load necessary assets.
					SunFwHelper::loadAssets(false, true);
				}
				else
				{
					$this->input->set('jsn', null);
				}
			}
		}
	}

	/**
	 * Implement onAfterRender event handler.
	 *
	 * @return  void
	 */
	public function onAfterRender()
	{
		// Get the current output buffer.
		$buffer = $this->app->getBody();

		if (isset($this->renderExtraOptions) && $this->renderExtraOptions)
		{
			// Define base Ajax URL.
			$ajaxBase = 'index.php?option=com_ajax&format=json&group=system&plugin=sunfw';
			$ajaxBase .= "&template_name={$this->renderExtraOptions->template}&style_id={$this->renderExtraOptions->id}";
			$ajaxBase .= '&' . JSession::getFormToken() . '=1&context=admin&action=get';
			$ajaxBase = JRoute::_($ajaxBase, false);

			$buffer = str_replace(
				'</body>',
				"<div id='sunfw-extra-options' data-render='ExtraOptions' data-url='{$ajaxBase}'></div></body>",
				$buffer
			);
		}

		// Check if we should ask user feedback for why they want to uninstall our product.
		if ($this->user->id && ($request = $this->input->get('jsn', null, 'array')))
		{
			if ($request['action'] === 'feedback' && $request['type'] === 'uninstall' && !empty($request['template']))
			{
				$buffer = str_replace(
					'</body>',
					SunFwHelper::renderFeedbackModal($request['template']) . '</body>',
					$buffer
				);
			}
		}

		// Fix conflict modal on Bootstrap 4
		if (SunFwCompat::isClient('site'))
		{
		    $bootstrapVersion = SunFwSiteBootstrap::getVersion();
		    if ($bootstrapVersion === '4')
		    {
		        $buffer = str_replace('class="modal hide fade', 'class="modal hide', $buffer);
		    }

            // fix reload google font
            $buffer = str_replace(array('<link href="https://fonts.gstatic.com" rel="stylesheet" />', '<link href="https://fonts.gstatic.com" rel="stylesheet" media="all" attribs="[]"/>'), '<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin />', $buffer);
		}


		// Set new buffer.
		$this->app->setBody($buffer);
	}

	/**
	 * Implement onExtensionBeforeInstall event handler to fix table column duplication problem.
	 *
	 * @param   string            $method     Either 'install' or 'discover_install'.
	 * @param   SimpleXMLElement  $type       Extension type.
	 * @param   SimpleXMLElement  $manifest   Extension manifest data.
	 * @param   integer           $extension  Extension ID if installed.
	 *
	 * @return  void
	 */
	public function onExtensionBeforeInstall($method, $type, $manifest, $extension)
	{
		if ('plg_system_sunfw' == (string) $manifest->name)
		{
			// Rename all update files.
			foreach (JFolder::files(JPATH_ROOT . '/plugins/system/sunfw/database/mysql/updates', '\.sql$') as $file)
			{
				$file = JPATH_ROOT . "/plugins/system/sunfw/database/mysql/updates/{$file}";

				JFile::move($file, "{$file}.bak");
			}
		}
	}

	/**
	 * Implement onExtensionAfterInstall event handler to restore update files.
	 *
	 * @param   JInstaller  $installer  The Joomla installer object.
	 * @param   integer     $eid        ID of the installed extension.
	 *
	 * @return  void
	 */
	public function onExtensionAfterInstall($installer, $eid)
	{
		if ('plg_system_sunfw' == (string) $installer->manifest->name)
		{
			// Restore all update files.
			foreach (JFolder::files(JPATH_ROOT . '/plugins/system/sunfw/database/mysql/updates', '\.bak$') as $file)
			{
				$file = JPATH_ROOT . "/plugins/system/sunfw/database/mysql/updates/{$file}";

				JFile::move($file, substr($file, 0, -4));
			}
		}
	}

	/**
	 * Implement onExtensionAfterSave event handler to clone SunFw style data.
	 *
	 * @return  void
	 */
	public function onExtensionAfterSave($context, $table, $isNew)
	{
		$task = $this->app->input->getString('task', '');

		// If context is not com_templates.style return immediately
		if ($context !== 'com_templates.style' || $table->client_id || !$isNew || $task != 'duplicate')
		{
			return;
		}

		$session = JFactory::getSession();
		$dbo = JFactory::getDBO();

		// If session SUNFW_CLONE_STYLE_ID is not existed then created and assign value to it
		if ($session->has('SUNFW_CLONE_STYLE_ID') == false)
		{
			$sessionData = $session->get('SUNFW_CLONE_STYLE_ID', array());

			if (!count((array) $sessionData))
			{
				$pks = $this->app->input->post->get('cid', array(), 'array');

				$session->set('SUNFW_CLONE_STYLE_ID', $pks);

				$sessionData = $session->get('SUNFW_CLONE_STYLE_ID', array());
			}
		}
		else
		{
			// If get session SUNFW_CLONE_STYLE_ID value if it is existed
			$sessionData = $session->get('SUNFW_CLONE_STYLE_ID', array());
		}

		// Check if clone style is a of style of Sun Framework based template
		if (!SunFwRecognization::detect($table->template))
		{
			unset($sessionData[0]);

			$sessionData = array_values($sessionData);

			$session->set('SUNFW_CLONE_STYLE_ID', $sessionData);

			$sessionData = $session->get('SUNFW_CLONE_STYLE_ID', array());

			if (!count((array) $sessionData))
			{
				$session->clear('SUNFW_CLONE_STYLE_ID');
			}

			return;
		}

		$currentSunFwStyle = SunFwHelper::getSunFwStyle($sessionData[0]);

		if (count((array) $currentSunFwStyle))
		{
			$columns = array(
				'style_id',
				'template',
				'layout_builder_data',
				'appearance_data',
				'system_data',
				'mega_menu_data',
				'cookie_law_data',
				'social_share_data',
				'commenting_data',
				'custom_404_data'
			);

			$values = array(
				intval($table->id),
				$dbo->quote($table->template),
				$dbo->quote($currentSunFwStyle->layout_builder_data),
				$dbo->quote($currentSunFwStyle->appearance_data),
				$dbo->quote($currentSunFwStyle->system_data),
				$dbo->quote($currentSunFwStyle->mega_menu_data),
				$dbo->quote($currentSunFwStyle->cookie_law_data),
				$dbo->quote($currentSunFwStyle->social_share_data),
				$dbo->quote($currentSunFwStyle->commenting_data),
				$dbo->quote($currentSunFwStyle->custom_404_data)
			);

			$query = $dbo->getQuery(true)
				->insert($dbo->quoteName('#__sunfw_styles'))
				->columns($dbo->quoteName($columns))
				->values(implode(',', $values));

			$dbo->setQuery($query);
			$dbo->execute();

			$sufwrender = new SunFwScssrender();
			$sufwrender->compile($table->id, $table->template);
			$sufwrender->compile($table->id, $table->template, "layout");
		}

		unset($sessionData[0]);

		$sessionData = array_values($sessionData);

		$session->set('SUNFW_CLONE_STYLE_ID', $sessionData);

		$sessionData = $session->get('SUNFW_CLONE_STYLE_ID', array());

		if (!count((array) $sessionData))
		{
			$session->clear('SUNFW_CLONE_STYLE_ID');
		}
	}

	/**
	 * Implement onExtensionAfterDelete event handler to clean up SunFw style data.
	 *
	 * @return  void
	 */
	public function onExtensionAfterDelete($context, $table)
	{
		$task = $this->app->input->getString('task', '');

		// Simply return if context is not 'com_templates.style'
		if ($context !== 'com_templates.style' || $table->client_id || $task != 'delete')
		{
			return;
		}

		// Check if deleted style is a of style of Sun Framework based template
		if (!SunFwRecognization::detect($table->template))
		{
			return;
		}

		SunFwHelper::deleteOrphanStyle(array(
			$table->id
		));
	}

	/**
	 * Handle onExtensionBeforeUninstall event.
	 *
	 * @param   int  $eid  ID of the extension just uninstalled.
	 *
	 * @return  void
	 */
	public function onExtensionBeforeUninstall($eid)
	{
		// Get extension data.
		$dbo = JFactory::getDbo();
		$ext = $dbo->setQuery("SELECT * FROM #__extensions WHERE extension_id = {$eid};")->loadObject();

		// Check if a JSN template that depends on JSN Sun Framework is being uninstalled.
		if ($ext && $ext->type === 'template')
		{
			// Make sure the template being uninstalled not belonging to a default style.
			$dbo->setQuery(
				$dbo->getQuery(true)
					->select('COUNT(*)')
					->from('#__template_styles')
					->where('template = ' . $dbo->quote($ext->element))
					->where('home != 0')
			);

			if ((int) $dbo->loadResult() > 0)
			{
				return;
			}

			// Parse the template's manifest cache.
			$info = json_decode($ext->manifest_cache);

			if (stripos($info->author, 'JoomlaShine') !== false
				&& stripos($info->copyright, 'JoomlaShine') !== false)
			{
				// Found a JSN template that is being uninstalled, check if it depends on JSN Sun Framework.
				$xml = JPATH_ROOT . "/templates/{$ext->element}/templateDetails.xml";

				if (JFile::exists($xml) && ($xml = simplexml_load_file($xml))
					&& isset($xml->group) && (string) $xml->group === 'sunfw')
				{
					// Check if the template that is being uninstalled has a valid token.
					$params = SunFwHelper::getExtensionParams('template', $ext->element);

					if ($params && !empty($params['token']))
					{
						// Check if the current screen is extension manager.
						if ($this->option === 'com_installer' && $this->view === 'manage' && $this->task === 'manage.remove')
						{
							// Make sure this is not an Ajax request.
							if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)
								|| strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')
							{
								// If there is only 1 template being uninstalled, ask for feedback.
								$dbo->setQuery(
									$dbo->getQuery(true)
										->select('COUNT(*)')
										->from('#__extensions')
										->where("type = 'template'")
										->where('extension_id IN (' . implode(',', $this->input->get('cid', array(), 'array')) . ')')
								);

								if ((int) $dbo->loadResult() === 1)
								{
									$url = isset($_SERVER['HTTP_REFERER'])
										? $_SERVER['HTTP_REFERER']
										: JRoute::_('index.php?option=com_installer&view=manage');

									$url .= (strpos($url, '?') === false ? '?' : '&')
										. "jsn[action]=feedback&jsn[type]=uninstall&jsn[template]={$ext->element}";

									$this->app->redirect($url);
								}
							}
						}

						// Let JoomlaShine know there is a template uninstalled.
						$client = new SunFwAjaxFeedback($ext->element);

						$client->sendUninstallFeedbackAction(true);

						unset($client);
					}
				}
			}
		}
	}

	/**
	 * Handle Ajax requests.
	 *
	 * @return  void
	 */
	public function onAjaxSunfw()
	{
		// Load plugin language file.
		$this->loadLanguage();

		// Send necessary headers.
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		// Execute Ajax action.
		SunFwAjax::execute();

		// Exit immediately to prevent Joomla from processing further.
		exit();
	}

	/**
	 * Method to check if current site has any SunFw based template.
	 *
	 * @return  boolean
	 */
	protected function hasSunFwBasedTemplate()
	{
		// Get all installed templates.
		$db = JFactory::getDbo();
		$qr = $db->getQuery(true)
			->select('element')
			->from('#__extensions')
			->where('type = "template"');

		if ($tpls = $db->setQuery($qr)->loadObjectList())
		{
			foreach ($tpls as $tpl)
			{
				if (SunFwRecognization::detect($tpl->element))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Class auto-loader.
	 *
	 * @param   string $class_name Name of class to load declaration file for.
	 *
	 * @return  mixed
	 */
	public static function autoload($class_name)
	{
		// Verify class prefix.
		if (0 !== strpos($class_name, self::$prefix))
		{
			return false;
		}

		// Generate file path from class name.
		$base = dirname(__FILE__) . '/includes';
		$path = strtolower(preg_replace(
			array('/([A-Z])/', '/([a-z])([0-9])/'),
			array('/\\1', '\\1/\\2'),
			substr($class_name, strlen(self::$prefix))
		));

		// Find class declaration file.
		$p1 = $path . '.php';
		$p2 = $path . '/' . basename($path) . '.php';

		while (true)
		{
			// Check if file exists in standard path.
			if (@is_file($base . $p1))
			{
				$exists = $p1;

				break;
			}

			// Check if file exists in alternative path.
			if (@is_file($base . $p2))
			{
				$exists = $p2;

				break;
			}

			// If there is no more alternative path, quit the loop.
			if (false === strrpos($p1, '/') || 0 === strrpos($p1, '/'))
			{
				break;
			}

			// Generate more alternative path.
			$p1 = preg_replace('#/([^/]+)$#', '-\\1', $p1);
			$p2 = dirname($p1) . '/' . substr(basename($p1), 0, -4) . '/' . basename($p1);
		}

		// If class declaration file is found, include it.
		if (isset($exists))
		{
			return include_once $base . $exists;
		}

		return false;
	}

	/**
	 * Method to add a message to the top message.
	 *
	 * @return  (array) $item
	 */
	public function SunFwGetComponentAlerts()
	{
	    $link = sprintf(SUNFW_GETTING_STARTED_SERIES_URL, 'top-message');
	    $gettingStartedSeriesMessage = array('id' => 'getting-started-series', 'type' => 'info', 'data' => JText::sprintf('SUNFW_GETTING_STARTED_TOP_MESSAGE', $link));
        return $gettingStartedSeriesMessage;
	}
}
