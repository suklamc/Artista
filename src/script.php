<?php
/**
 * @package    Artista
 * @copyright  Copyright (C) 2026 Artriva Studios.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Package install script: stop early, before anything is copied, on an unsupported server.
 */
class Pkg_ArtistaInstallerScript
{
	const MIN_PHP    = '8.1.0';
	const MIN_JOOMLA = '4.4.0';

	public function preflight($route, $installer)
	{
		if ($route === 'uninstall')
		{
			return true;
		}

		$app = Factory::getApplication();

		if (version_compare(PHP_VERSION, self::MIN_PHP, '<'))
		{
			$app->enqueueMessage('Artista needs PHP ' . self::MIN_PHP . ' or later. This server runs PHP ' . PHP_VERSION . '.', 'error');

			return false;
		}

		if (version_compare(JVERSION, self::MIN_JOOMLA, '<'))
		{
			$app->enqueueMessage('Artista needs Joomla ' . self::MIN_JOOMLA . ' or later. This site runs Joomla ' . JVERSION . '.', 'error');

			return false;
		}

		return true;
	}

	/**
	 * After installing or updating, remove files that Artista no longer ships.
	 *
	 * Joomla only removes whole folders listed in an old manifest, so a site upgraded from
	 * JoomlaShine's release would otherwise keep the removed sample-data, licence and
	 * Joomla 3 override code, some of it still reachable over Ajax. Site files (custom.css,
	 * generated CSS, backups) are never touched.
	 */
	public function postflight($route, $installer)
	{
		if ($route !== 'install' && $route !== 'update' && $route !== 'discover_install')
		{
			return;
		}

		$allowed = array(
			JPATH_ROOT . '/plugins/system/sunfw/',
			JPATH_ROOT . '/templates/jsn_artista2_pro/',
		);

		foreach (self::OBSOLETE as $relative)
		{
			$path = JPATH_ROOT . '/' . $relative;

			if (!file_exists($path) && !is_link($path))
			{
				continue;
			}

			// Only ever delete inside the plugin and template folders.
			$real = realpath($path);
			$safe = false;

			foreach ($allowed as $root)
			{
				$rootReal = realpath($root);

				if ($real !== false && $rootReal !== false && strpos($real . '/', rtrim($rootReal, '/') . '/') === 0 && $real !== $rootReal)
				{
					$safe = true;
				}
			}

			if ($safe)
			{
				self::removePath($real);
			}
		}
	}

	/**
	 * Delete a file, or a folder and everything in it.
	 */
	private static function removePath($path)
	{
		if (is_dir($path) && !is_link($path))
		{
			foreach (scandir($path) ?: array() as $item)
			{
				if ($item !== '.' && $item !== '..')
				{
					self::removePath($path . '/' . $item);
				}
			}

			@rmdir($path);

			return;
		}

		@unlink($path);
	}

	/**
	 * Files and folders shipped by JoomlaShine's Sun Framework 2.2.33 / JSN Artista 2 Pro 2.1.3
	 * (or left by an earlier patch attempt) that Artista 3 removed.
	 */
	const OBSOLETE = array(
		'plugins/system/sunfw/assets/joomlashine/admin/js/live-chat-settings',
		'plugins/system/sunfw/assets/joomlashine/admin/js/original-sunfw.js',
		'plugins/system/sunfw/assets/joomlashine/admin/js/privacy-settings',
		'plugins/system/sunfw/assets/joomlashine/admin/js/sample-data',
		'plugins/system/sunfw/assets/joomlashine/admin/js/sunfw-patch.js',
		'plugins/system/sunfw/assets/joomlashine/admin/js/user-account',
		'plugins/system/sunfw/includes/ajax/downloader.php',
		'plugins/system/sunfw/includes/ajax/feedback.php',
		'plugins/system/sunfw/includes/ajax/integrity.php',
		'plugins/system/sunfw/includes/ajax/sample-data.php',
		'plugins/system/sunfw/includes/ajax/tmpl/update',
		'plugins/system/sunfw/includes/ajax/update.php',
		'plugins/system/sunfw/includes/ajax/user-account.php',
		'plugins/system/sunfw/includes/api',
		'plugins/system/sunfw/includes/archive',
		'plugins/system/sunfw/includes/client',
		'plugins/system/sunfw/includes/integrity.php',
		'plugins/system/sunfw/includes/overwrite',
		'plugins/system/sunfw/includes/validatelicense.php',
		'plugins/system/sunfw/libraries/3rd-party/httpclient',
		'plugins/system/sunfw/libraries/3rd-party/php-zip',
		'plugins/system/sunfw/libraries/joomlashine',
		'templates/jsn_artista2_pro/html/com_finder',
		'templates/jsn_artista2_pro/html/com_users',
		'templates/jsn_artista2_pro/html/modules.php',
		'templates/jsn_artista2_pro/install',
		'templates/jsn_artista2_pro/template.checksum',
	);
}
