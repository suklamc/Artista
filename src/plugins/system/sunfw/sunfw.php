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

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;

// Define neccessary constants.
require_once dirname(__FILE__) . '/sunfw.defines.php';

/**
 * Plugin class.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
#[\AllowDynamicProperties]
class PlgSystemSunFw extends CMSPlugin
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
		$this->app = Factory::getApplication();

		// Get Joomla's input object.
		$this->input = $this->app->input;

		// Get Joomla's user instance.
		$this->user = Factory::getUser();

		// Get necessary request variables.
		$this->option = $this->input->getCmd('option');
		$this->view = $this->input->getCmd('view');
		$this->task = $this->input->getCmd('task');

		// Check if user want to preview the coming soon page.
		if ($this->input->getInt('preview-coming-soon'))
		{
			Factory::$config->set('offline', '1');
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
					$db = Factory::getDbo();
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
							$installer = Installer::getInstance();

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
			if (PluginHelper::isEnabled('system', 'jsntplframework'))
			{
				$this->app->registerEvent('onAfterRoute', function () {
					$this->onAfterRoute();
				});
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
		if (PluginHelper::isEnabled('system', 'jsntplframework') && !isset($this->onAfterRouteReordered))
		{
			$this->onAfterRouteReordered = true;

			return;
		}

		// Get the template style assigned to the requested frontend page.
		if (SunFwCompat::isClient('site') && !isset(self::$template))
		{
			// Check if the Metaman system plugin is installed and enabled?
			if (PluginHelper::isEnabled('system', 'metaman'))
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

				$cache = Factory::getCache('com_templates', '');

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
					$db    = Factory::getDbo();
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

						$template->params = new Registry($template->params);
					}

					// Unset the $template reference to the last $templates[n] item cycled in the foreach above to avoid editing it later.
					unset($template);

					// Add home element, after loop to avoid double execution.
					if (isset($template_home))
					{
						$template_home->params = new Registry($template_home->params);
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
				$template->template = InputFilter::getInstance()->clean($template->template, 'cmd');

				// Fallback template.
				if (!file_exists(JPATH_THEMES . '/' . $template->template . '/index.php'))
				{
					$this->app->enqueueMessage(Text::_('JERROR_ALERTNOTEMPLATE'), 'error');

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
						throw new InvalidArgumentException(Text::sprintf('JERROR_COULD_NOT_FIND_TEMPLATE', $original_tmpl));
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
	 * Add social share buttons and commenting above an article (when configured).
	 *
	 * @param   string   $context  The context of the content being displayed.
	 * @param   object   &$row     The article.
	 * @param   object   &$params  The article params.
	 * @param   integer  $page     The page number.
	 *
	 * @return  string
	 */
	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
		return $this->renderArticleExtras('onContentBeforeDisplay', $context, $row, $params, $page);
	}

	/**
	 * Add social share buttons and commenting below an article (when configured).
	 *
	 * @param   string   $context  The context of the content being displayed.
	 * @param   object   &$row     The article.
	 * @param   object   &$params  The article params.
	 * @param   integer  $page     The page number.
	 *
	 * @return  string
	 */
	public function onContentAfterDisplay($context, &$row, &$params, $page = 0)
	{
		return $this->renderArticleExtras('onContentAfterDisplay', $context, $row, $params, $page);
	}

	/**
	 * Render social share and commenting through the active Sun Framework site renderer.
	 *
	 * @return  string
	 */
	protected function renderArticleExtras($eventName, $context, &$row, &$params, $page)
	{
		if (!SunFwCompat::isClient('site') || !SunFwSite::hasInstance())
		{
			return '';
		}

		$site = SunFwSite::getInstance();
		$html = '';

		foreach (array('renderSocialShare', 'renderCommenting') as $method)
		{
			$result = $site->{$method}($context, $row, $params, $page, $eventName);

			if (is_string($result))
			{
				$html .= $result;
			}
		}

		return $html;
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
					$dbo = Factory::getDbo()->setQuery('SELECT * FROM #__template_styles WHERE client_id = 0 AND home = 1;');
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
					Form::addFormPath(JPATH_PLUGINS . '/system/sunfw/includes/admin/forms');

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
			$ajaxBase .= '&' . Session::getFormToken() . '=1&context=admin&action=get';
			$ajaxBase = Route::_($ajaxBase, false);

			$buffer = str_replace(
				'</body>',
				"<div id='sunfw-extra-options' data-render='ExtraOptions' data-url='{$ajaxBase}'></div></body>",
				$buffer
			);
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
			foreach (SunFwFolder::files(JPATH_ROOT . '/plugins/system/sunfw/database/mysql/updates', '\.sql$') as $file)
			{
				$file = JPATH_ROOT . "/plugins/system/sunfw/database/mysql/updates/{$file}";

				SunFwFile::move($file, "{$file}.bak");
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
			foreach (SunFwFolder::files(JPATH_ROOT . '/plugins/system/sunfw/database/mysql/updates', '\.bak$') as $file)
			{
				$file = JPATH_ROOT . "/plugins/system/sunfw/database/mysql/updates/{$file}";

				SunFwFile::move($file, substr($file, 0, -4));
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

		$session = Factory::getSession();
		$dbo = Factory::getDBO();

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
		$db = Factory::getDbo();
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

}
