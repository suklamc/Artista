<?php
/**
 * @package    Artista (Sun Framework)
 * @copyright  Copyright (C) 2026 Artriva Studios.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filter\InputFilter;

/**
 * File helpers with the Joomla 3 JFile behaviour the framework was written against:
 * methods return false on failure instead of throwing.
 *
 * Joomla 6 moved the CMS filesystem classes into an optional compatibility plugin, and the
 * framework package versions throw exceptions, so the framework uses this small class instead.
 *
 * @package  Artista
 * @since    3.0.0
 */
#[\AllowDynamicProperties]
abstract class SunFwFile
{
	/**
	 * @param   string  $file  Path to check.
	 *
	 * @return  boolean
	 */
	public static function exists($file)
	{
		return is_file((string) $file);
	}

	/**
	 * @param   string  $file  Path to read.
	 *
	 * @return  string|false
	 */
	public static function read($file)
	{
		return is_file($file) ? @file_get_contents($file) : false;
	}

	/**
	 * Write a file, creating its folder if needed.
	 *
	 * @param   string   $file        Path to write.
	 * @param   string   $buffer      Contents.
	 * @param   boolean  $useStreams  Unused; kept for call compatibility.
	 *
	 * @return  boolean
	 */
	public static function write($file, $buffer, $useStreams = false)
	{
		if (!SunFwFolder::create(dirname($file)))
		{
			return false;
		}

		$written = @file_put_contents($file, $buffer) !== false;

		if ($written)
		{
			self::invalidateCache($file);
		}

		return $written;
	}

	/**
	 * Delete one file or an array of files. Missing files count as deleted.
	 *
	 * @param   string|array  $file  Path(s) to delete.
	 *
	 * @return  boolean
	 */
	public static function delete($file)
	{
		$ok = true;

		foreach ((array) $file as $path)
		{
			if (is_file($path) || is_link($path))
			{
				@chmod($path, 0777);

				if (!@unlink($path))
				{
					$ok = false;
					continue;
				}

				self::invalidateCache($path);
			}
		}

		return $ok;
	}

	/**
	 * @param   string  $src   Source path.
	 * @param   string  $dest  Destination path.
	 *
	 * @return  boolean
	 */
	public static function move($src, $dest)
	{
		if (!is_readable($src) || !SunFwFolder::create(dirname($dest)))
		{
			return false;
		}

		if (!@rename($src, $dest))
		{
			return false;
		}

		self::invalidateCache($src);
		self::invalidateCache($dest);

		return true;
	}

	/**
	 * @param   string  $src   Source path.
	 * @param   string  $dest  Destination path.
	 *
	 * @return  boolean
	 */
	public static function copy($src, $dest)
	{
		if (!is_readable($src) || !SunFwFolder::create(dirname($dest)))
		{
			return false;
		}

		return @copy($src, $dest);
	}

	/**
	 * Move an uploaded file into place after Joomla's safe-file check.
	 *
	 * @param   string  $src   The uploaded temporary file.
	 * @param   string  $dest  Destination path.
	 *
	 * @return  boolean
	 */
	public static function upload($src, $dest)
	{
		if (!is_uploaded_file($src))
		{
			return false;
		}

		$descriptor = array(
			'name'     => basename($dest),
			'tmp_name' => $src,
			'type'     => '',
			'error'    => 0,
			'size'     => filesize($src),
		);

		if (!InputFilter::isSafeFile($descriptor))
		{
			return false;
		}

		if (!SunFwFolder::create(dirname($dest)) || !@move_uploaded_file($src, $dest))
		{
			return false;
		}

		@chmod($dest, 0644);

		return true;
	}

	/**
	 * Get a file's extension.
	 *
	 * @param   string  $file  File name.
	 *
	 * @return  string
	 */
	public static function getExt($file)
	{
		$dot = strrpos($file, '.');

		return $dot === false ? '' : substr($file, $dot + 1);
	}

	/**
	 * Strip the extension from a file name.
	 *
	 * @param   string  $file  File name.
	 *
	 * @return  string
	 */
	public static function stripExt($file)
	{
		return preg_replace('#\.[^.]*$#', '', $file);
	}

	/**
	 * Make a file name safe: no path parts, no leading dots, only common characters.
	 *
	 * @param   string  $file  File name.
	 *
	 * @return  string
	 */
	public static function makeSafe($file)
	{
		$file = basename(str_replace('\\', '/', (string) $file));
		$file = preg_replace(array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#'), '', $file);

		return trim($file);
	}

	/**
	 * Clear PHP's opcode cache for a file so changed PHP files take effect.
	 *
	 * @param   string  $file  Path.
	 *
	 * @return  void
	 */
	protected static function invalidateCache($file)
	{
		if (function_exists('opcache_invalidate') && substr($file, -4) === '.php')
		{
			@opcache_invalidate($file, true);
		}
	}
}
