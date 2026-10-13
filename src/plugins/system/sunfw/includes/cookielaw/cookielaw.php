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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;

/**
 * Cookie consent class.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
#[\AllowDynamicProperties]
class SunFwCookielaw
{
	/**
	 * Instance of template administrator object
	 *
	 * @var  SunFwCookielaw
	 */
	private static $_instance;

	/**
	 * Return an instance of SunFwCookielaw class.
	 *
	 * @return  SunFwCookielaw
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new SunFwCookielaw();
		}

		return self::$instance;
	}

	/**
	 * Load Cookie EU Law
	 *
	 */
	public static function loadCookie()
	{
		$app = Factory::getApplication();
		$document = Factory::getDocument();

		if ($app->input->getCmd('tmpl') == 'component' || $document->getType() !== 'html')
		{
			return false;
		}

		$cookieLawData = SunFwSite::getInstance()->cookie_law_data;

		if (count((array) $cookieLawData) && isset($cookieLawData['enabled']) && (int) $cookieLawData['enabled'])
		{
			self::loadCookieLibrary();

			// Prepare parameters.
			$jsParamsContent = array(
				'message' => '',
				'link' => null,
				'href' => null
			);

			// Banner position.
			if (empty($cookieLawData['banner-placement']))
			{
				$cookieLawData['banner-placement'] = 'top';
			}
			elseif ($cookieLawData['banner-placement'] == 'floating')
			{
				$cookieLawData['banner-placement'] = 'bottom-right';
			}
			elseif ($cookieLawData['banner-placement'] == 'floating-left')
			{
				$cookieLawData['banner-placement'] = 'bottom-left';
			}

			// Text for accept button.
			if (!empty($cookieLawData['accept-button-text']))
			{
				$jsParamsContent['dismiss'] = $cookieLawData['accept-button-text'];
			}

			// Text for read more button.
			if (!empty($cookieLawData['read-more-button-text']))
			{
				$jsParamsContent['link'] = $cookieLawData['read-more-button-text'];
			}

			// The message...
			if (isset($cookieLawData['message_type']) && $cookieLawData['message_type'] == 'article' && !empty($cookieLawData['article']))
			{
				// Get the selected article.
				$item = explode(':', $cookieLawData['article']);
				$item = SunFwSiteHelper::getArticle((int) array_pop($item));

				// Add router helpers.
				$item->slug = $item->alias ? ( $item->id . ':' . $item->alias ) : $item->id;
				$item->catslug = $item->category_alias ? ( $item->catid . ':' . $item->category_alias ) : $item->catid;
				$item->parent_slug = $item->parent_alias ? ( $item->parent_id . ':' . $item->parent_alias ) : $item->parent_id;

				// No link for ROOT category.
				if ($item->parent_alias == 'root')
				{
					$item->parent_slug = null;
				}

				// Get read more link.
				$item->readmore_link = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));

				// Process the content plugins.
				PluginHelper::importPlugin('content');

				$item->text = $item->introtext;

				Factory::getApplication()->triggerEvent('onContentPrepare',
					array(
						'com_content.article',
						&$item,
						&$item->params,
						0
					));

				// Set message.
				$jsParamsContent['message'] = "<!-- Cookie Consent Message :: Start -->{$item->text}<!-- Cookie Consent Message :: End -->";

				// Link for read more button.
				if (!empty($item->fulltext))
				{
					$jsParamsContent['href'] = $item->readmore_link;
				}
				elseif (!empty($cookieLawData['cookie-policy-link']))
				{
					$jsParamsContent['href'] = $cookieLawData['cookie-policy-link'];
				}
				else
				{
					$jsParamsContent['link'] = null;
				}
			}
			elseif (( empty($cookieLawData['message_type']) || $cookieLawData['message_type'] == 'text' ) &&
				 !empty($cookieLawData['message']))
			{
				$jsParamsContent['message'] = $cookieLawData['message'];

				// Link for read more button.
				if (!empty($cookieLawData['cookie-policy-link']))
				{
					$jsParamsContent['href'] = $cookieLawData['cookie-policy-link'];
				}
				else
				{
					$jsParamsContent['link'] = null;
				}
			}
			else
			{
				$jsParamsContent['link'] = null;
			}

			// Prepare theme.
			if (empty($cookieLawData['style']))
			{
				$cookieLawData['style'] = 'dark';
			}

			$document->addStylesheet(
				Uri::root(true) . "/plugins/system/sunfw/assets/3rd-party/cookieconsent/styles/{$cookieLawData['style']}.css");

			// Print inline script to initialize Cookie Consent.
			$document->addScriptDeclaration(
				';window.addEventListener("load", function() {
					window.cookieconsent.initialise({
						position: "' . $cookieLawData['banner-placement'] . '",
						content: ' . json_encode($jsParamsContent) .
					 ',
						elements: {
							messagelink: \'<div id="cookieconsent:desc" class="cc-message">{{message}}</div>' .
					 ( empty($jsParamsContent['link']) ? '' : '<ul><li><a aria-label="learn more about cookies" role=button tabindex="0" class="cc-link" href\' + \'=\' + \'"{{href}}" target="_blank">{{link}}</a></li></ul>' ) . '\'
						}
					});
					setTimeout(function() {
						var btn = document.querySelector(".cc-compliance .cc-btn.cc-dismiss");
						if (btn) {
							btn.addEventListener("click", function() {
								setTimeout(function() {
									window.location.reload();
								}, 100);
							});
						}
					}, 100);
				});
			');
		}
	}

	/**
	 * Load Cookie Consent library.
	 */
	public static function loadCookieLibrary()
	{
		Factory::getDocument()->addScript(Uri::root(true) . '/plugins/system/sunfw/assets/3rd-party/cookieconsent/cookieconsent.min.js');
	}
}
