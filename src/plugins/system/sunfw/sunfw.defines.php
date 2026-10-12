<?php
/**
 * @version     $Id$
 * @package     JSNExtension
 * @subpackage  TPLFRAMEWORK2
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Define base constants for the framework
define('SUNFW_PATH', dirname(__FILE__));

define('SUNFW_PATH_INCLUDES', SUNFW_PATH . '/includes');

define('SUNFW_ID', 'tpl_sunframework');
define('SUNFW_VERSION', '2.2.33');
define('SUNFW_RELEASED_DATE', '07/09/2021');

// Define remote URL for communicating with JoomlaShine server
define('SUNFW_SUPPORT_URL', 'https://www.joomlashine.com/forum.html');
define('SUNFW_CUSTOMER_AREA', 'https://www.joomlashine.com/customer-area/licenses.html');
define('SUNFW_VERSIONING_URL', 'https://www.joomlashine.com/versioning/product_version.php');
define('SUNFW_UPGRADE_DETAILS', 'https://www.joomlashine.com/versioning/product_upgrade.php');

define('SUNFW_PLUGIN_CHANGELOG', 'https://www.joomlashine.com/joomla-templates/jsn-sunframework.html#changelog');
define('SUNFW_TEMPLATE_CHANGELOG', 'https://www.joomlashine.com/joomla-templates/%s.html#changelog');

define('SUNFW_TEMPLATE_URL', 'https://www.joomlashine.com/joomla-templates/jsn-%s.html');
define('SUNFW_DOCUMENTATION_URL', 'https://www.joomlashine.com/documentation/jsn-templates/jsn-%1$s/jsn-%2$s-configuration-manual.html');

define('SUNFW_LIGHTCART_URL', 'https://www.joomlashine.com/index.php?option=com_lightcart');
define('SUNFW_POST_CLIENT_INFORMATION_URL', SUNFW_LIGHTCART_URL . '&view=clientinfo&task=clientinfo.getclientinfo');
define('SUNFW_CHECK_TOKEN_URL', SUNFW_LIGHTCART_URL . '&view=token&task=token.verify');
define('SUNFW_GET_TOKEN_URL', SUNFW_LIGHTCART_URL . '&view=token&task=token.gettoken');
define('SUNFW_GET_BANNER_URL', SUNFW_LIGHTCART_URL . '&view=adsbanners&task=adsbanners.getBanners&tmpl=component&type=json');
define('SUNFW_GET_INFO_URL', SUNFW_LIGHTCART_URL . '&view=productapi&task=productapi.getInformation&tmpl=component&type=json');
define('SUNFW_GET_LIVE_CHAT_URL', 'https://www.joomlashine.com/livechat.php');

define('SUNFW_API_URL', SUNFW_LIGHTCART_URL . '&view=authenticationapi&tmpl=component');
define('SUNFW_GET_LICENSE_URL', SUNFW_API_URL . '&task=authenticationapi.getEdition');
define('SUNFW_GET_UPDATE_URL', SUNFW_API_URL . '&task=authenticationapi.getUpdate');
define('SUNFW_JOIN_TRIAL_URL', SUNFW_API_URL . '&task=authenticationapi.createTrialOrder');
define('SUNFW_VALIDATE_LICENSE_URL', SUNFW_API_URL . '&task=authenticationapi.validateLicense');
define('SUNFW_GET_FEEDBACK_URL', SUNFW_API_URL . '&task=authenticationapi.getFeedbackOptions&type=product-uninstall');
define('SUNFW_POST_FEEDBACK_URL', SUNFW_API_URL . '&task=authenticationapi.collectFeedback&feedback_type=product&feedback_sub_type=uninstall');
define('SUNFW_GET_DISCOUNT_URL', SUNFW_API_URL . '&task=authenticationapi.getLicenseStatus');

define('SUNFW_GETTING_STARTED_SERIES_URL', 'https://www.joomlashine.com/help-center/getting-started.html?utm_source=jsn-sunfw&utm_medium=%1$s&utm_campaign=[Referral] Getting Started Series');
