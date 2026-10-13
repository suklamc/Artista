<?php
/**
 * @package    Artista (Sun Framework)
 * @copyright  Copyright (C) 2012 JoomlaShine.com. Copyright (C) 2026 Artriva Studios.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * Install script for the Sun Framework plugin.
 *
 * Runs before the framework itself is loaded, so it uses plain PHP and core Joomla only.
 */
#[\AllowDynamicProperties]
class PlgSystemSunFwInstallerScript
{
	/**
	 * Lowest supported PHP version.
	 */
	const MIN_PHP = '8.1.0';

	/**
	 * Lowest supported Joomla version.
	 */
	const MIN_JOOMLA = '4.4.0';

	/**
	 * Check requirements, then stop Joomla from re-running database updates that are
	 * already applied (sites upgrading from the JoomlaShine releases).
	 *
	 * @param   string  $route      install, update, discover_install or uninstall.
	 * @param   object  $installer  The installer adapter.
	 *
	 * @return  boolean
	 */
	public function preflight($route, $installer)
	{
		if ($route === 'uninstall')
		{
			return true;
		}

		if (version_compare(PHP_VERSION, self::MIN_PHP, '<'))
		{
			Log::add('Artista needs PHP ' . self::MIN_PHP . ' or later. This server runs PHP ' . PHP_VERSION . '.', Log::WARNING, 'jerror');

			return false;
		}

		if (version_compare(JVERSION, self::MIN_JOOMLA, '<'))
		{
			Log::add('Artista needs Joomla ' . self::MIN_JOOMLA . ' or later. This site runs Joomla ' . JVERSION . '.', Log::WARNING, 'jerror');

			return false;
		}

		$target = JPATH_ROOT . '/plugins/system/sunfw/database/mysql/updates';

		// Clean up obsolete update files left by old JoomlaShine releases.
		foreach (array('1.3.1', '2.0.3') as $old)
		{
			if (is_file("{$target}/{$old}.sql"))
			{
				@unlink("{$target}/{$old}.sql");
			}
		}

		$dbo    = Factory::getDbo();
		$tables = $dbo->setQuery('SHOW TABLES')->loadColumn();

		if (!in_array($dbo->getPrefix() . 'sunfw_styles', $tables, true))
		{
			return true;
		}

		// The styles table exists: hide update files whose columns are already there.
		$columns = $dbo->setQuery('SHOW COLUMNS FROM ' . $dbo->quoteName('#__sunfw_styles'))->loadColumn();
		$source  = $installer->getParent()->getPath('source') . '/database/mysql/updates';

		foreach (glob("{$source}/*.sql") ?: array() as $update)
		{
			$sql = (string) file_get_contents($update);

			if (!preg_match_all('/ALTER TABLE `?[^\s]+_sunfw_styles`? ADD ([^\s]+)/i', $sql, $matches, PREG_SET_ORDER))
			{
				continue;
			}

			foreach ($matches as $match)
			{
				if (in_array(trim($match[1], '`'), $columns, true))
				{
					// Don't let Joomla import it from the package...
					@unlink($update);

					// ...nor from the installed copy; it's restored in postflight().
					$installed = "{$target}/" . basename($update);

					if (is_file($installed))
					{
						@rename($installed, "{$installed}.bak");
					}

					break;
				}
			}
		}

		// Record the installed version as the schema version.
		$info = $dbo->setQuery(
			$dbo->getQuery(true)
				->select($dbo->quoteName(array('extension_id', 'manifest_cache')))
				->from($dbo->quoteName('#__extensions'))
				->where($dbo->quoteName('element') . ' = ' . $dbo->quote('sunfw'))
				->where($dbo->quoteName('type') . ' = ' . $dbo->quote('plugin'))
				->where($dbo->quoteName('folder') . ' = ' . $dbo->quote('system'))
		)->loadObject();

		if ($info && ($manifest = json_decode((string) $info->manifest_cache)) && !empty($manifest->version))
		{
			$dbo->setQuery(
				$dbo->getQuery(true)
					->update($dbo->quoteName('#__schemas'))
					->set($dbo->quoteName('version_id') . ' = ' . $dbo->quote($manifest->version))
					->where($dbo->quoteName('extension_id') . ' = ' . (int) $info->extension_id)
			)->execute();
		}

		return true;
	}

	/**
	 * Restore the hidden update files and make sure the plugin is enabled.
	 *
	 * @param   string  $route      install, update or discover_install.
	 * @param   object  $installer  The installer adapter.
	 *
	 * @return  void
	 */
	public function postflight($route, $installer)
	{
		if ($route === 'uninstall')
		{
			return;
		}

		foreach (glob(JPATH_ROOT . '/plugins/system/sunfw/database/mysql/updates/*.bak') ?: array() as $file)
		{
			@rename($file, substr($file, 0, -4));
		}

		$dbo = Factory::getDbo();

		$dbo->setQuery(
			$dbo->getQuery(true)
				->update($dbo->quoteName('#__extensions'))
				->set(array(
					$dbo->quoteName('enabled') . ' = 1',
					// Not protected, so uninstalling the Artista package can remove it cleanly.
					$dbo->quoteName('protected') . ' = 0',
					$dbo->quoteName('ordering') . ' = -9',
				))
				->where($dbo->quoteName('element') . ' = ' . $dbo->quote('sunfw'))
				->where($dbo->quoteName('type') . ' = ' . $dbo->quote('plugin'))
				->where($dbo->quoteName('folder') . ' = ' . $dbo->quote('system'))
		)->execute();
	}
}
