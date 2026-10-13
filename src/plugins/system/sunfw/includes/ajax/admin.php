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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;


/**
 * Class that handles general template admin Ajax requests.
 *
 * @package  SUN Framework
 * @since    1.3.0
 */
#[\AllowDynamicProperties]
class SunFwAjaxAdmin extends SunFwAjax
{
	/**
	 * Get components for rendering template admin screen.
	 *
	 * @return  void
	 */
	public function getAction()
	{
		// Get the first template style ID.
		$this->dbo->setQuery(
			'SELECT id FROM #__template_styles WHERE template = ' . $this->dbo->quote($this->templateName)
			. 'ORDER BY home DESC, id ASC',
			0,
			1
		);

		$firstStyle = $this->dbo->loadResult();

		// Get root URL.
		$root = Uri::root();

		// Get template style data.
		$this->dbo->setQuery(
			'SELECT COUNT(id) FROM #__sunfw_styles WHERE template = ' . $this->dbo->quote($this->templateName)
		);

		$hasData = $this->dbo->loadResult();

		// Get authorization token.
		$params = SunFwHelper::getExtensionParams('template', $this->template['name']);

		if (empty($params['token']))
		{
			$params = SunFwHelper::getExtensionParams('plugin', 'sunfw', 'system');

			if (!empty($params['token']))
			{
				SunFwHelper::updateExtensionParams(array(
					'token' => $params['token']
				), 'template', $this->template['name']);
			}
		}

		// Get active languages if multi-language is in use.
		$lang = array();

		if (
			Multilanguage::isEnabled()
			&&
			method_exists('JLanguageHelper', 'getInstalledLanguages')
		)
		{
			$lang = LanguageHelper::getInstalledLanguages(0);

			// Get URL Language Code.
			$dbo = Factory::getDbo();

			$qry = $dbo->getQuery(true)
				->select('lang_code, sef')
				->from('#__languages')
				->where('lang_code IN (' . implode(', ', array_map(array(
				$dbo,
				'quote'
			), array_keys($lang))) . ')');

			$dbo->setQuery($qry);

			$lang = $dbo->loadAssocList('lang_code', 'sef');
		}

		// Get registered username.
		$params = SunFwHelper::getExtensionParams('template', $this->template['name']);

		// Define base Ajax URL.
		$ajaxBase = "{$root}administrator/index.php?option=com_ajax&format=json&group=system&plugin=sunfw"
			. "&template_name={$this->templateName}&style_id={$this->styleID}&" . Session::getFormToken() . '=1';

		// Documentation now lives with the project on GitHub.
		$this->documentationLink        = ARTISTA_REPO_URL;
		$this->gettingStartedSeriesLink = ARTISTA_REPO_URL;

		// Get plugin params.
		$pluginParams = SunFwHelper::getExtensionParams('plugin', 'sunfw', 'system');

		// Get license.
		$license = $this->getLicense();

		$this->setResponse(array(
			'bootstrapVersion' => SunFwSiteBootstrap::getVersion($this->templateName),
			'maxLoading' => isset($pluginParams['max_loading']) ? (int) $pluginParams['max_loading'] : 0,
			'firstStyle' => $firstStyle,
			'namespace' => md5($root . '|' . $this->templateName . '|' . $this->styleID),
			'hasData' => (int) $hasData,
			'license' => $license,
			'token' => '',
			'lang' => $lang,
			'urls' => array(
				'root' => $root,
				'plugin' => "{$root}plugins/system/sunfw",
				'ajaxBase' => $ajaxBase,
				'configManual' => $this->documentationLink,
				'gettingStartedSeriesLink' => $this->gettingStartedSeriesLink,
				'savePluginParams' => '&action=savePluginParams',
				// Chosen ships with the framework (Joomla 6 no longer includes it).
				'chosen' => array(
					'css' => 'plugins/system/sunfw/assets/3rd-party/chosen/chosen.css',
					'js'  => 'plugins/system/sunfw/assets/3rd-party/chosen/chosen.jquery.min.js'
				)
			),
			'components' => array(
				array(
					'id' => 'alerts',
					'name' => 'ComponentAlerts',
					'data' => $this->getComponentAlerts()
				),
				array(
					'id' => 'header',
					'name' => 'ComponentHeader',
					'data' => $this->getComponentHeader()
				),
				array(
					'id' => 'body',
					'name' => 'ComponentBody',
					'data' => $this->getComponentBody()
				),
				array(
					'id' => 'footer',
					'name' => 'ComponentFooter',
					'data' => $this->getComponentFooter()
				),
				array(
					'id' => 'update',
					'name' => 'ComponentUpdate',
					'data' => $this->getComponentUpdate()
				)
			),
			'textMapping' => array(
				'ok' => Text::_('SUNFW_OK'),
				'cancel' => Text::_('SUNFW_CANCEL'),
				'close' => Text::_('SUNFW_CLOSE'),
				'save' => Text::_('SUNFW_SAVE'),
				'undo' => Text::_('SUNFW_UNDO'),
				'redo' => Text::_('SUNFW_REDO'),
				'loading' => Text::_('SUNFW_LOADING'),
				'yes' => Text::_('SUNFW_YES'),
				'no' => Text::_('SUNFW_NO'),
				'left' => Text::_('SUNFW_LEFT'),
				'center' => Text::_('SUNFW_CENTER'),
				'right' => Text::_('SUNFW_RIGHT'),
				'top' => Text::_('SUNFW_TOP'),
				'middle' => Text::_('SUNFW_MIDDLE'),
				'bottom' => Text::_('SUNFW_BOTTOM'),
				'none' => Text::_('SUNFW_NONE'),
				'text' => Text::_('SUNFW_TEXT'),
				'link' => Text::_('SUNFW_LINK'),
				'icon' => Text::_('SUNFW_ICON'),
				'name' => Text::_('SUNFW_NAME'),
				'normal' => Text::_('SUNFW_NORMAL'),
				'select' => Text::_('SUNFW_SELECT'),
				'effect' => Text::_('SUNFW_EFFECT'),
				'push' => Text::_('SUNFW_PUSH'),
				'slide' => Text::_('SUNFW_SLIDE'),
				'title' => Text::_('SUNFW_TITLE'),
				'general' => Text::_('SUNFW_GENERAL'),
				'effects' => Text::_('SUNFW_EFFECTS'),
				'menu' => Text::_('SUNFW_MENU'),
				'image' => Text::_('SUNFW_IMAGE'),
				'continue' => Text::_('SUNFW_CONTINUE'),
				'all' => Text::_('SUNFW_ALL'),
				'fading' => Text::_('SUNFW_FADING'),
				'module' => Text::_('SUNFW_MODULE'),
				'width' => Text::_('SUNFW_WIDTH'),
				'height' => Text::_('SUNFW_HEIGHT'),
				'back' => Text::_('SUNFW_BACK'),
				'style' => Text::_('SUNFW_STYLE'),
				'skip' => Text::_('SUNFW_SKIP'),
				'color' => Text::_('SUNFW_COLOR'),
				'border' => Text::_('SUNFW_BORDER'),
				'standard' => Text::_('SUNFW_STANDARD'),
				'google' => Text::_('SUNFW_GOOGLE'),
				'custom' => Text::_('SUNFW_CUSTOM'),
				'margin' => Text::_('SUNFW_MARGIN'),
				'padding' => Text::_('SUNFW_PADDING'),
				'icons' => Text::_('SUNFW_ICONS'),
				'language' => Text::_('SUNFW_LANGUAGE'),
				'section' => Text::_('SUNFW_SECTION'),
				'row' => Text::_('SUNFW_ROW'),
				'column' => Text::_('SUNFW_COLUMN'),
				'block' => Text::_('SUNFW_BLOCK'),
				'item' => Text::_('SUNFW_ITEM'),
				'logo' => Text::_('SUNFW_LOGO'),
				'mobile' => Text::_('SUNFW_MOBILE'),
				'desktop' => Text::_('SUNFW_DESKTOP'),
				'laptop' => Text::_('SUNFW_LATOP'),
				'tablet' => Text::_('SUNFW_TABLET'),
				'smartphone' => Text::_('SUNFW_SMARTPHONE'),
				'visibility' => Text::_('SUNFW_VISIBLE'),

				'please-wait' => Text::_('SUNFW_PLEASE_WAIT'),
				'saving-data' => Text::_('SUNFW_SAVING_DATA'),
				'save-success' => Text::_('SUNFW_SAVE_DATA_SUCCESS'),
				'save-all' => Text::_('SUNFW_SAVE_ALL'),
				'no-settings' => Text::_('SUNFW_NO_SETTINGS'),
				'edit-item' => Text::_('SUNFW_EDIT_ITEM'),
				'edit-icon' => Text::_('SUNFW_EDIT_ICON'),
				'text-color' => Text::_('SUNFW_TEXT_COLOR'),
				'text-hover-color' => Text::_('SUNFW_TEXT_TEXT_HOVER_COLOR'),
				'custom-classes' => Text::_('SUNFW_CUSTOME_CLASSES'),
				'search-for' => Text::_('SUNFW_SEARCH_FOR'),
				'file-manager' => Text::_('SUNFW_FILE_MANAGER'),
				'choose-image' => Text::_('SUNFW_CHOOSE_IMAGE'),
				'joomla-module' => Text::_('SUNFW_SELECT_JOOMLA_MODULE'),
				'module-style' => Text::_('SUNFW_SELECT_MODULE_STYLE'),
				'alt-text' => Text::_('SUNFW_ALT_TEXT'),
				'start-editing' => Text::_('SUNFW_START_EDITING'),
				'click-to-select' => Text::_('SUNFW_CLICK_TO_SELECT'),
				'title-cannot-be-blank' => Text::_('SUNFW_TITLE_CANNOT_BE_BLANK'),
				'settings-not-available' => Text::_('SUNFW_SETTINGS_NOT_AVAILABLE'),
				'powered-by' => Text::_('SUNFW_POWERED_BY'),
				'update-to' => Text::_('SUNFW_UPDATE_TO'),
				'learn-more' => Text::_('SUNFW_LEARN_MORE'),
				'download-it' => Text::_('SUNFW_DOWNLOAD_IT'),
				'update-product' => Text::_('SUNFW_UPDATE_PRODUCT'),
				'see-tutorial' => Text::_('SUNFW_SEE_TUTORIAL'),

				'get-started' => Text::_('SUNFW_GET_STARTED'),
				'install-sample-data' => Text::_('SUNFW_INSTALL_SAMPLE_DATA'),
				'install-sample-data-intro' => Text::_('SUNFW_INSTALL_SAMPLE_DATA_INTRO'),
				'read-documentation' => Text::_('SUNFW_READ_DOCUMENTATION'),
				'read-documentation-intro' => Text::_('SUNFW_READ_DOCUMENTATION_INTRO'),
				'hide-get-started-message' => Text::_('SUNFW_HIDE_GET_STARTED_MESSAGE'),
				'got-it' => Text::_('SUNFW_GOT_IT'),
				'save-settings' => Text::_('SUNFW_SAVE_SETTINGS'),
				'only-alphanumeric-characters-are-supported' => Text::_('SUNFW_ONLY_ALPHANUMERIC_CHARACTERS_ARE_SUPPORTED'),

				'AwEwCtWR' => Text::_('SUNFW_PRIVACY_SETTINGS_TITLE'),
				'Vw6nEmbC' => Text::_('SUNFW_PRIVACY_SETTINGS_CONTENT'),
				'm8Z3DzfB' => Text::_('SUNFW_PRIVACY_SETTINGS_ACCEPT'),
				'HNeEdv2Z' => Text::_('SUNFW_PRIVACY_SETTINGS_DECLINE'),
				'uVDbHsJD' => Text::_('SUNFW_PRIVACY_SETTINGS_INTRO'),

				'module-position' => Text::_('SUNFW_MODULE_POSITION'),
				'create-position' => Text::_('SUNFW_CREATE_POSITION'),
				'position-name' => Text::_('SUNFW_POSITION_NAME'),

				'filter-menu-items-by-language' => Text::_('SUNFW_FILTER_MENU_ITEM_BY_LANGUAGE'),
				'select-menu-item' => Text::_('SUNFW_SELECT_MENU_ITEM'),
				'edit-menu-item' => Text::_('SUNFW_EDIT_MENU_ITEM'),

				'select-module' => Text::_('SUNFW_SELECT_MODULE'),
				'choose-module' => Text::_('SUNFW_CHOOSE_MODULE'),
				'configure-module' => Text::_('SUNFW_CONFIGURE_MODULE'),
				'select-module-style' => Text::_('SUNFW_SELECT_MODULE_STYLE'),

				'html-content' => Text::_('SUNFW_HTML_CONTENT'),
				'set-html-content' => Text::_('SUNFW_SET_HTML_CONTENT'),

				'background-color' => Text::_('SUNFW_BACKGROUND_COLOR'),
				'background-hover-color' => Text::_('SUNFW_BACKGROUND_HOVER_COLOR'),
				'background-image' => Text::_('SUNFW_BACKGROUND_IMAGE'),
				'background-image-settings' => Text::_('SUNFW_BACKGROUND_IMAGE_SETTINGS'),
				'background-repeat' => Text::_('SUNFW_BACKGROUND_REPEAT'),
				'background-size' => Text::_('SUNFW_BACKGROUND_SIZE'),
				'background-attachment' => Text::_('SUNFW_BACKGROUND_ATTACHMENT'),
				'background-position' => Text::_('SUNFW_BACKGROUND_POSITION'),

				'border-settings' => Text::_('SUNFW_BORDER_SETTINGS'),
				'border-width' => Text::_('SUNFW_BORDER_WIDTH'),
				'border-style' => Text::_('SUNFW_BORDER_STYLE'),
				'border-color' => Text::_('SUNFW_BORDER_COLOR'),
				'border-top' => Text::_('SUNFW_BORDER_TOP'),
				'border-right' => Text::_('SUNFW_BORDER_RIGHT'),
				'border-bottom' => Text::_('SUNFW_BORDER_BOTTOM'),
				'border-left' => Text::_('SUNFW_BORDER_LEFT'),
				'border-color-hover' => Text::_('SUNFW_BORDER_COLOR_HOVER'),

				'font-type' => Text::_('SUNFW_FONT_TYPE'),
				'font-family' => Text::_('SUNFW_FONT_FAMILY'),
				'google-font-selector' => Text::_('SUNFW_GOOGLE_FONT_SELECTOR'),
				'google-font-categories' => Text::_('SUNFW_GOOGLE_FONT_CATEGORIES'),
				'google-font-subsets' => Text::_('SUNFW_GOOGLE_FONT_SUBSETS'),
				'google-font-total' => Text::_('SUNFW_GOOGLE_FONT_TOTAL'),
				'google-font-search' => Text::_('SUNFW_GOOGLE_FONT_SEARCH'),
				'google-font-variants' => Text::_('SUNFW_GOOGLE_FONT_VARIANTS'),
				'google-font-variant' => Text::_('SUNFW_GOOGLE_FONT_VARIANT'),
				'google-font-subset' => Text::_('SUNFW_GOOGLE_FONT_SUBSET'),
				'font-file' => Text::_('SUNFW_FONT_FILE'),
				'choose-custom-font' => Text::_('SUNFW_CHOOSE_CUSTOM_FONT'),

				'text-transform' => Text::_('SUNFW_TEXT_TRANSFORM'),
				'text-transform-capitalize' => Text::_('SUNFW_TEXT_TRANSFORM_CAPITALIZE'),
				'text-transform-uppercase' => Text::_('SUNFW_TEXT_TRANSFORM_UPPERCASE'),
				'text-transform-lowercase' => Text::_('SUNFW_TEXT_TRANSFORM_LOWERCASE'),

				'base-size' => Text::_('SUNFW_TEXT_BASE_SIZE'),
				'font-size' => Text::_('SUNFW_FONT_SIZE'),
				'font-style' => Text::_('SUNFW_FONT_STYLE'),
				'font-style-normal' => Text::_('SUNFW_FONT_STYLE_NORMAL'),
				'font-style-italic' => Text::_('SUNFW_FONT_STYLE_ITALIC'),
				'font-style-oblique' => Text::_('SUNFW_FONT_STYLE_OBLIQUE'),
				'font-weight' => Text::_('SUNFW_FONT_WEIGHT'),
				'font-weight-bold' => Text::_('SUNFW_FONT_WEIGHT_BOLD'),
				'line-height' => Text::_('SUNFW_LINE_HEIGHT'),
				'letter-spacing' => Text::_('SUNFW_TEXT_LETTER_SPACING'),

				'box-shadow' => Text::_('SUNFW_BOX_SHADOW'),
				'box-shadow-settings' => Text::_('SUNFW_BOX_SHADOW_SETTINGS'),
				'box-shadow-h-shadow' => Text::_('SUNFW_BOX_SHADOW_H_SHADOW'),
				'box-shadow-v-shadow' => Text::_('SUNFW_BOX_SHADOW_V_SHADOW'),
				'box-shadow-blur' => Text::_('SUNFW_BOX_SHADOW_BLUR'),
				'box-shadow-spread' => Text::_('SUNFW_BOX_SHADOW_SPREAD'),
				'box-shadow-color' => Text::_('SUNFW_BOX_SHADOW_COLOR'),
				'box-shadow-inset' => Text::_('SUNFW_BOX_SHADOW_INSET'),
				'box-shadow-opacity' => Text::_('SUNFW_BOX_SHADOW_OPACITY'),

				'text-shadow' => Text::_('SUNFW_TEXT_TEXT_SHADOW'),
				'text-shadow-settings' => Text::_('SUNFW_TEXT_SHADOW_SETTINGS'),

				'margin-settings' => Text::_('SUNFW_MARGIN_SETTINGS'),
				'margin-top' => Text::_('SUNFW_MARGIN_TOP'),
				'margin-right' => Text::_('SUNFW_MARGIN_RIGHT'),
				'margin-bottom' => Text::_('SUNFW_MARGIN_BOTTOM'),
				'margin-left' => Text::_('SUNFW_MARGIN_LEFT'),

				'padding-settings' => Text::_('SUNFW_PADDING_SETTINGS'),
				'padding-top' => Text::_('SUNFW_PADDING_TOP'),
				'padding-right' => Text::_('SUNFW_PADDING_RIGHT'),
				'padding-bottom' => Text::_('SUNFW_PADDING_BOTTOM'),
				'padding-left' => Text::_('SUNFW_PADDING_LEFT'),

				'border-radius' => Text::_('SUNFW_TEXT_BORDER_RADIUS'),
				'border-radius-settings' => Text::_('SUNFW_BORDER_RADIUS_SETTINGS'),
				'border-radius-top' => Text::_('SUNFW_BORDER_RADIUS_TOP'),
				'border-radius-right' => Text::_('SUNFW_BORDER_RADIUS_RIGHT'),
				'border-radius-bottom' => Text::_('SUNFW_BORDER_RADIUS_BOTTOM'),
				'border-radius-left' => Text::_('SUNFW_BORDER_RADIUS_LEFT'),

				'social-icons' => Text::_('SUNFW_SOCIAL_ICONS'),
				'icon-color' => Text::_('SUNFW_ICON_COLOR'),
				'icon-hover-color' => Text::_('SUNFW_ICON_HOVER_COLOR'),
				'icon-size' => Text::_('SUNFW_ICON_SIZE'),
				'link-target' => Text::_('SUNFW_LINK_TARGET'),
				'add-social-icon' => Text::_('SUNFW_SOCIAL_ICON_ADD'),
				'social-network' => Text::_('SUNFW_SOCIAL_NETWORK'),
				'social-icon' => Text::_('SUNFW_SOCIAL_ICON'),
				'profile-link' => Text::_('SUNFW_SOCIAL_PROFILE_LINK'),

				'show-module-title' => Text::_('SUNFW_TEXT_SHOW_MODULE_TITLE'),
				'social-icon-setting' => Text::_('SUNFW_SOCIAL_ICONS_SETTING'),
				'social-title' => Text::_('SUNFW_SOCIAL_TITLE'),

				'color-picker-main-color' => Text::_('SUNFW_MAIN_COLOR'),
				'color-picker-sub-color' => Text::_('SUNFW_SUB_COLOR'),
				'color-picker-custom-color' => Text::_('SUNFW_CUSTOM_COLOR'),

				'article-selector' => Text::_('SUNFW_ARTICLE_PICKER'),
				'edit-article' => Text::_('SUNFW_EDIT_ARTICLE'),
				'select-article' => Text::_('SUNFW_SELECT_ARTICLE'),
				'choose-article' => Text::_('SUNFW_CHOOSE_ARTICLE'),

				'JxPmqKxB-Gjfd4Sxv' => Text::_('SUNFW_USER_VERIFICATION_TITLE'),
				'yUsBHMth' => Text::_('SUNFW_SELECT_EXISTING_ACCOUNT'),
				'HYffw0aK' => Text::_('SUNFW_REGISTER_NEW_ACCOUNT'),
				'JxPmqKxB-td6Tz2Gb' => Text::_('SUNFW_USER_VERIFICATION_MESSAGE'),
				'JxPmqKxB-HYffw0aK' => Text::_('SUNFW_USER_VERIFICATION_REGISTER'),
				'JxPmqKxB-Qe77Z23Y' => Text::_('SUNFW_USER_VERIFICATION_PRIMARY_BUTTON'),
				'JxPmqKxB-UBp6A5Q9' => Text::_('SUNFW_USER_VERIFICATION_SECONDARY_BUTTON'),

				'NBp9UaGE-Gjfd4Sxv' => Text::_('SUNFW_PRODUCT_VERIFICATION_TITLE'),
				'NBp9UaGE-wvJcKMWJ' => Text::_('SUNFW_PRODUCT_VERIFICATION_ALL_DONE'),
				'mPSFCX47' => Text::_('SUNFW_YOUR_WEBSITE_IS_REGISTERED_TO_FOLLOWING_PRODUCT_LICENSE'),
				'NgZ6fjv2' => Text::_('SUNFW_PRODUCT_VERIFICATION_THANK_YOU'),
				'Az99s9KS' => Text::_('SUNFW_PRODUCT_VERIFICATION_LETS_GET_STARTED'),
				'mQXvSGWx' => Text::_('SUNFW_PRODUCT_VERIFICATION_FREE_EDITION'),
				'NBp9UaGE-got-it' => Text::_('SUNFW_PRODUCT_VERIFICATION_GOT_IT'),

				'K53G78yE-Gjfd4Sxv' => Text::_('SUNFW_TIME_TO_GO_PRO_TITLE'),
				'K53G78yE-td6Tz2Gb' => Text::_('SUNFW_TIME_TO_GO_PRO_MESSAGE'),
				'KW6yu9fy-td6Tz2Gb' => Text::_('SUNFW_TRIAL_EXPIRED_MESSAGE'),
				'K53G78yE-Qe77Z23Y' => Text::_('SUNFW_TIME_TO_GO_PRO_PRIMARY_BUTTON'),
				'K53G78yE-UBp6A5Q9' => Text::_('SUNFW_TIME_TO_GO_PRO_SECONDARY_BUTTON'),
				'K53G78yE-Z23xaNm4' => Text::_('SUNFW_TIME_TO_GO_PRO_MAYBE_LATER_BUTTON'),
				'K53G78yE-xtJ1Gs8h' => Text::_('SUNFW_TIME_TO_GO_PRO_NO_THANKS_BUTTON'),

				'extra-menu-options-Gjfd4Sxv' => Text::_('SUNFW_MENU_OPTIONS_LIMITATION_TITLE'),
				'extra-menu-options-td6Tz2Gb' => Text::_('SUNFW_MENU_OPTIONS_LIMITATION_MESSAGE'),

				'extra-article-options-Gjfd4Sxv' => Text::_('SUNFW_ARTICLE_OPTIONS_LIMITATION_TITLE'),
				'extra-article-options-td6Tz2Gb' => Text::_('SUNFW_ARTICLE_OPTIONS_LIMITATION_MESSAGE'),

				'KBwZeTmw-Gjfd4Sxv' => Text::_('SUNFW_THANK_YOU_TRIAL_EDITION_TITLE'),
				'KBwZeTmw-td6Tz2Gb' => Text::_('SUNFW_THANK_YOU_TRIAL_EDITION_MESSAGE'),
				'KBwZeTmw-NdnhAJdA' => Text::_('SUNFW_THANK_YOU_TRIAL_EDITION_BUTTON_TEXT'),

				'ct4pYJcT-Gjfd4Sxv' => Text::_('SUNFW_SORRY_TRIAL_EXPIRED_TITLE'),
				'ct4pYJcT-td6Tz2Gb' => Text::_('SUNFW_LIGHTCART_API_ERROR_12'),
				'ct4pYJcT-NdnhAJdA' => Text::_('SUNFW_SORRY_TRIAL_EDITION_BUTTON_TEXT'),

				'xpz0tWTT' => Text::_('SUNFW_DISCOUNT_OFFER_EXPIRED'),
				'UccWXSfV' => empty($params['clickedPurchase']) ? null : $params['clickedPurchase'],
				'qUTvD2zQ' => empty($params['currentDiscount']) ? null : $params['currentDiscount'],

			    'read-getting-started-series' => Text::_('SUNFW_READ_GETTING_STARTED_SERIES'),
			    'read-getting-started-series-intro' => Text::_('SUNFW_READ_GETTING_STARTED_SERIES_INFO'),
			    'read-guide' => Text::_('SUNFW_READ_GUIDE'),
			    'video-guide' => Text::_('SUNFW_VIDEO_GUIDE'),
			    'getting-started' => Text::_('SUNFW_GETTING_STARTED'),
			    'get-help' => Text::_('SUNFW_GET_HELP')
			)
		));
	}

	/**
	 * Method to get alert data.
	 *
	 * @return  array
	 */
	protected function getComponentAlerts()
	{
		// Allow 3rd-party plugins to add their own alerts.
		$items = array_merge(array(), $this->app->triggerEvent('SunFwGetComponentAlerts'));

		$data = array(
			'items' => $items
		);

		return $data;
	}

	/**
	 * Method to get data for header nav bar.
	 *
	 * @return  array
	 */
	protected function getComponentHeader()
	{
		// Get site URL.
		$root = Uri::root(true);

		// Define header nav bar.
		$data = array(
			'logo' => array(
				'link' => Uri::root(),
				'image' => "{$root}/plugins/system/sunfw/assets/images/logo.png",
				'title' => htmlspecialchars(
					$this->app->getCfg('sitename'), ENT_QUOTES, 'UTF-8'
				)
			),
			'menu' => array(
				array(
					'href' => '#',
					'icon' => 'database',
					'type' => 'trigger-other',
					'title' => Text::_('SUNFW_MAINTENANCE_TAB'),
					'target' => '#maintenance'
				),
				array(
					'href' => '#',
					'icon' => 'info',
					'type' => 'trigger-other',
					'title' => Text::_('SUNFW_ABOUT'),
					'target' => '#about'
				),
				array(
					'href' => ARTISTA_REPO_URL,
					'icon' => 'github',
					'title' => 'GitHub',
					'target' => '_blank'
				)
			),
			'textMapping' => array(
				'y37Rm2cT' => Text::sprintf('SUNFW_UPGRADE_TO_PRO_FOR_ADVANCED_FEATURES', $this->templateName),
				'zntBTB4M' => Text::_('SUNFW_YOUR_TRIAL_LICENSE_WILL_EXPIRE_IN'),
				'AVyacBDn' => Text::_('SUNFW_YOUR_TRIAL_LICENSE_IS_EXPIRED'),
				'WRJJEdEg' => Text::_('SUNFW_YOUR_PRO_LICENSE_IS_EXPIRED'),
				'BcBkJNHJ' => Text::_('SUNFW_PURCHASE_PRO'),
				'bMz6gJBY' => Text::_('SUNFW_RENEW_PRO'),
				'cQVnkpgC' => Text::_('SUNFW_RENEW_LITE'),

				'w2b97wVJ-Gjfd4Sxv' => Text::_('SUNFW_ADVANCED_FEATURES_LIMITATION_TITLE'),
				'w2b97wVJ-td6Tz2Gb' => Text::_('SUNFW_ADVANCED_FEATURES_LIMITATION_MESSAGE'),

				'w4NZZZgN-PbjM7szs' => Text::_('SUNFW_RENEW_PRO_MODAL_TITLE'),
				'w4NZZZgN-Gjfd4Sxv' => Text::_('SUNFW_RENEW_PRO_TITLE'),
				'w4NZZZgN-M3qrv0Rh' => Text::_('SUNFW_RENEW_PRO_MESSAGE_1'),
				'w4NZZZgN-sDVgQf7K' => Text::_('SUNFW_RENEW_PRO_MESSAGE_2'),
				'w4NZZZgN-NdnhAJdA' => Text::_('SUNFW_RENEW_PRO_BUTTON_TEXT')
			)
		);

		// Allow 3rd-party plugins to add their own menu items into header nav bar.
		$data = array_merge_recursive($data, $this->app->triggerEvent('SunFwGetComponentHeader'));

		return $data;
	}

	/**
	 * Method to get data for main workspace.
	 *
	 * @return  array
	 */
	protected function getComponentBody()
	{
		// Get site URL.
		$root = Uri::root(true);

		// Build URL to save a copy of the current template style.
		$saveAsCopy = "index.php?option=com_ajax&format=json&group=system&plugin=sunfw&context=admin"
			. "&action=saveAsCopy&template_name={$this->templateName}&style_id={$this->styleID}&"
			. Session::getFormToken() . '=1';

		// Get all input components.
		$inputs = array();
		$path = JPATH_ROOT . '/plugins/system/sunfw/assets/joomlashine/admin/js/inputs';

		foreach (glob("{$path}/*.js") as $input)
		{
			$inputs[substr(basename($input), 0, -3)] = $root . str_replace(
				JPATH_ROOT, '', $input
			);
		}

		// Prepare getting started content.
		$gettingStarted = null;

		if (SunFwHelper::getBrandSetting('replaceTplGettingStartedContent'))
		{
			$gettingStarted = SunFwHelper::getBrandSetting('getTplGettingStartedContent');
		}

		// Define tabs and associated editors in main workspace.
		$vd = 'v=' . SUNFW_VERSION . '&d=' . SUNFW_RELEASED_DATE;
		$data = array(
			'tabs' => array(
				array(
					'href' => '#layout',
					'title' => Text::_('SUNFW_LAYOUT_TAB'),
					'render' => array(
						'name' => 'PaneLayout',
						'data' => ( new SunFwAjaxLayout() )->getAction(true),
						'file' => "{$root}/plugins/system/sunfw/assets/joomlashine/admin/js/layout"
							. "/render.js?{$vd}"
					),
					'className' => 'toggle-pane'
				),
				array(
					'href' => '#styles',
					'title' => Text::_('SUNFW_STYLES_TAB'),
					'render' => array(
						'name' => 'PaneStyles',
						'data' => ( new SunFwAjaxStyles() )->getAction(true),
						'file' => "{$root}/plugins/system/sunfw/assets/joomlashine/admin/js/styles"
							. "/render.js?{$vd}"
					),
					'className' => 'toggle-pane'
				),
				array(
					'href' => '#mega-menu',
					'title' => Text::_('SUNFW_MEGAMENU_TAB'),
					'render' => array(
						'name' => 'PaneMegaMenu',
						'data' => ( new SunFwAjaxMegaMenu() )->getAction(true),
						'file' => "{$root}/plugins/system/sunfw/assets/joomlashine/admin/js/mega-menu"
							. "/render.js?{$vd}"
					),
					'className' => 'toggle-pane'
				),
				array(
					'href' => '#system',
					'title' => Text::_('SUNFW_SYSTEM_TAB'),
					'render' => array(
						'name' => 'PaneSystem',
						'data' => ( new SunFwAjaxSystem() )->getAction(true),
						'file' => "{$root}/plugins/system/sunfw/assets/joomlashine/admin/js/system"
							. "/render.js?{$vd}"
					),
					'className' => 'toggle-pane'
				),
				array(
					'href' => '#extras',
					'title' => Text::_('SUNFW_EXTRAS_TAB'),
					'items' => array(
						array(
							'href' => '#cookie-law',
							'title' => Text::_('SUNFW_COOKIE_LAW_TAB'),
							'render' => array(
								'name' => 'PaneCookieLaw',
								'data' => ( new SunFwAjaxCookieLaw() )->getAction(true),
								'file' => "{$root}/plugins/system/sunfw/assets/joomlashine/admin/js/cookie-law"
									. "/render.js?{$vd}"
							),
							'className' => 'toggle-pane'
						),
						array(
							'href' => '#social-share',
							'title' => Text::_('SUNFW_SOCIAL_SHARE_TAB'),
							'render' => array(
								'name' => 'PaneSocialShare',
								'data' => ( new SunFwAjaxSocialShare() )->getAction(true),
								'file' => "{$root}/plugins/system/sunfw/assets/joomlashine/admin/js/social-share"
									. "/render.js?{$vd}"
							),
							'className' => 'toggle-pane'
						),
						array(
							'href' => '#commenting',
							'title' => Text::_('SUNFW_COMMENTING_TAB'),
							'render' => array(
								'name' => 'PaneCommenting',
								'data' => ( new SunFwAjaxCommenting() )->getAction(true),
								'file' => "{$root}/plugins/system/sunfw/assets/joomlashine/admin/js/commenting"
									. "/render.js?{$vd}"
							),
							'className' => 'toggle-pane'
						),
						array(
							'href' => '#custom-404',
							'title' => Text::_('SUNFW_CUSTOM_404_TAB'),
							'render' => array(
								'name' => 'PaneCustom404',
								'data' => ( new SunFwAjaxCustom404() )->getAction(true),
								'file' => "{$root}/plugins/system/sunfw/assets/joomlashine/admin/js/custom-404"
									. "/render.js?{$vd}"
							),
							'className' => 'toggle-pane'
						),
						array(
							'href' => '#coming-soon',
							'title' => Text::_('SUNFW_COMING_SOON_TAB'),
							'render' => array(
								'name' => 'PaneComingSoon',
								'data' => ( new SunFwAjaxComingSoon() )->getAction(true),
								'file' => "{$root}/plugins/system/sunfw/assets/joomlashine/admin/js/coming-soon"
									. "/render.js?{$vd}"
							),
							'className' => 'toggle-pane'
						)
					),
					'className' => 'extras'
				),
				array(
					'href' => '#menu-assignment',
					'title' => Text::_('SUNFW_MENU_ASSIGNMENT_TAB'),
					'render' => array(
						'name' => 'PaneAssignment',
						'data' => ( new SunFwAjaxAssignment() )->getAction(true),
						'file' => "{$root}/plugins/system/sunfw/assets/joomlashine/admin/js/assignment"
							. "/render.js?{$vd}"
					),
					'className' => 'toggle-pane'
				),
				array(
					'href' => '#data',
					'title' => Text::_('SUNFW_DATA_TAB'),
					'listClass' => 'hidden',
					'items' => array(
						array(
							'href' => '#maintenance',
							'title' => Text::_('SUNFW_MAINTENANCE_TAB'),
							'render' => array(
								'name' => 'PaneMaintenance',
								'data' => ( new SunFwAjaxMaintenance() )->getAction(true),
								'file' => "{$root}/plugins/system/sunfw/assets/joomlashine/admin/js/maintenance"
									. "/render.js?{$vd}"
							),
							'className' => 'toggle-pane'
						)
					)
				),
				array(
					'href' => '#about',
					'title' => Text::_('SUNFW_ABOUT'),
					'render' => array(
						'name' => 'PaneAbout',
						'data' => ( new SunFwAjaxAbout() )->getAction(true),
						'file' => "{$root}/plugins/system/sunfw/assets/joomlashine/admin/js/about"
							. "/render.js?{$vd}"
					),
					'listClass' => 'hidden',
					'className' => 'toggle-pane'
				)
			),
			'buttons' => array(
				array(
					array(
						'id' => 'sunfw-save-all',
						'href' => '#',
						'type' => 'success',
						'icon' => 'pencil-square-o mr-1',
						'label' => Text::_('SUNFW_SAVE_ALL'),
						'disabled' => true
					),
					array(
						'type' => 'save',
						'label' => '',
						'menu' => array(
							array(
								'id' => 'sunfw-save-as-copy',
								'href' => $saveAsCopy,
								'title' => Text::_('SUNFW_SAVE_AS_COPY')
							)
						),
						'menuClass' => 'dropdown-menu-right'
					)
				),
				array(
					'href' => 'index.php?option=com_templates',
					'icon' => 'times px-2',
					'label' => Text::_('SUNFW_CLOSE'),
					'className' => 'sub-btn'
				)
			),
			'inputs' => $inputs,
			'gettingStarted' => $gettingStarted,
			'textMapping' => array(
				'TQRUYEYz-Gjfd4Sxv' => Text::_('SUNFW_TEMPLATE_STYLE_LIMITATION_TITLE'),
				'TQRUYEYz-td6Tz2Gb' => Text::_('SUNFW_TEMPLATE_STYLE_LIMITATION_MESSAGE'),

				'XFnHMC0g-Gjfd4Sxv' => Text::_('SUNFW_SAMPLE_DATA_LIMITATION_TITLE'),
				'XFnHMC0g-td6Tz2Gb' => Text::_('SUNFW_SAMPLE_DATA_LIMITATION_MESSAGE'),

				'pane-layout-Gjfd4Sxv' => Text::_('SUNFW_PANE_LAYOUT_LIMITATION_TITLE'),
				'pane-layout-td6Tz2Gb' => Text::_('SUNFW_PANE_LAYOUT_LIMITATION_MESSAGE'),

				'pane-layout-custom-Gjfd4Sxv' => Text::_('SUNFW_PANE_CUSTOM_LAYOUT_LIMITATION_TITLE'),
				'pane-layout-custom-td6Tz2Gb' => Text::_('SUNFW_PANE_CUSTOM_LAYOUT_LIMITATION_MESSAGE'),

				'pane-styles-Gjfd4Sxv' => Text::_('SUNFW_PANE_STYLES_LIMITATION_TITLE'),
				'pane-styles-td6Tz2Gb' => Text::_('SUNFW_PANE_STYLES_LIMITATION_MESSAGE'),

				'pane-mega-menu-Gjfd4Sxv' => Text::_('SUNFW_PANE_MEGA_MENU_LIMITATION_TITLE'),
				'pane-mega-menu-td6Tz2Gb' => Text::_('SUNFW_PANE_MEGA_MENU_LIMITATION_MESSAGE'),

				'pane-cookie-law-Gjfd4Sxv' => Text::_('SUNFW_PANE_COOKIE_LAW_LIMITATION_TITLE'),
				'pane-cookie-law-td6Tz2Gb' => Text::_('SUNFW_PANE_COOKIE_LAW_LIMITATION_MESSAGE'),

				'pane-social-share-Gjfd4Sxv' => Text::_('SUNFW_PANE_SOCIAL_SHARE_LIMITATION_TITLE'),
				'pane-social-share-td6Tz2Gb' => Text::_('SUNFW_PANE_SOCIAL_SHARE_LIMITATION_MESSAGE'),

				'pane-commenting-Gjfd4Sxv' => Text::_('SUNFW_PANE_COMMENTING_LIMITATION_TITLE'),
				'pane-commenting-td6Tz2Gb' => Text::_('SUNFW_PANE_COMMENTING_LIMITATION_MESSAGE'),

				'pane-custom-404-Gjfd4Sxv' => Text::_('SUNFW_PANE_CUSTOM_404_LIMITATION_TITLE'),
				'pane-custom-404-td6Tz2Gb' => Text::_('SUNFW_PANE_CUSTOM_404_LIMITATION_MESSAGE'),

				'pane-coming-soon-Gjfd4Sxv' => Text::_('SUNFW_PANE_COMING_SOON_LIMITATION_TITLE'),
				'pane-coming-soon-td6Tz2Gb' => Text::_('SUNFW_PANE_COMING_SOON_LIMITATION_MESSAGE'),

				'xrZVPjMr-Gjfd4Sxv' => Text::_('SUNFW_ADD_LAYOUT_ITEM_LIMITATION_TITLE'),
				'xrZVPjMr-td6Tz2Gb' => Text::_('SUNFW_ADD_LAYOUT_ITEM_LIMITATION_MESSAGE'),

				'h6qjVrwy-Gjfd4Sxv' => Text::_('SUNFW_ADD_LAYOUT_ITEM_LIMITATION_TITLE'),
				'h6qjVrwy-td6Tz2Gb' => Text::_('SUNFW_ADD_LAYOUT_ITEM_LIMITATION_MESSAGE'),

				'setting-responsive-support-Gjfd4Sxv' => Text::_('SUNFW_RESPONSIVE_LAYOUT_LIMITATION_TITLE'),
				'setting-responsive-support-td6Tz2Gb' => Text::_('SUNFW_RESPONSIVE_LAYOUT_LIMITATION_MESSAGE'),

				'setting-joomlashine-branding-Gjfd4Sxv' => Text::_('SUNFW_JOOMLASHINE_BRANDING_LIMITATION_TITLE'),
				'setting-joomlashine-branding-td6Tz2Gb' => Text::_('SUNFW_JOOMLASHINE_BRANDING_LIMITATION_MESSAGE'),

				'setting-assets-compression-Gjfd4Sxv' => Text::_('SUNFW_ASSETS_COMPRESSION_LIMITATION_TITLE'),
				'setting-assets-compression-td6Tz2Gb' => Text::_('SUNFW_ASSETS_COMPRESSION_LIMITATION_MESSAGE'),

				'ErWVstHA-Gjfd4Sxv' => Text::_('SUNFW_BANNER_REMOVAL_TITLE'),
				'ErWVstHA-td6Tz2Gb' => Text::_('SUNFW_BANNER_REMOVAL_MESSAGE'),

				'KW6yu9fy' => Text::_('SUNFW_TRIAL_EXPIRED'),
				'Sbrr2pJQ' => Text::_('SUNFW_PRO_BADGE_TEXT'),
				'XNqRzhzv' => Text::_('SUNFW_TRY_PRO_LINK_TEXT'),
				'Q3TX2PUM' => Text::_('SUNFW_PRO_TOOLTIP_TEXT'),

				'icon-selector' => Text::_('SUNFW_ICON_SELECTOR'),
				'all-icons' => Text::_('SUNFW_ICON_SELECTOR_ALL_ICONS'),
				'new-icons-4-7' => Text::_('SUNFW_ICON_SELECTOR_NEW_47'),
				'web-development' => Text::_('SUNFW_ICON_SELECTOR_WEB_DEVELOPMENT'),
				'accessibility' => Text::_('SUNFW_ICON_SELECTOR_ACCESSIBILITY'),
				'hand' => Text::_('SUNFW_ICON_SELECTOR_HAND'),
				'transportation' => Text::_('SUNFW_ICON_SELECTOR_TRANSPORTATION'),
				'gender' => Text::_('SUNFW_ICON_SELECTOR_GENDER'),
				'file-type' => Text::_('SUNFW_ICON_SELECTOR_FILE_TYPE'),
				'spinner' => Text::_('SUNFW_ICON_SELECTOR_SPINNER'),
				'form-control' => Text::_('SUNFW_ICON_SELECTOR_FORM_CONTROL'),
				'payment' => Text::_('SUNFW_ICON_SELECTOR_PAYMENT'),
				'chart' => Text::_('SUNFW_ICON_SELECTOR_CHART'),
				'currency' => Text::_('SUNFW_ICON_SELECTOR_CURRENCY'),
				'text-editor' => Text::_('SUNFW_ICON_SELECTOR_TEXT_EDITOR'),
				'directional' => Text::_('SUNFW_ICON_SELECTOR_DIRECTIONAL'),
				'video-player' => Text::_('SUNFW_ICON_SELECTOR_VIDEO_PLAYER'),
				'brand' => Text::_('SUNFW_ICON_SELECTOR_BRAND'),
				'medical' => Text::_('SUNFW_ICON_SELECTOR_MEDICAL'),

				'settings-live-chat-popup' => Text::_('SUNFW_LIVE_CHAT_SETTINGS_ENABLE_POPUP'),
				'settings-live-chat-popup-hint' => Text::_('SUNFW_LIVE_CHAT_SETTINGS_ENABLE_POPUP_HINT')
			)
		);

		// Check if the Custom 404 feature is supported by the current template.
		$xml = SunFwHelper::getManifest($this->templateName);

		if (
			!$xml
			||
			!($xml = current($xml->xpath('//feature[@name="custom_404"]')))
			||
			(string) $xml['enabled'] !== 'yes'
		)
		{
			foreach ($data['tabs'] as $i => $tab)
			{
				if ($tab['href'] === '#extras')
				{
					foreach ($tab['items'] as $j => $sub)
					{
						if ($sub['href'] === '#custom-404')
						{
							unset($data['tabs'][$i]['items'][$j]);

							break 2;
						}
					}
				}
			}
		}

		// Check if the Custom Coming Soon feature is supported by the current template.
		$xml = SunFwHelper::getManifest($this->templateName);

		if (
			!$xml
			||
			!($xml = current($xml->xpath('//feature[@name="coming_soon"]')))
			||
			(string) $xml['enabled'] !== 'yes'
		)
		{
			foreach ($data['tabs'] as $i => $tab)
			{
				if ($tab['href'] === '#extras')
				{
					foreach ($tab['items'] as $j => $sub)
					{
						if ($sub['href'] === '#coming-soon')
						{
							unset($data['tabs'][$i]['items'][$j]);

							break 2;
						}
					}
				}
			}
		}

		// Allow 3rd-party plugins to add their own tabs into main workspace.
		$data = array_merge_recursive($data, $this->app->triggerEvent('SunFwGetComponentBody'));

		return $data;
	}

	/**
	 * Method to get data for footer info bar.
	 *
	 * @return  array
	 */
	protected function getComponentFooter()
	{
		// Prepare footer content.
		if (SunFwHelper::getBrandSetting('replaceTplFooterContent'))
		{
			$otherFooter = SunFwHelper::getBrandSetting('getTplFooterContent');
		}
		else
		{
			$otherFooter = array(
				'Artista is maintained on <a href="' . ARTISTA_REPO_URL . '" target="_blank"'
				. ' rel="noopener noreferrer" class="link-normal">GitHub</a>'
			);
		}

		// Define footer info bar.
		$data = array(
			'credits' => array(
				'template' => array(
					'name' => $this->template['realName'],
					'link' => ARTISTA_REPO_URL,
					'edition' => $this->template['edition'],
					'version' => $this->template['version']
				),
				'framework' => array(
					'name' => Text::_('PLG_SYSTEM_SUNFW'),
					'link' => ARTISTA_REPO_URL,
					'version' => SUNFW_VERSION
				)
			),
			'others' => (array) $otherFooter,
			// Updates come through Joomla's own updater (System > Update > Extensions).
			'showChangelogs' => 0,
			'showUpdate' => 0
		);

		// Allow 3rd-party plugins to add their own tabs into main workspace.
		$data = array_merge_recursive($data, $this->app->triggerEvent('SunFwGetComponentFooter'));

		return $data;
	}

	/**
	 * Method to get update data.
	 *
	 * @return  array
	 */
	protected function getComponentUpdate()
	{
		// Updates are handled by Joomla's own updater, so the editor never checks for them itself.
		$data = array(
			'url' => '',
			'modalTitle' => array(
				'framework' => Text::_('SUNFW_UPDATE_FRAMEWORK_TITLE'),
				'template' => Text::_('SUNFW_UPDATE_TEMPLATE_TITLE')
			)
		);

		return $data;
	}

	/**
	 * Get license data.
	 *
	 * @return  string
	 */
	protected function getLicense()
	{
		// [artriva patch] Artriva holds a lifetime JoomlaShine licence covering all templates
		// and extensions. JoomlaShine's licence server has shut down, so the licence is reported
		// here instead of being fetched. An empty expiration date shows as "Never" in the editor.
		return array(
			'edition'         => 'PRO DEVELOPER',
			'expired'         => false,
			'expiration_date' => ''
		);
	}

	/**
	 * Save data to database
	 *
	 * @throws Exception
	 */
	public function saveStyleSettingsAction()
	{
		$styleTitle = $this->input->getString('style_title', '');
		$home = $this->input->getString('home', '');

		if (empty($styleTitle))
		{
			throw new Exception('Invalid Request');
		}

		// If style is set as default, clear current default style.
		if ($home != '0')
		{
			$query = $this->dbo->getQuery(true);

			$query->update('#__template_styles')
				->set('home = 0')
				->where('client_id = 0')
				->where('home = ' . $this->dbo->quote($home));

			// Execute query to save advanced data.
			try
			{
				$this->dbo->setQuery($query);

				if (!$this->dbo->execute())
				{
					throw new Exception($this->dbo->getErrorMsg());
				}
			}
			catch (Exception $e)
			{
				throw $e;
			}
		}

		// Save style title and assignment.
		$query = $this->dbo->getQuery(true);

		$query->update('#__template_styles')
			->set('title = ' . $this->dbo->quote($styleTitle))
			->set('home = ' . $this->dbo->quote($home))
			->where('id = ' . (int) $this->styleID);

		try
		{
			$this->dbo->setQuery($query);

			if (!$this->dbo->execute())
			{
				throw new Exception($this->dbo->getErrorMsg());
			}
		}
		catch (Exception $e)
		{
			throw $e;
		}

		$this->setResponse(array(
			'message' => Text::_('SUNFW_TEMPLATE_STYLE_SAVED_SUCCESSFULLY')
		));
	}

	/**
	 * Save data to database
	 *
	 * @throws Exception
	 */
	public function saveAsCopyAction()
	{
		// Detect disabled extension
		$extension = Table::getInstance('Extension');

		if ($extension->load(
			array(
				'enabled' => 0,
				'type' => 'template',
				'element' => $this->templateName,
				'client_id' => 0
			)))
		{
			throw new Exception(Text::_('SUNFW_ERROR_SAVE_DISABLED_TEMPLATE'));
		}

		// Load 'template_styles' table object.
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_templates/tables');

		$styleTbl = Table::getInstance('Style', 'TemplatesTable');

		$styleTbl->load(0);

		$currentStyle = SunFwHelper::getTemplateStyle($this->styleID);

		if (!count((array) $currentStyle))
		{
			throw new Exception(Text::_('SUNFW_ERROR_STYLE_IS_INVALID'));
		}

		$currentStyle = (array) $currentStyle;
		$currentStyle['id'] = 0;
		$currentStyle['home'] = 0;
		$currentStyle['title'] = $this->generateNewTitle(null, null, $currentStyle['title']);
		$currentStyle['assigned'] = '';

		if (!$styleTbl->bind($currentStyle))
		{
			throw new Exception($styleTbl->getError());
		}

		// Store the data.
		if (!$styleTbl->store())
		{
			throw new Exception($styleTbl->getError());
		}

		$currentSunFwStyle = SunFwHelper::getSunFwStyle($this->styleID);

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
				intval($styleTbl->id),
				$this->dbo->quote($this->templateName),
				$this->dbo->quote($currentSunFwStyle->layout_builder_data),
				$this->dbo->quote($currentSunFwStyle->appearance_data),
				$this->dbo->quote($currentSunFwStyle->system_data),
				$this->dbo->quote($currentSunFwStyle->mega_menu_data),
				$this->dbo->quote($currentSunFwStyle->cookie_law_data),
				$this->dbo->quote($currentSunFwStyle->social_share_data),
				$this->dbo->quote($currentSunFwStyle->commenting_data),
				$this->dbo->quote($currentSunFwStyle->custom_404_data)
			);

			$query = $this->dbo->getQuery(true)
				->insert($this->dbo->quoteName('#__sunfw_styles'))
				->columns($this->dbo->quoteName($columns))
				->values(implode(',', $values));

			$this->dbo->setQuery($query);
			$this->dbo->execute();
		}

		// Compile SCSS.
		$sufwrender = new SunFwScssrender();

		$sufwrender->compile($styleTbl->id, $this->templateName);
		$sufwrender->compile($styleTbl->id, $this->templateName, 'layout');

		$this->setResponse(array(
			'id' => $styleTbl->id
		));
	}

	/**
	 * Method to change the title.
	 *
	 * @param   integer  $category_id  The id of the category.
	 * @param   string   $alias        The alias.
	 * @param   string   $title        The title.
	 *
	 * @return  string  New title.
	 *
	 * @since   1.7.1
	 */
	protected function generateNewTitle($categoryId, $alias, $title)
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_templates/tables');

		$table = Table::getInstance('Style', 'TemplatesTable');

		while ($table->load(array(
			'title' => $title
		)))
		{
			$title = \Joomla\String\StringHelper::increment($title);
		}

		return $title;
	}

	/**
	 * Method to load JSON settings file for template admin.
	 *
	 * @return  void
	 */
	public function loadJsonFileAction()
	{
		// Get requested JSON file.
		$file = $this->input->getString('file');
		$root = Uri::root(true);

		if (!empty($root) && strpos($file, $root) === 0)
		{
			$file = substr($file, strlen($root));
		}

		if (empty($file) || !is_file($file = JPATH_ROOT . $file))
		{
			exit();
		}

		// Set response header.
		header('Content-Type: application/json');

		// Read and print JSON file content.
		echo file_get_contents($file);

		exit();
	}

}
