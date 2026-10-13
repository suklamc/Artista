<?php
/**
 * @package    Artista (Sun Framework)
 * @copyright  Copyright (C) 2026 Artriva Studios.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');

/**
 * Folder helpers with the Joomla 3 JFolder behaviour the framework was written against:
 * methods return false on failure instead of throwing. See SunFwFile for why.
 *
 * @package  Artista
 * @since    3.0.0
 */
#[\AllowDynamicProperties]
abstract class SunFwFolder
{
	/**
	 * @param   string  $path  Path to check.
	 *
	 * @return  boolean
	 */
	public static function exists($path)
	{
		return is_dir((string) $path);
	}

	/**
	 * Create a folder (and its parents). An existing folder counts as success.
	 *
	 * @param   string   $path  Folder path.
	 * @param   integer  $mode  Permissions for new folders.
	 *
	 * @return  boolean
	 */
	public static function create($path, $mode = 0755)
	{
		$path = rtrim((string) $path, '/\\');

		if ($path === '' || is_dir($path))
		{
			return true;
		}

		return @mkdir($path, $mode, true) || is_dir($path);
	}

	/**
	 * Delete a folder and everything in it.
	 *
	 * @param   string  $path  Folder path.
	 *
	 * @return  boolean
	 */
	public static function delete($path)
	{
		$path = rtrim((string) $path, '/\\');

		// Refuse to delete obviously wrong targets.
		if ($path === '' || !is_dir($path) || realpath($path) === realpath(JPATH_ROOT))
		{
			return false;
		}

		$items = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($items as $item)
		{
			if ($item->isDir() && !$item->isLink())
			{
				if (!@rmdir($item->getPathname()))
				{
					return false;
				}
			}
			elseif (!SunFwFile::delete($item->getPathname()))
			{
				return false;
			}
		}

		return @rmdir($path);
	}

	/**
	 * @param   string  $src   Source folder.
	 * @param   string  $dest  Destination folder.
	 *
	 * @return  boolean
	 */
	public static function move($src, $dest)
	{
		if (!is_dir($src) || file_exists($dest) || !self::create(dirname($dest)))
		{
			return false;
		}

		return @rename($src, $dest);
	}

	/**
	 * List files in a folder.
	 *
	 * @param   string   $path           Folder to read.
	 * @param   string   $filter         Regular expression the file name must match.
	 * @param   mixed    $recurse        True for all levels, or the number of levels to descend.
	 * @param   boolean  $full           Return full paths instead of names.
	 * @param   array    $exclude        Names to skip.
	 * @param   array    $excludeFilter  Regular expressions of names to skip.
	 *
	 * @return  array|false  False when the folder doesn't exist.
	 */
	public static function files($path, $filter = '.', $recurse = false, $full = false,
		$exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'), $excludeFilter = array('^\..*', '.*~'))
	{
		return self::items($path, $filter, $recurse, $full, $exclude, $excludeFilter, true);
	}

	/**
	 * List sub-folders of a folder. Same parameters as files().
	 *
	 * @return  array|false  False when the folder doesn't exist.
	 */
	public static function folders($path, $filter = '.', $recurse = false, $full = false,
		$exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'), $excludeFilter = array('^\..*'))
	{
		return self::items($path, $filter, $recurse, $full, $exclude, $excludeFilter, false);
	}

	/**
	 * Shared listing logic for files() and folders().
	 */
	protected static function items($path, $filter, $recurse, $full, $exclude, $excludeFilter, $wantFiles)
	{
		$path = rtrim((string) $path, '/\\');

		if (!is_dir($path))
		{
			return false;
		}

		$skipRegex = count($excludeFilter) ? '/(' . implode('|', $excludeFilter) . ')/' : '';
		$result    = array();

		self::walk($path, $filter, $recurse, $full, (array) $exclude, $skipRegex, $wantFiles, $result);

		asort($result);

		return array_values($result);
	}

	/**
	 * Recursive worker for items().
	 */
	protected static function walk($path, $filter, $recurse, $full, $exclude, $skipRegex, $wantFiles, &$result)
	{
		$handle = @opendir($path);

		if (!$handle)
		{
			return;
		}

		while (($name = readdir($handle)) !== false)
		{
			if ($name === '.' || $name === '..' || in_array($name, $exclude, true)
				|| ($skipRegex !== '' && preg_match($skipRegex, $name)))
			{
				continue;
			}

			$fullPath = $path . '/' . $name;
			$isDir    = is_dir($fullPath);

			if ($isDir === !$wantFiles && preg_match("/$filter/", $name))
			{
				$result[] = $full ? $fullPath : $name;
			}

			if ($isDir && $recurse)
			{
				$next = is_int($recurse) ? $recurse - 1 : $recurse;

				if ($next !== 0)
				{
					self::walk($fullPath, $filter, $next, $full, $exclude, $skipRegex, $wantFiles, $result);
				}
			}
		}

		closedir($handle);
	}
}
