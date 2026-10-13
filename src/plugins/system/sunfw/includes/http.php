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
use Joomla\CMS\Http\Http;
use Joomla\CMS\Language\Text;


/**
 * Class for handling HTTP request.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
#[\AllowDynamicProperties]
class SunFwHttp
{
	/**
	 * Send HTTP request using GET method.
	 *
	 * @param   string   $link       The URL to send request to.
	 * @param   string   $cache      The number of seconds to cache request results.
	 * @param   string   $cacheFile  Path to cache file.
	 * @param   boolean  $force      Whether to send request even when cached results available?
	 *
	 * @return  mixed
	 */
	public static function get($link, $cache = 0, $cacheFile = null, $force = false)
	{
		$result = null;

		// [artriva patch] JoomlaShine's servers are gone. Don't call them (or a blanked URL);
		// return "no data" so the template admin falls back instead of failing.
		if (self::isDeadEndpoint($link))
		{
			return null;
		}

		// Get results from cache file if not expired.
		if (empty($cacheFile))
		{
			$cacheFile = Factory::getConfig()->get('tmp_path') . '/sunfw/' . md5($link);
		}

		if (!$force && is_numeric($cache) && $cache > 0)
		{
			if (SunFwFile::exists($cacheFile) && time() - filemtime($cacheFile) < $cache)
			{
				$result = file_get_contents($cacheFile);
			}
		}

		// Send request if cached results not available.
		if (empty($result))
		{
			$client = new Http();

			// [artriva patch] 15-second timeout, and fall back to the cached copy if the request fails.
			try
			{
				$result = $client->get($link, null, 15);
			}
			catch (Exception $e)
			{
				$result = null;
			}

			if ($result)
			{
				$result = $result->body;

				// Cache results.
				if (is_numeric($cache) && $cache > 0 && SunFwFolder::create(dirname($cacheFile)))
				{
					SunFwFile::write($cacheFile, $result);
				}
			}
			elseif (SunFwFile::exists($cacheFile))
			{
				$result = file_get_contents($cacheFile);

				// Update last modification time of the cache file.
				touch($cacheFile);
			}
			else
			{
				throw new Exception(Text::sprintf('SUNFW_HTTP_REQUEST_FAIL', parse_url($link, PHP_URL_HOST)));
			}
		}

		return SunFwText::parseJson($result);
	}

	/**
	 * Send HTTP request using POST method.
	 *
	 * @param   string   $link  The URL to send request to.
	 * @param   array    $data  Data to post.
	 *
	 * @return  mixed
	 */
	public static function post($link, $data = array())
	{
		// [artriva patch] Never post anything to JoomlaShine's dead servers.
		if (self::isDeadEndpoint($link))
		{
			return null;
		}

		// Send request.
		$client = new Http();
		$result = $client->post($link, $data, null, 15);

		if (!$result)
		{
			throw new Exception(Text::sprintf('SUNFW_HTTP_REQUEST_FAIL', parse_url($link, PHP_URL_HOST)));
		}

		return SunFwText::parseJson($result->body);
	}

	/**
	 * [artriva patch] True when a URL has no host (a blanked JoomlaShine constant)
	 * or points at joomlashine.com, which no longer serves the framework.
	 *
	 * @param   string  $link  The URL about to be requested.
	 *
	 * @return  boolean
	 */
	protected static function isDeadEndpoint($link)
	{
		$host = parse_url((string) $link, PHP_URL_HOST);

		if (empty($host))
		{
			return true;
		}

		$host = strtolower($host);

		return $host === 'joomlashine.com' || substr($host, -16) === '.joomlashine.com';
	}
}
